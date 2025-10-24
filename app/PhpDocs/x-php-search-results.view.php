<nav id="php-search-results" class="grid gap-2">
    <a :foreach="$matches as $match" :href="$match->uri">{{ $match->title }}</a>
</nav>