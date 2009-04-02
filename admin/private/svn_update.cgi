#!/bin/sh
set -f
echo "Content-type: text/plain; charset=iso-8859-1"
echo
/usr/bin/svn update /var/www/www.dynamicarcade.co.uk/SpaceMMO/ --username Production --password meow
