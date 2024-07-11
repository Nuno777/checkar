#!/bin/bash
set -e

# Instalação do PHP
apt-get update
apt-get install -y php-cli

# Iniciar servidor PHP
php -S 0.0.0.0:8080
