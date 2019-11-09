> Or in other words, dealing with complex database relations and Laravel models.

Recently I had to deal with a complex performance issue in one of our larger Laravel projects. Let me quickly set the scene.

We want an admin user to see an overview of all people in the system in a table, and we want a column in that table to list which contracts are active at the moment for each person.

The relation between `Contract` and `Person` is as follows:

```
<hljs type>Contract</hljs> > <hljs type>HabitantContract</hljs> > <hljs type>Habitant</hljs> > <hljs type>Person</hljs>
```

I don't want to spend too much time going into details as to how we came to this relationship hierarchy. It's important for you to know that, yes, this hierarchy is important for our use cases: a `Contract` can have several `Habitants`, which are linked via a pivot model `HabitantContract`; and each `Habitant` has a relation to one `Person`.

{{ ad:carbon }}

Since we're showing an overview of all people, we'd like to do something like this in our controller:

```php
class PeopleController
{
    public function index() 
    {
        $people = <hljs type>PersonResource</hljs>::<hljs prop>collection</hljs>(<hljs prop>Person</hljs>::<hljs prop>paginate</hljs>());

        return <hljs prop>view</hljs>('people.index', <hljs prop>compact</hljs>('people'));
    }
}
```

Let's make clear that this is an oversimplified example, though I hope you get the gist. Ideally, we'd want our resource class to look something like this:

```php
/** @mixin \App\Domain\People\Models\Person */
class PersonResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name' => $this->name,

            'active_contracts' => $this->activeContracts
                -><hljs prop>map</hljs>(function (<hljs type>Contract</hljs> $contract) {
                    return $contract->contract_number;
                })
                -><hljs prop>implode</hljs>(', '),

            // …
        ];
    }
}
```

Notice especially the `Person::activeContracts` relation. How could we make this work?

A first though might be by using a `HasManyThrough` relation, but remember that we're 4 levels deep in our relation hierarchy. Besides that, I find `HasManyThrough` to be [very confusing](*/blog/laravel-has-many-through).

We could query the contracts on the fly, one by one per person. The issue with that is that we're introducing an n+1 issue since we'll be an extra query _per_ person. Imagine the performance impact if you're dealing with more than just a few models.

One last solution that came to mind was to load all people, all contracts, and map them together manually. In the end that's exactly what I ended up doing, though I did it in the cleanest possible way: using custom relations.

Let's dive in.

## Configuring the Person model

Since we want our `$person->activeContracts` to work exactly like any other relation, there's little work to be done here: let's add a relation method to our model, just like any other.

```php
class Person extends Model
{
    public function activeContracts(): ActiveContractsRelation
    {
        return new <hljs type>ActiveContractsRelation</hljs>($this);
    }
}
```

There's nothing more to do here. Of course we're only starting, since we haven't actually implemented `ActiveContractsRelation`!

## The custom relation class

Unfortunately there's no documentation on making your own relation classes. Luckily you don't need much to learn about them: some code-diving skills and a little bit of time gets you pretty far. Oh an IDE also helps.

Looking at the existing relation classes provided by Laravel, we learn that there's one base relation that rules them all: `Illuminate\Database\Eloquent\Relations\Relation`. Extending it means you need to implement some abstract methods.

```php
class ActiveContractsRelation extends Relation
{
    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints() { /* … */ }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param array $models
     *
     * @return void
     */
    public function addEagerConstraints(array $models) { /* … */ }

    /**
     * Initialize the relation on a set of models.
     *
     * @param array $models
     * @param string $relation
     *
     * @return array
     */
    public function initRelation(array $models, $relation) { /* … */ }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param array $models
     * @param \Illuminate\Database\Eloquent\Collection $results
     * @param string $relation
     *
     * @return array
     */
    public function match(array $models, Collection $results, $relation) { /* … */ }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults() { /* … */ }
}
```

The doc blocks get us on the way, though it's not always entirely clear what needs to happen. Again we're in luck, Laravel still has some existing relation classes where we can look to.

Let's go through building our custom relation class step by step. We'll start by overriding the constructor and adding some type hints to the existing properties. Just to make sure the type system will prevent us from making stupid mistakes.

The abstract `Relation` constructor requires both specifically for  an eloquent `Builder` class, as well as the parent model the relationship belongs to. The `Builder` is meant to be the base query object for our related model, `Contract`, in our case.

Since we're building a relation class specifically for our use case, there's no need to make the builder configurable. Here's what the constructor looks like:

```php
class ActiveContractsRelation extends Relation
{
    /** @var \App\Domain\Contract\Models\Contract|Illuminate\Database\Eloquent\Builder */
    protected $query;

    /** @var \App\Domain\People\Models\Person */
    protected $parent;

    public function __construct(<hljs type>Person</hljs> $parent)
    {
        parent::<hljs prop>__construct</hljs>(<hljs type>Contract</hljs>::<hljs prop>query</hljs>(), $parent);
    }

    // …
}
```

Note that we type hint `$query` both with the `Contract` model as well as the `Builder` class. This allows IDEs to provide better autocompletion, such as custom scopes defined on the model class.

We've got our relation constructed: it will query `Contract` models, and use a `Person` model as its parent. Moving on to building our query.

