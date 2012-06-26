#!/bin/sh

#parameter1 is reccurrence in minutes (e.g. 5)
#parameter2 is the programm's name (atc-load.sh)
#parameter3 is the file for logging for script2
#parameter4 is the parameter of the cron's programm (site URL)
#parameter5 is the file for logging for script1


#nouvelleLigne="*/1 * * * * php -f /opt/lampp/htdocs/wordpress/wp-content/plugins/ATC/atc-load.php >> /opt/lampp/htdocs/wordpress/wp-content/plugins/ATC/cron.log 2>&1"
newLine="*/$1 * * * * sh $2 $3 $4 >> $5  2>&1"

echo "$newLine" | crontab -


