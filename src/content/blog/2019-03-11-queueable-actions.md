People who follow my work or Spatie's, might have come across a pattern we use in some of our projects. 
We call them "actions", and simply put they are classes to encapsulate business logic.

You can read up on how we structure projects by domains and actions [here](*/blog/organise-by-domain), 
and find examples of actions in the [code of my aggregate project](*https://github.com/brendt/aggregate.stitcher.io/blob/master/app/Domain/Post/Actions/AddViewAction.php).

{{ ad:carbon }}

Let's give one example using actions: creating a contract.
A contract creation not only saves a model in the database, but also generates a PDF of that contract.

Here's how we'd program this action:

```php
class CreateContractAction
{
    public function __construct(
        GeneratePdfAction $generatePdfAction
    ) { /* … */ }
    
    public function execute(
        ContractData $contractData
    ): Contract {
        $contract = Contract::createFromDTO($contractData);
        
        $this->generatePdfAction->execute($contract);
        
        return $contract->refresh();
    }
}
```

If you know DDD, actions can be thought of as a command and its handler combined.
There are projects where this approach doesn't suffice, but there are also cases where they are very helpful.

We use this pattern a lot, because of the three benefits it offers:

- It's very easy to unit test individual action classes.
- Actions can be composed out of other actions via the dependency container.
- Actions allow us to think in well-defined context, they reduce cognitive load and decrease maintenance cost.

One detail: there are some cases where we want actions to be executed asynchronously. 

In the case of our example: we want to create the contract immediately, 
but we don't want our users to wait until the PDF is generated. 
This should be done asynchronously.

In the past, we used to wrap actions into jobs. It would look something like this:

```php
class GeneratePdfJob implements ShouldQueue
{
    use Dispatchable, 
        InteractsWithQueue, 
        Queueable, 
        SerializesModels;

    public function __construct(
        Contract $contract 
    ) { /* … */ }
    
    public function handle(
        GeneratePdfAction $generatePdfAction
    ) {
        $generatePdfAction
            ->execute($this->contract);
    }
}
```

Instead of directly calling the action within another action, we dispatch a new job.

```php
class CreateContractAction
{
    public function execute(
        ContractData $contractData
    ): Contract {
        // …
        
        dispatch(new GeneratePdfJob($contract));
        
        // …
    }
}
```

This works fine, but manually wrapping an action in a job started to be kind of tedious in our larger projects.

That's why we started looking into ways of automating this. 
And sure thing: we can!

Here's what the `GeneratePdfAction` would look like, using our package:

```php
use Spatie\QueueableAction\QueueableAction;

class GeneratePdfAction
{
    use QueueableAction;

    public function __construct(
        Renderer $renderer,
        Browsershot $browsershot
    ) { /* … */ }
    
    public function execute(Pdfable $pdfable): void
    {
        $html = $this->renderer->render($pdfable);
        
        $this->browsershot
            ->html($html)
            ->save($pdfable->getPath());
    }
}
```

By using `QueueableAction`, this action can now be executed asynchronously.
Here's how it's used:

```php
class CreateContractAction
{
    // …
    
    public function execute(
        ContractData $contractData
    ): Contract {
        // …
        
        $this->generatePdfAction
            ->onQueue()
            ->execute($contract);
            
        // …
    }
}
```

It's important to note that the above will still have auto completion of the `execute` method, 
as well as DI support; just like normal actions:

![](/resources/img/blog/queueable/autocompletion.png)

## What's the difference with Jobs?!?

Actions allow for constructor injection, which means you can use actions within actions within actions, and so forth.

Jobs on the other hand get container injection in their `handle` method. 
This means you cannot compose jobs of of other jobs via the dependency container.

It's obvious why Laravel cannot provide constructor injection in jobs: 
job-specific data, like our contract, needs to be serialised on order for jobs to be queueable, 
and the constructor is required to ensure the job has valid data.

By introducing the concept of actions, we're able to separate responsibilities between classes better:
jobs are used for data serialisation and executing tasks asynchronously; 
but they are not concerned with business logic anymore.  

If you're concerned with difficult to debug actions when they're queued, 
you can put your mind at ease.
`ActionJob` classes that are dispatched to for example, Horizon, 
have their name changed to the action class they wrap:

![](/resources/img/blog/queueable/horizon.png)  

The underlying details of making actions queueable are hidden to the developer using them,
making it very easy to work with them, even in an asynchronous context.

Like I said: we made this into a small package. 
It consists of a simple trait and an `ActionJob`.
If you want to give it a try, you can check it out here: [spatie/laravel-queueable-action](*https://github.com/spatie/laravel-queueable-action).
