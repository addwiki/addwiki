# addwiki-cli

On Packagist:
[![Latest Stable Version](https://poser.pugx.org/addwiki/addwiki/version.png)](https://packagist.org/packages/addwiki/addwiki)
[![Download count](https://poser.pugx.org/addwiki/addwiki/d/total.png)](https://packagist.org/packages/addwiki/addwiki)

Issue tracker: https://phabricator.wikimedia.org/project/profile/1490/

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
