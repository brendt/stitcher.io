<x-base title="Books">
    <x-container>
        <x-menu/>

        <div class="grid gap-4">
            <x-book-card title="Things I wish I knew when I started programming" img="/img/static/books/things-cover.png" href="https://things-i-wish-i-knew.com/">
                <p>
                    This is my newest book aimed at program mers of any skill level. This book isn't about patterns, principles, or best practices; there's actually barely any code in it. It's about the many things I've learned along the way being a professional programmer, and about the many, many mistakes I made along that way as well. It's what I wish someone would have told me years ago, and I hope it might inspire you.
                </p>
            </x-book-card>

            <x-book-card title="Things I wish to know: notebooks" img="/img/static/books/notebook-cover.png" href="https://things-i-wish-i-knew.com/">
                <p>A hardcover, dot grid notebook that goes along the "Things I wish I knew when I started programming" book. Available in two sizes.</p>

                <ul>
                    <li>
                        <a href="https://www.amazon.com/Things-I-wish-know-Notebook/dp/B0FQJVRDNB">Notebook, 6x9", 122 pages</a>
                    </li>
                    <li>
                        <a href="https://www.amazon.com/Things-wish-know-Notebook-XL/dp/B0FQPP6QHW">Notebook XL, 7x10", 256 pages</a>
                    </li>
                </ul>
            </x-book-card>

            <x-book-card title="Event Sourcing in Laravel" img="/img/static/books/esl-cover.jpg" href="https://event-sourcing-laravel.com/">
                <p>Everything you need to learn about starting with event sourcing is covered in this course: from learning about event driven design and events to aggregates, projections, process managers, CQRS and more. Not only are all patterns and principles covered, we also discuss many common pitfalls of an event sourced system, and how to avoid them.</p>
            </x-book-card>

            <x-book-card title="Front Line PHP" img="/img/static/books/flp-cover.jpg" href="https://front-line-php.com/">
                <p>Front Line PHP looks at PHP from the perspective of a modern-day PHP developer. You'll learn about everything new in PHP 7 and PHP 8, as well patterns, best practices, PHP internals and more!</p>
            </x-book-card>

            <x-book-card title="Laravel Beyond CRUD" img="/img/static/books/lbc-cover.jpg" href="https://laravel-beyond-crud.com/">
                <p>Going beyond a standard CRUD application, this course teaches you how to structure and manage a large Laravel application to keep it maintainable for years to come.</p>
            </x-book-card>
        </div>
    </x-container>
</x-base>