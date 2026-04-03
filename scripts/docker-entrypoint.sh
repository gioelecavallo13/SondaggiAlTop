#!/bin/sh
set -e

# Render (e altri PaaS) impostano PORT; in locale Docker tipicamente 80.
PORT="${PORT:-80}"

if [ -f /etc/apache2/ports.conf ]; then
  sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
fi

if [ -f /etc/apache2/sites-available/000-default.conf ]; then
  sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

exec apache2-foreground
