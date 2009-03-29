#!/bin/sh
set -f
echo "Content-type: text/plain; charset=iso-8859-1"
echo
/usr/bin/svn update /home/thethiefmaster/dynamicarcade.co.uk/SpaceMMO/ --username Production --password meow
