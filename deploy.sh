ssh forge@stitcher.io '
  cd "stitcher.io"; \
  git pull; \
  php8.3 /usr/local/bin/composer install; \
  php8.3 stitcher.php; \
  echo "done"; \
'