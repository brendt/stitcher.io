At [Spatie](*https://www.spatie.be), we're working on a large project which uses UUIDs in many database tables.
These tables vary in size from a few thousand records to half a million.

As you might know, normal UUIDs are stored as `VARCHAR(36)` fields in the database. 
This has an enormous performance cost, because MySQL is unable to properly index these records.
Take a look at the following graph, plotting the execution time of hundred queries against two datasets: one with 50k rows, one with 500k rows.

![Poor textual UUID performance](/img/blog/binary-uuid/textual_uuid.png)

That's an average of more than 1.5 seconds when using textual UUIDs! 
Looking around for better alternatives, we found a two-part solution.

## Saving UUIDs as binary data

Instead of saving UUIDs as `VARCHAR`, it's possible to store their actual binary data in a `BINARY` field. 
Storing them in this format, MySQL has a lot less trouble indexing this table. 
This is the graph plotting a much faster result.

![Binary UUIDs have a huge performance improvement](/img/blog/binary-uuid/binary_uuid.png)

That's an avarage of 0.0001324915886 seconds per query, in comparison to 1.5 seconds for the textual UUID.

## It becomes even better!

The binary encoding of UUIDs solved most of the issue.
There's one extra step to take though, which allows MySQL to even better index this field.

By switching some of the bits in the UUID, more specifically time related data, 
we're able to save them in a more ordered way.
And it seems that MySQL is especially fond of ordered data when creating indices.

Using this approach, we can see following result.

![Binary UUIDs have a huge performance improvement](/img/blog/binary-uuid/comparison.png)

The optimised approach is actually slower for lookups in a small table, 
but it outperforms the normal binary approach on larger datasets.
You can also see that normal integer IDs are still the winner by far.
I would recommend only using UUIDs when there's a very good use case for them.
For example: when you want unique IDs over all tables, and not just one;
or if you want to hide exactly how many rows there are in the table

The MySQL team wrote a [blogpost](*http://mysqlserverteam.com/storing-uuid-values-in-mysql-tables/)
explaining this bit-shifting of UUIDs in further detail. 
If you'd like to know how it works internally, over there is a good start. 

If you're building a Laravel application and would like to use optimised UUIDs in your project, 
we've made [a package](*https://github.com/spatie/laravel-binary-uuid) especially for you.
You'll also find more benchmark details in the README over there.

Finally, if you're looking into implementing this behaviour in a non-Laravel project, 
you should definitely take a look at [Ramsey's UUID package](*https://github.com/ramsey/uuid), we're using it too!
