# addwiki-cli

[![GitHub issue custom search in repo](https://img.shields.io/github/issues-search/addwiki/addwiki?label=issues&query=is%3Aissue%20is%3Aopen%20%5Baddwiki-cli%5D)](https://github.com/addwiki/addwiki/issues?q=is%3Aissue+is%3Aopen+%5Baddwiki-cli%5D+)

## Installation

Download and use a phar:

    NOT YET IMPLEMENTED!

You can build a phar of this repo using https://github.com/clue/phar-composer

    ~/phar-composer.phar build ~/git/github/addwiki/addwiki/ ~/aww.phar

Download and install using composer:

    composer create-project addwiki/addwiki addwiki dev-master

The binary can be found at `./addwiki/aww`.

## Example Usage

Set the bot up:

    aww config:setup

View your settings:

    aww config:list

Configure a default user and wiki:

    aww config:set:default:wiki local
    aww config:set:default:user localadmin

For docs of extra specific commands please see the commands repos README files!
