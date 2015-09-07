mediawiki-bot
==================
[![Build Status](https://travis-ci.org/addwiki/mediawiki-bot.png?branch=master)](https://travis-ci.org/addwiki/mediawiki-bot)
[![Code Coverage](https://scrutinizer-ci.com/g/addwiki/mediawiki-bot/badges/coverage.png?s=fae232d8c82ba16e2123faa640983cb22f96f51d)](https://scrutinizer-ci.com/g/addwiki/mediawiki-bot/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/addwiki/mediawiki-bot/badges/quality-score.png?s=eda891f8ffeff635f1b36994d42370650b59e718)](https://scrutinizer-ci.com/g/addwiki/mediawiki-bot/)

Issue tracker: https://phabricator.wikimedia.org/project/profile/1490/

## Installation

Install using composer!

## Example Usage

Set the bot up:

    awb config:setup

View your settings:

    awb config:list

Run your first scripts:

    awb task:restore-revisions --wiki localwiki --user localadmin 555
    awb task:restore-revisions --wiki localwiki --user localadmin 1 2 3

Configure a default user and wiki:

    awb config:set:default:wiki local
    awb config:set:default:user localadmin

Run scripts using the defaults:

    awb task:restore-revisions 663
    awb task:restore-revisions --summary "Custom Summary with $revid" --minor 0 --bot 0 663 777
