#!/bin/bash

. /home/forge/.bashrc
. ~/.nvm/nvm.sh

# Dependencies
php8.5 /usr/local/bin/composer install --no-dev
/home/forge/.bun/bin/bun install

# Backedn
php8.5 tempest cache:clear --force --internal --all
php8.5 tempest discovery:generate
php8.5 tempest migrate:up --force

# Frontend
/home/forge/.bun/bin/bun run build
php8.5 tempest cache:clear --force
php8.5 tempest static:generate /analytics

# Supervisor
sudo supervisorctl restart all