This is where the `addConstraints` method come in. It will be used to configure the base query. It will set up our relation query specifically to our needs. This is the place where most business rules will be contained:

- We only want active contracts to show up
- We only want to load active contracts that belong to a specified person (the `$parent` of our relation)
- We might want to eagerly load some other relations, but more on that later.

Here's what `addContraints` looks like, for now:

```php
class ActiveContractsRelation extends Relation
{
    // …

    public function addConstraints()
    {
        $this->query
            -><hljs prop>whereActive</hljs>() // A query scope on our `Contract` model
            -><hljs prop>join</hljs>(
                'contract_habitants', 
                'contract_habitants.contract_id', 
                '=', 
                'contracts.id'
            )
            -><hljs prop>join</hljs>(
                'habitants', 
                'habitants.id', 
                '=', 
                'contract_habitants.habitant_id'
            );
    }
}
```

Now I do assume that you know how basic joins work. Though I will summarize what's happening here: we're building a query that will load all `contracts` and their `habitants`, via the `contract_habitants` pivot table, hence the two joins.

One other constraint is that we only want active contracts to show up, for this we can simply use an existing query scope provided by the `Contract` model.

With our base query in place, it's time to add the real magic: supporting eager loads. This is where the performance wins are: instead of doing one query per person to load its contracts, we're doing one query to load all contracts, and link these contracts to the correct persons afterwards.

This is what `addEagerConstraints`, `initRelation` and `match` are used for. Let's look at them one by one.

First the `addEagerConstraints` method. This one allows us to modify the query to load in all contracts related to a set of people. Remember we only want two queries, and link the results together afterwards.

```php
class ActiveContractsRelation extends Relation
{
    // …

    public function addEagerConstraints(<hljs type>array</hljs> $people)
    {
        $this->query-><hljs prop>whereIn</hljs>(
            'habitants.contact_id', 
            <hljs prop>collect</hljs>($people)->pluck('id')
        );
    }
}
```

Since we joined the `habitants` table before, this method is fairly easy: we'll only load contracts that belong to the set of people provided.

Next the `initRelation`. Again this one is rather easy: its goal is to initialise the empty `activeContract` relationship on every `Person` model, so that it can be filled afterwards.

```php
class ActiveContractsRelation extends Relation
{
    // …

    public function initRelation(<hljs type>array</hljs> $people, $relation)
    {
        foreach ($people as $person) {
            $person-><hljs prop>setRelation</hljs>(
                $relation, 
                $this->related-><hljs prop>newCollection</hljs>()
            );
        }

        return $people;
    }
}
```

Note that the `$this->related` property is set by the parent `Relation` class, it's a clean model instance of our base query, in other words an empty `Contract` model:

```php
abstract class Relation
{
    public function __construct(<hljs type>Builder</hljs> $query, <hljs type>Model</hljs> $parent)
    {
        $this->related = $query-><hljs prop>getModel</hljs>();
    
        // …
    }
    
    // …
}
```

Finally we arrive at the core function that will solve our problem: linking all people and contracts together.

```php
class ActiveContractsRelation extends Relation
{
    // …

    public function match(<hljs type>array</hljs> $people, <hljs type>Collection</hljs> $contracts, $relation)
    {
        if ($contracts-><hljs prop>isEmpty</hljs>()) {
            return $people;
        }

        foreach ($people as $person) {
            $person-><hljs prop>setRelation</hljs>(
                $relation, 
                $results-><hljs prop>filter</hljs>(function (<hljs type>Contract</hljs> $contract) use ($person) {
                    return <hljs prop>in_array</hljs>($person->id, $contract->habitants-><hljs prop>pluck</hljs>('person_id')-><hljs prop>toArray</hljs>());
                })
            );    
        }

        return $people;
    }
}
```

Let's walk through what's happening here: on the one hand we've got an array of parent models, the people; on the other hand we've got an array of contracts. The goal of the `match` function is to link them together.

How to do this? It's not that difficult: loop over all people, and search all contracts that belong to each one of them, based on the habitants linked to that contract. 

Almost done? Well… there's one more issue. Since we're using the `$contract->habitants` relation, we need to make sure it is also eagerly loaded, otherwise we just moved the n+1 issue instead of solving it. So it's back to the `addEagerConstraints` method for a moment.

```php
class ActiveContractsRelation extends Relation
{
    // …

    public function addEagerConstraints(<hljs type>array</hljs> $people)
    {
        $this->query
            -><hljs prop>whereIn</hljs>(
                'habitants.contact_id', 
                <hljs prop>collect</hljs>($people)->pluck('id')
            )
            -><hljs prop>with</hljs>('habitants')
            -><hljs prop>select</hljs>('contracts.*');
    }
}
```

We're adding the `with` call to eagerly load all habitants, but also note the specific `select` statement. We need to tell Laravel's query builder to only select the data from the `contracts` table, because otherwise the related habitant data will be merged on the `Contract` model, cause it to have the wrong ids.

Finally we need to implement the `getResults` method, which simply executes the query:

```php
class ActiveContractsRelation extends Relation
{
    // …

    public function getResults()
    {
        return $this->query->get();
    }
}
```

---

And that's it! Our custom relation can now be used like any other Laravel relation. It's an elegant solution to solving a complex problem the Laravel way.
