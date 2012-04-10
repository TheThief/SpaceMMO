#!/bin/sh
set -f
echo "Content-type: text/plain; charset=utf8"
echo
svn update /var/www/www.dynamicarcade.co.uk/SpaceMMO --username Production --password meow
