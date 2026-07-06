---
title: Deployment
description: Learn the basics of deploying PHP applications, from hosting and servers to production configuration, releases, and runtime concerns.
image: meta/php/08-deployment.png
---

```nginx
server {
    listen 80;
    server_name stitcher.io;
    root /home;
    index index.php index.html;

    access_log /var/log/nginx/stitcher.io-access.log;
    error_log  /var/log/nginx/stitcher.io-error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.5-fpm.sock;
    }
}
```