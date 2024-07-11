#!/usr/bin/env bash
# Exit on error
set -o errexit

# Install PHP
apt-get update
apt-get install -y php-cli

# Run the start command
php -S 0.0.0.0:80 -t public
