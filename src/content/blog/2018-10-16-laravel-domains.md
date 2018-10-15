In this post we'll look at a different approach of structuring large code bases into separate domains.
The name "domain" is derived from the popular DDD paradigm, or also: domain driven design.

While many concepts in this post are inspired by DDD principles, 
they don't strictly follow domain driven design.
In our context, "domain" could also be named "module". 
A "domain" simply refers to a category of related stuff, 
that's it. 

It's also important to note is that this approach isn't a silver bullet.
At [Spatie](*https://spatie.be) we choose a different project structure
based on the needs of that specific project. 
It is possible that your project isn't a good fit for what we'll be reviewing today.

In our experience, today's principles are mostly beneficial in larger projects:

- Long running projects with an initial development timespan of half a year to one year or more, 
with several years of maintenance and extensions after that.
- Around fifty to hundred models representing the business.
- Several hundred routes exposing functionality to the outside.

## So, what is a "domain" ?

If you've worked on these kinds of large projects before, 
you know that "the business logic" never is just *one* thing.
Often during development, you'll identify "sub-systems" within the larger domain;
that is: the collection of problems you're trying to solve with your code.

To name a few examples: user management, inventory management, invoicing and contracts.
I'm sure you can think of many others.

Most likely, every sub-system has one or several models. 
But it doesn't stop there:
models can be interacted with,
actions can be performed with them, 
there can be system-specific validation rules, 
ways of passing data between systems, and more.

Looking at a standard Laravel application, the code describing a single system 
is often spread across multiple directories:

```
app/
├── Enums/
│   ├── ContractDurationType.php
│   └── ContractType.php
├── Exceptions/
│   └── InvalidContractDate.php
├── Models/
│   └── Contract.php
└── Rules/
    ├── ContractAvailabilityRule.php
    └── ContractDurationRule.php
```

This structure was the first struggle that prompted me to look for a better solution.
I often found myself searching through several places in order to work one thing, one system.

So why not group sub-systems together? 
It looks something like this:

```
Domain/
├── Contracts/
├── Invoicing/
└── Users/
```

You can see the name `Domain` here. 
According to Oxford Dictionary, a "domain" can be described like so:

> A specified sphere of activity or knowledge.

We're grouping code together based on their sphere of activity, their domain.
Let's zoom into one specific domain folder:

```
Contracts/
├── Actions/
├── Enums/
├── Exceptions/
├── Models/
├── Rules/
├── Status/
└── ValueObjects/
```

Modern PHP developers are most likely familiar with most of these folder names. 
Though some deserve a little more attention.

### Actions

Actions are probably the most powerful tool within this whole setup.
An action is a class that performs an operation within its domain.
This might be a simple task like creating or updating a model,
or something more complex following one or several business rules like approving a contract.

Because a single action only focuses on one task, 
they are extremely flexible: 
an action can be composed out of other actions and they can be injected wherever you want.

Here's an example of two actions working together: `CreateOrUpdateContractLine` and `ResolveContractLines`.
The first one will do as its name says: create or update a single contract line.
The second one will loop over a collection of user input, and resolve the lines one by one.

Here's what `ResolveContractLines` will do:

- Loop over the user input and create or update existing lines.
- Keep a list of contract lines which are currently added to the contract.
- Remove all lines that don't exist anymore, the user has removed them.

Here's the code:

```php
class ResolveContractLines
{
    public function __construct(
        CreateOrUpdateContractLine $createOrUpdateContractLine,
        RemoveContractLine $removeContractLine
    ) { /* … */ }

    public function execute(
        Contract $contract,
        ContractLinesCollection $contractLinesCollection
    ) {
        $lineIds = [];

        foreach ($contractLinesCollection as $contractLineData) {
            $contractLine = $this->createOrUpdateContractLine
                ->execute($contractLineData);

            $lineIds[] = $contractLine->id;
        }

        $contractLinesToRemove = ContractLine::query()
            ->whereContract($contract)
            ->whereNotIn('id', $lineIds)
            ->get();

        foreach ($contractLinesToRemove as $contractLine) {
            $this->removeContractLine->execute($contractLine);
        }
    }
}
```

Besides composing actions together, they are also great for testing. 
Because of an action's small size and single responsibility, 
it can be unit tested very efficiently.

Actions also encapsulate most of the business logic for the app:
generating contract numbers, changing statuses, handling side-effects in an explicit way,…
This makes it easier for developers to reason about what the application does, 
as most of its business is encapsulated as actions.

If you're into DDD, you're probably thinking of commands right now.
Actions are a simpler version of them. 
There's no command bus and actions may directly return values.
For the scope of our projects, it's a very manageable approach.

### ValueObjects

You're probably wondering how this domain stuff ties together with controllers or CLI commands.
That's of course the place where you'll use them.
There's one more abstraction we need to understand though: value objects.

Have you noticed the `ContractLinesCollection` passed to the `ResolveContractLines` action in the previous example?
That's a value object.

Working with user input isn't always straight forward. 
In Laravel applications you'll get an array of form data or an array of CLI arguments, 
and the rest is up to you.

Value objects are a representation of that user data, in a structured way.
Because we want don't want to concern our actions with input validation,
we pass them a value object. 
There's one rule applied to value objects: if they exist, they are valid.

Most of the time, value objects are a simple mapping between validated request data, 
and properties that can be used by actions.

Here's an example of a value object:

```php
class ContractLineData
{
    public $price;
    public $dateFrom;
    public $dateTo;
    public $article;

    public static function fromArray(
        array $input
    ): ContractLineData {
        return new self(
            $input['price'],
            Carbon::make($input['date_from']),
            Carbon::make($input['date_to']),
            Article::find($input['article_id'])
        );
    }

    public function __construct(
        int $price,
        Carbon $dateFrom,
        Carbon $dateTo,
        Article $article
    ) { /* … */ }
}
```

Because of convenience, we're using public properties. 
You can imagine why we're looking forward to strongly typed and readonly properties in PHP.

Value objects allow actions to only focus on the actual action, 
and not be concerned whether input is valid or not.
Furthermore, it's easy to fake a value object, making tests simpler once more.

## Tying it together

Up until this point, I've said almost nothing about controllers or CLI commands, 
and how they fit into this picture. That's intentional.

See, because our domains are split into separate areas, 
we're able to develop a whole domain, without ever writing a single controller or view.
Everything in the domain is easily testable, 
and almost every domain can be be developed side by side with other domains.

In larger projects, this is a highly efficient approach. 
We've got two or three backend developers working on one project, 
and each of them has a domain they are working on next to each other.

Also, because every domain is tested, we're very certain that all business logic
required by the client works as intended, before writing a single form and integration tests.

Once a domain is done, it can be consumed. 
The domain itself doesn't care when or where it is used, 
its usage rules are clear to the outside.

This means we're able to build one or more applications, using the existing domains.
In one of our projects, there's an admin HTTP application and a REST API.
Both of them use the same domains; their actions, models, rules, etc.
You can see how this approach is not only efficient during development, 
but also enables for much better scaling.

Here's an example of how a controller in the admin HTTP application looks:

```php
class ContractsController
{
    public function index() { /* … */ }

    public function edit(Contract $contract) { /* … */ }

    public function update(
        Contract $contract,
        UpdateContract $updateContract,
        UpdateContractRequest $updateContractRequest
    ) {
        $contract = $updateContract->execute(
            $contract,
            ContractData::fromRequest($updateContractRequest)
        );
        
        return new ContractViewModel($contract);
    }
}
```

Almost all our controllers actions are as simple as this:

- Validate the request data and parse it into a value object.
- Execute the action, we don't care anymore what happens underneath at this point.
- Return the result, in our case using [view models](/blog/laravel-view-models).

## In closing

Structuring code in domains increases efficiency between developers on a single project.
Furthermore, it decreases the complexity of maintenance, because sub-systems are separated and well tested.

By using actions and value objects, you're able to communicate with the domain 
in a controlled and testable way. 
While it takes longer to initially write, this approach pays off very quickly,
even during early development.

Maybe the most important reason for structuring our code this way, 
is that it's easier to understand.
We humans don't think in abstracts like "models", "actions" and "rules"; 
we categorize complex business processes into sub-systems. 
Things like "contracts" and "invoicing".

I've been structuring complex code bases like this for two years,
and can say from experience that it's significantly more easy to reason about them now.
In end, I believe developer experience is equally important as theoretical knowledge and paradigms to succeed.
