In the previous chapters, I've talked about two of the three core building blocks of every application: DTOs and actions — data and functionality. In this chapter we will look at the last piece that I consider part of this core: exposing data that's persisted in a data store; in other words: models.

Now, models are a tricky subject. Laravel provides a lot of functionality via its Eloquent model classes, which means that they not only represent the data in a data store, they also allow you build queries, load and save data, they have a built-in event system, and more.

In this chapter, I will not tell you to ditch all the model functionality that's provided by Laravel — it's quite useful indeed. However I will name a few pitfalls that you need to be careful of, and solutions for them; so that even in large projects, models won't be the cause of difficult maintainership.

My point of view is that we should embrace the framework, instead of trying to fight it; though we should embrace it in such a way that large projects stay maintainable. Let's dive in. 

## Models ≠ business logic

The first pitfall that many developers fall into, is that they think of models as _the_ place to be when it comes to business logic. I already listed a few responsibilities of models which are built-into Laravel, and I would argue to be careful not to add any more.

It sounds very appealing at first, to be able to do something like `$invoiceLine->price_including_vat` or `$invoice->total_price`; and it sure does. I actually do believe that invoices and invoice lines _should_ have these methods. There's one important distinction to make though: these methods shouldn't calculate anything. Let's take a look at what *not* to do:

Here's a `total_price` accessor on our invoice model, looping over all invoice lines and making the sum of their total price. 

```php
class Invoice extends Model
{
    public function getTotalPriceAttribute(): int
    {
        return $this->invoiceLines
            -><hljs prop>reduce</hljs>(function (<hljs type>int</hljs> $totalPrice, <hljs type>InvoiceLine</hljs> $invoiceLine) {
                return $totalPrice + $invoiceLine->total_price;
            }, 0);
    }
}
```

And here is how the total price per line is calculated.

```php
class InvoiceLine extends Model
{
    public function getTotalPriceAttribute(): int
    {
        $vatCalculator = <hljs prop>app</hljs>(<hljs type>VatCalculator</hljs>::class);
    
        $price = $this->item_amount * $this->item_price;

        if ($this->price_excluding_vat) {
            $price = $vatCalculator-><hljs prop>totalPrice</hljs>(
                $price, 
                $this->vat_percentage
            );
        }
    
        return $price;
    }
}
```

Since you read the previous chapter on actions, you might guess what I would do instead: calculating the total price of an invoice is a user story that should be represented by an action.

The `Invoice` and `InvoiceLine` models could have the simple `total_price` and `price_including_vat` properties, but they are calculated by actions first, and then stored in the database. When using `$invoice->total_price`, you're simply reading data that's already been calculated before.

There are a few advantages to this approach. First the obvious one: performance, you're only doing the calculations once, not every time when in need of the data. Second, you can query the calculated data directly. And third: you don't have to worry about side effects.

Now, we could start a purist debate about how single responsibility helps make your classes small, better maintainable and easily testable; and how dependency injection is superior to service location; but I rather state the obvious instead of having long theoretical debates where I know there's simply two sides that won't agree.

So, the obvious: even though you might like to be able to do `$invoice->send()` or `$invoice->toPdf()`, the model code is growing and growing. This is something that happens over time, it doesn't seem to be a big deal at first. `$invoice->toPdf()` might actually only be one or two lines of code. 

From experience though, these one or two lines add up. One or two lines isn't the problem, but hundred times one or two lines is. The reality is that model classes grow over time, and can grow quite large indeed. 

Even if you don't agree with me on the advantages that single responsibility and dependency injection brings, there's little to disagree about this: a model class with hundreds of lines of code, does not stay maintainable.

All that to say this: think of models and their purpose as to only provide data for you, let something else be concerned with making sure that data is calculated properly. 

## Scaling down models

If our goal is to keep model classes reasonably small — small enough to be able to understand them by simply opening their file — we need to move some more things around. Ideally, we only want to keep getters and setters, simple accessors and mutators, casts and relations. 

Other responsibilities should be moved to other classes. One example is query scopes: we could easily move them to dedicated query builder classes. 

Believe it or not: query builder classes are actually the normal way of using Eloquent; scopes are simply syntactic sugar on top of them. This is what a query builder class might look like.

