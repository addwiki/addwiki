#!/bin/bash

set -x

if [ ! -f entrypoint-done.txt ]; then

    # Wait for the DB to be ready?
    /wait-for-it.sh $MYSQL_SERVER:3306 -t 300
    sleep 1
    /wait-for-it.sh $MYSQL_SERVER:3306 -t 300

    # Install MediaWiki
    php maintenance/install.php --server="http://localhost:8877" --scriptpath= --dbtype mysql --dbuser $MYSQL_USER --dbpass $MYSQL_PASSWORD --dbserver $MYSQL_SERVER --lang en --dbname $MYSQL_DATABASE --pass LongCIPass123 SiteName CIUser

    # Settings for extensions
    echo "wfLoadExtension( 'OAuth' );" >> LocalSettings.php
    echo "\$wgGroupPermissions['sysop']['mwoauthproposeconsumer'] = true;" >> LocalSettings.php
    echo "\$wgGroupPermissions['sysop']['mwoauthmanageconsumer'] = true;" >> LocalSettings.php
    echo "\$wgGroupPermissions['sysop']['mwoauthviewprivate'] = true;" >> LocalSettings.php
    echo "\$wgGroupPermissions['sysop']['mwoauthupdateownconsumer'] = true;" >> LocalSettings.php
    echo "require_once \"\$IP/extensions/Wikibase/vendor/autoload.php\";" >> LocalSettings.php
    echo "require_once \"\$IP/extensions/Wikibase/repo/Wikibase.php\";" >> LocalSettings.php
    echo "require_once \"\$IP/extensions/Wikibase/repo/ExampleSettings.php\";" >> LocalSettings.php

    # Settings to make testing easier
    echo "\$wgGroupPermissions['*']['noratelimit'] = true;" >> LocalSettings.php
    echo "\$wgEnableUploads = true;" >> LocalSettings.php

    # Update MediaWiki & Extensions
    php maintenance/update.php --quick

    ## Run some needed scripts
    # Add a site for Wikibase sitelinks
    php maintenance/addSite.php mywiki default --interwiki-id --pagepath http://localhost:8877/index.php?title=\$1 --filepath http://localhost:8877/\$1
    echo "\$wgWBRepoSettings['siteLinkGroups'] = [ 'default' ];" >> LocalSettings.php
    # Add an OAuth Consumer
    php maintenance/resetUserEmail.php --no-reset-password CIUser CIUser@addwiki.github.io
    php extensions/OAuth/maintenance/addwikiAddOauth.php --approve --callbackUrl https://CiConsumerUrl \
    --callbackIsPrefix true --user CIUser --name CIConsumer --description CIConsumer --version 1.1.0 \
    --grants highvolume --jsonOnSuccess > createOAuthConsumer.json

    # Mark the entrypoint as having run!
    echo "entrypoint done!" > entrypoint-done.txt

fi

# Run apache
apache2-foreground
