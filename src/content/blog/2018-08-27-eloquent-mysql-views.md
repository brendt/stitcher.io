MySQL views are a way of storing queries on the database level, and producing virtual tables with them.
In this post we'll look at why you want to use them and how they can be integrated in Laravel with Eloquent models.

If you're already convinced of the power of MySQL views, or just want to know how to implement them in Laravel,
you're free to [skip ahead](#impatient).

{{ ad:carbon }}

## Benefits of MySQL views

A view in MySQL stores the result of a query in a table-like structure. 
You're able to query this view just like you would query a normal table.

The power of views is twofold:

- Complex queries with joins and unions can be represented as a queryable table on their own.
- MySQL is generally smarter than us when it comes to querying data. 
Compared to using collections or array functions in PHP, there's a big performance gain.

There's also a caveat to using views though. 
Depending on the kind of query, MySQL will need to construct an "in memory" table representing the view, at runtime.
This operation is called table materialization and happens when using certain keywords like `GROUP BY`, or aggregated functions.

The takeaway is that views might actually hurt query performance, 
depending on the kind of query you're executing.
As with all things, views are a good solution for some problems, but a terrible idea for others.
Use them wisely, and read up on their restrictions [here](*https://dev.mysql.com/doc/refman/8.0/en/view-restrictions.html).

## Views and their alternatives

Let's look at a real-life example, to demonstrate how we could solve a given problem.

We've got a model `MeterReading` which logs a meter reading done in an apartment building.
Every unit in the building has its own electricity, water and gas meters.

Every reading is listed in the database with a reference to the unit, the date, 
the user doing the reading, the type, and the actual meter value. 
Type in this example is `electricity`, `water` or `gas`.

This is what a simplified migration of this table looks like:

```php
Schema::create('meter_readings', function (Blueprint $table) {
    $table->unsignedInteger('unit_id');
    $table->unsignedInteger('user_id');

    $table->string('type');
    $table->dateTime('date');
    $table->unsignedInteger('value');
});
```

Now the client asks us to generate reports based on this raw data.
He wants to see an overview of the units, where every row represents 
the readings for that unit, on that day, and whether all readings were done or not.

In short, he wants to see this:

```txt
+---------+---------+------------+-------------+-------+-----+
| unit_id | user_id | date       | electricity | water | gas |
+---------+---------+------------+-------------+-------+-----+
|      14 |      72 | 2018-08-19 |           0 |     1 |   0 |
|      59 |      61 | 2018-08-06 |           0 |     0 |   1 |
|      41 |      64 | 2018-08-02 |           1 |     1 |   1 |
|      41 |      45 | 2018-08-02 |           1 |     1 |   1 |
...
|      41 |      51 | 2018-08-02 |           1 |     1 |   1 |
+---------+---------+------------+-------------+-------+-----+
```

The report show a data set that is grouped by unit, user and day; 
and the corresponding readings done for at the time.

Here are a few ways of generating this report.

### On the fly

We always query all the data, and group it in our code. 
This is the most easy way of doing it, but has some downsides:

- PHP and Laravel collections are slow, compared to the optimised algorithms MySQL can use.
- Building a virtual data set means you'll have to manually implement pagination. One row can represent multiple models.
- You're adding a lot of code to manage that special collection of readings.

### Using a raw query

We can of course skip PHP and build the raw query to fully use the power of MySQL.
While this solves the performance issue, we're still working with a custom data set which can't make use of standard pagination.
Also, you're now maintaining a big SQL query somewhere in your code. 
It's probably a string somewhere in PHP, or –slightly better– a separate sql file.

### Projecting the changes

We could make a separte model called `MeterReadingReport`, 
and use event hooks on `MeterReading` to manage these reports.

Every time a reading is added, we can get or create a report for that unit, day and user; 
and update the data accordingly.

Now there's a separate model that's simple to query. 
There's no more performance impact and the pagination issue is also solved.

But on the other hand, there's a lot more code to manage these event hooks.
Creating reports is one thing, but what if a reading is updated or deleted? 
That's a lot of complexity we need to manage.

Projecting events into other models isn't a bad idea though. 
It's one of the key features in event sourcing. 
If you've got the right setup, making projectors would definitely be an option.

While we do have a package that handles this exact use case ([laravel-event-projector](*https://github.com/spatie/laravel-event-projector)),
it seemed overkill for this use case;
especially since there are a lot of other "normal" models in this project.

### Finding the middle ground

Looking at all the possible solutions, we can make a simple list of requirements:

- As less overhead as possible in the code base.
- Good performance.
- Be able to use the standard Laravel features without any workarounds.

MySQL views are this perfect middle ground. 
Let's look at how they are implemented.  

<a name="impatient"></a>

{{ ad:google }}

## SQL views in Laravel

To work with a view, we'll have to first create a query that can build this view.
While many people are scared of SQL –modern ORMs made us way too lazy– I find it a lot of fun.

Beware that I'm no SQL master, so there might be things that could be done better. 
I also won't explain what this query does exactly, as it'll be different for your use case.

In this case, it generates the table listed above. This is it:

```sql
SELECT 
    unit_id
    , user_id
    , DATE_FORMAT(`date`, '%Y-%m-%d') AS day
    , COUNT(CASE WHEN type = 'electricity' THEN type END) 
        AS `electricity`
    , COUNT(CASE WHEN type = 'water' THEN type END) 
        AS `water`
    , COUNT(CASE WHEN type = 'gas' THEN type END) 
        AS `gas`
    
FROM 
    meter_readings
    
GROUP BY
    unit_id
    , user_id
    , day
;
```

It's very easy to build this query in your favourite SQL browser, 
and afterwards plug it into your project.

How to plug it in, you ask? Very simple, with a migration. 

```php
public function up()
{
    DB::statement($this->dropView());
    
    DB::statement($this->createView());
}
```

First of all, `dropView` is required, because Laravel only drops tables when doing a fresh migration. 
It's as simple as this:

```php
    private function dropView(): string
    {
        return <<<SQL
DROP VIEW IF EXISTS `meter_reading_reports`;
SQL;
    }
```

You notice I prefer Heredoc in these cases, a separate SQL file is of course equally good.

Michael Dyrynda pointed out to me that there's a `--drop-views` flag you can pass to the migrate command.
So, technically, this manual dropping isn't required. 
I prefer this way though, because now we don't have to remember to add the extra flag. 

Next up, the `createView` method returns the query, with some added syntax. 
I've shortened the sample a bit, but you get the point.

```php
    private function createView(): string
    {
        return <<<SQL
CREATE VIEW `meter_reading_reports` AS

SELECT /* … The query */
SQL;
    }
```

Sidenote: I'm very much looking forward to PHP 7.3 and [flexible Heredoc syntax](*/blog/new-in-php-73).

Now that we have a migration in place, all else just works like normal Laravel!

```php
class MeterReadingReport extends Model
{
    protected $casts = [
        'day' => 'date',
    ];
    
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

We're using a simple model, without any workarounds whatsoever.
Relations work just like normal, casting like you're used to, 
pagination works like it should be, and there no more performance impact.

The only thing that's not possible is of course writing to a view. 
It is actually possible to do it in MySQL, but completely irrelevant to our use case. 

Maybe you can already see some use cases where MySQL views might be useful?
Maybe you have a followup question or remark? 
I'd love to hear from you!
You can reach me on [Twitter](*https://twitter.com/brendt_gd) or via [e-mail](mailto:brendt@stitcher.io).

{{ ad:google }}
