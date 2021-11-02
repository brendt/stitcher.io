A week ago, at the beginning of these series, I made a distinction between running static analysers in realtime while coding, and running them as standalone programs. While the boundaries are a little troubled — PhpStorm can run Psalm and PHPStan in "realtime", and PhpStorm's inspections can also be run standalone — the simplest way of thinking about this is that your IDE will perform realtime analysis, while tools like Psalm and PHPStan will most likely be run separately.

In the latter case, there's an opportunity to run them in some kind of continuous integration (CI) flow. Running static analysers locally can be helpful, though they take a while, and it's most important that the integrity of your code is ensured before deploying.

My personal favourite way of running CI actions like these is by using GitHub Actions: they are easy to set up and free to a certain extent.

Let's take a look at a simplified GitHub action YAML to run Psalm in a Laravel project:

```txt
<hljs keyword>name</hljs>: Psalm

<hljs keyword>jobs</hljs>:
  <hljs keyword>psalm</hljs>:
    <hljs keyword>name</hljs>: Psalm
    <hljs keyword>runs-on</hljs>: 'ubuntu-18.04'

    <hljs keyword>env</hljs>:
      <hljs keyword>extensions</hljs>: 
        - dom
        - curl
        - libxml
        - mbstring
        - zip
        - pcntl
        - pdo
        - sqlite
        - pdo_sqlite
        - bcmath
        - soap
        - intl
        - gd
        - exif
        - iconv
        - imagick
        
    <hljs keyword>steps</hljs>:
      - <hljs keyword>uses</hljs>: actions/checkout@v2

      - <hljs keyword>name</hljs>: Setup PHP
        <hljs keyword>uses</hljs>: shivammathur/setup-php@v2
        <hljs keyword>with</hljs>:
          <hljs prop>php-version</hljs>: '8.0'
          <hljs prop>extensions</hljs>: ${{ env.extensions }}
          <hljs prop>coverage</hljs>: none

      - <hljs keyword>name</hljs>: Run composer install
        <hljs keyword>run</hljs>: composer install -n --prefer-dist

      - <hljs keyword>name</hljs>: Create sqlite database
        <hljs keyword>run</hljs>: touch db.sqlite

      - <hljs keyword>name</hljs>: Prepare Laravel Application
        <hljs keyword>run</hljs>: |
          cp .env.ci .env
          php artisan key:generate
          php artisan migrate:fresh

      - <hljs keyword>name</hljs>: Generate IDE helper files
        <hljs keyword>run</hljs>: composer ide-helper

      - <hljs keyword>name</hljs>: Run psalm
        <hljs keyword>run</hljs>: ./vendor/bin/psalm --threads=1 --no-diff
```

The first part of this action is the basic setup: installing PHP and its extensions and installing the composer dependencies; I actually removed the caching steps to keep it smaller for the purpose of this mail. 

The next steps are about setting up specifically Laravel. The SQLite database are needed to generate the IDE helper files, which in turn are needed by Psalm in order to better understand Laravel's magic.

And finally, we simply run Psalm. If it fails, GitHub will let us know.

The important lesson here is that you shouldn't bother running Psalm or PHPStan with every line of code you change. Maybe run it once for every commit, or even only when a branch is merged. Maybe you prefer to run it locally before pushing, or want GitHub to tell you about your mistakes.

Whatever your preference, the power of these tools is that they can run in the background, while you're concerned with other things. They are doing part of the work for you.

Now, both Psalm and PHPStan have significantly improved their performance over time. Still, when running them in "realtime" via PhpStorm, there is a noticeable delay. This is why I personally don't bother with trying to run them as fast as possible, I'm sure my GitHub Action will tell me when something's wrong.

---

I do want to mention that this is just my personal preference. I talked with Ondřej Mirtes about this topic, and he described an opposite workflow to mine. He called it "type-driven refactoring": when doing a refactor, he starts by deliberately breaking type definitions and sees what code PHPStan reports is breaking, so that he knows which places are affected. 

It's definitely an interesting approach, and one that I'll experiment with in the near future!

What's your favourite way of running static analysers? Let me know by hitting the reply button. 

Until tomorrow!

Brent
