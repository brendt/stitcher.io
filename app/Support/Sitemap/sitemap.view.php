<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url :foreach="$sitemap->uris as $uri">
        <loc>{{ $uri }}</loc>
    </url>
</urlset>
