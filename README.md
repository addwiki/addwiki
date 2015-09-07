# addwiki

[![Build Status](https://travis-ci.org/addwiki/addwiki.png?branch=master)](https://travis-ci.org/addwiki/addwiki)
[![Code Coverage](https://scrutinizer-ci.com/g/addwiki/addwiki/badges/coverage.png?s=fae232d8c82ba16e2123faa640983cb22f96f51d)](https://scrutinizer-ci.com/g/addwiki/addwiki/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/addwiki/addwiki/badges/quality-score.png?s=eda891f8ffeff635f1b36994d42370650b59e718)](https://scrutinizer-ci.com/g/addwiki/addwiki/)
[![Latest Stable Version](https://poser.pugx.org/addwiki/addwiki/version.png)](https://packagist.org/packages/addwiki/addwiki)
[![Download count](https://poser.pugx.org/addwiki/addwiki/d/total.png)](https://packagist.org/packages/addwiki/addwiki)

Issue tracker: https://phabricator.wikimedia.org/project/profile/1490/

## Installation

If composer is not installed:

    php -r "readfile('https://getcomposer.org/installer');" | php

Download and install addwiki:

    php composer.phar require "addwiki/addwiki:~0.1@dev"

Any extension package can be installed in the same way.
The binary can be found at `./vendor/bin/aww`.

## Example Usage

Set the bot up:

    aww config:setup

View your settings:

    aww config:list

Run your first scripts:

    aww task:restore-revisions --wiki localwiki --user localadmin 555
    aww task:restore-revisions --wiki localwiki --user localadmin 1 2 3

Configure a default user and wiki:

    aww config:set:default:wiki local
    aww config:set:default:user localadmin

Run scripts using the defaults:

    aww task:restore-revisions 663
    aww task:restore-revisions --summary "Custom Summary with $revid" --minor 0 --bot 0 663 777
