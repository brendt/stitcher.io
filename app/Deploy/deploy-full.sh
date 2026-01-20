#!/bin/bash

. /home/forge/.bashrc
. ~/.nvm/nvm.sh

# Dependencies
php8.5 /usr/local/bin/composer install --no-dev
/home/forge/.bun/bin/bun install

# Tempest
php8.5 tempest cache:clear --force --internal --all
php8.5 tempest discovery:generate
php8.5 tempest migrate:up --force

# Build front-end
php8.5 tempest static:clean --force
/home/forge/.bun/bin/bun run build
php8.5 tempest cache:clear --force
php8.5 tempest view:clear --force
php8.5 tempest static:generate --verbose=true

# Supervisor
sudo supervisorctl restart all
