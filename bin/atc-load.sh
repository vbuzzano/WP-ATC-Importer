#!/bin/sh

#parameter1 is the programm's name (atc-load.sh)
#parameter2 is the site URL

retour=`curl  -L -o "$1" "$2"`


echo $retour
today=`date`
echo $today
