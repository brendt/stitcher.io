#!/bin/bash

. /home/forge/.bashrc
. ~/.nvm/nvm.sh

# Dependencies
php8.5 /usr/local/bin/composer install --no-dev

# Tempest
php8.5 tempest cache:clear --force --internal --all
php8.5 tempest discovery:generate
php8.5 tempest migrate:up --force

# Supervisor
sudo supervisorctl restart all
