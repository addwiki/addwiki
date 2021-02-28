#!/bin/bash

set -x

# Wait for the DB to be ready?
/wait-for-it.sh $MYSQL_SERVER:3306 -t 300
sleep 1
/wait-for-it.sh $MYSQL_SERVER:3306 -t 300

# Install MediaWiki
php maintenance/install.php --server="http://localhost:8877" --scriptpath= --dbtype mysql --dbuser $MYSQL_USER --dbpass $MYSQL_PASSWORD --dbserver $MYSQL_SERVER --lang en --dbname $MYSQL_DATABASE --pass LongCIPass123 SiteName CIUser

# Settings for extensions
echo "wfLoadExtension( 'OAuth' );" >> LocalSettings.php

# Update MediaWiki & Extensions
php maintenance/update.php --quick

# Run apache
apache2-foreground
