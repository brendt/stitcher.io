#!/bin/bash

. /home/forge/.bashrc
. ~/.nvm/nvm.sh

# Dependencies
php8.4 /usr/local/bin/composer install --no-dev

# Tempest
php8.4 tempest cache:clear --force --internal --all
php8.4 tempest discovery:generate
php8.4 tempest migrate:up --force

# Supervisor
sudo supervisorctl restart all