```php
namespace <hljs type>Domain\Invoices\QueryBuilders</hljs>;

use <hljs type>Domain\Invoices\States\Paid</hljs>;
use <hljs type>Illuminate\Database\Eloquent\Builder</hljs>;

class InvoiceQueryBuilder extends Builder
{
    public function wherePaid(): self
    {
        return $this-><hljs prop>whereState</hljs>('status', <hljs type>Paid</hljs>::class);
    }
}

```

Next up, we override the `newEloquentBuilder` method in our model and return our custom class. Laravel will use it from now on.

```php
namespace <hljs type>Domain\Invoices\Models</hljs>;

use <hljs type>Domain\Invoices\QueryBuilders\InvoiceQueryBuilder</hljs>;

class Invoice extends Model 
{
    public function newEloquentBuilder($query): <hljs type>InvoiceQueryBuilder</hljs>
    {
        return new <hljs type>InvoiceQueryBuilder</hljs>($query);
    }
}
```

This is what I meant by embracing the framework: you don't need to introduce new patterns like repositories, you can build upon what Laravel provides. Giving it some thought, we strike the perfect balance between using the commodities provided by the framework, and preventing our code from growing too large in specific places.  

Using this mindset, we can also provide custom collection classes for relations. Laravel has great collection support, though you often end up with long chains of collection functions either in the model or in the application layer. This again isn't ideal, and luckily Laravel provides us with the needed hooks to bundle collection logic into a dedicated class.

Here's an example of a custom collection class, and note that it's entirely possible to combine several methods into new ones, avoiding long function chains in other places.

```php
namespace <hljs type>Domain\Invoices\Collections</hljs>;

use <hljs type>Domain\Invoices\Models\InvoiceLines</hljs>;
use <hljs type>Illuminate\Database\Eloquent\Collection</hljs>;

class InvoiceLineCollection extends Collection
{
    public function creditLines(): self
    {
        return $this-><hljs prop>filter</hljs>(function (<hljs type>InvoiceLine</hljs> $invoiceLine) {
            return $invoiceLine-><hljs prop>isCreditLine</hljs>();
        });
    }
}
```

This is how you link a collection class to a model; `InvoiceLine`, in this case:

```php
namespace <hljs type>Domain\Invoices\Models</hljs>;

use <hljs type>Domain\Invoices\Collection\InvoiceLineCollection</hljs>;

class InvoiceLine extends Model 
{
    public function newCollection(<hljs type>array</hljs> $models = []): <hljs type>InvoiceLineCollection</hljs>
    {
        return new <hljs type>InvoiceLineCollection</hljs>($models);
    }

    public function isCreditLine(): <hljs type>bool</hljs>
    {
        return $this->price < 0.0;
    }
}
```

Every model having a `HasMany` relation to `InvoiceLine`, will now use our collection class instead.

```php
$invoice
    ->invoiceLines
    -><hljs prop>creditLines</hljs>()
    -><hljs prop>map</hljs>(function (<hljs type>InvoiceLine</hljs> $invoiceLine) {
        // …
    });
```

Try to keep your models clean and data-oriented, instead of having them provide business logic. There are better places to handle it.

## Empty bags of nothingness

As a reaction to this chapter, someone asked how to avoid our objects becoming nothing more than empty bags of data, an anti-pattern Martin Fowler [wrote about](*https://martinfowler.com/bliki/AnemicDomainModel.html).

The answer — my answer — is twofold. First of all: I don't think of models as empty bags with plain old data. Using accessors, mutators and casts, they provide a rich layer between the plain data in the database, and the data the developer wants to use. In this chapter I argued to move several other responsibilities to separate classes, that's true, yet I believe that models in their "trimmed" down version still offer a lot more value than simple bags of data, thanks to all the functionality Laravel provides.

Secondly, I think it's worth to mention Alan Kay's vision on this topic (he's the one who came up with the term OOP). He said, in [a talk back in 1997](*https://www.youtube.com/watch?time_continue=2265&v=oKg1hTOQXoY), that he regretted calling the paradigm "object oriented", and not "process oriented". Alan argues that he's actually a proponent of splitting process and data. 

Whether you agree with that point of view or not is up to you. I do admit to have been influenced by some of Alan's insights, and you might notice that throughout this blog series. Like I said before: don't think of this series as the holy grail of software design. My goal is to challenge the current way you're writing code, making you think whether there are more optimal ways to solve some of your problems.
