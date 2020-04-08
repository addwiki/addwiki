.. addwiki documentation master file, created by
   sphinx-quickstart on Fri Sep 16 16:13:21 2016.
   You can adapt this file completely to your liking, but it should at least
   contain the root `toctree` directive.

Welcome to the addwiki documentation
===================================

The addwiki project is split into multiple libraries which can be seen below.

.. toctree::
   :maxdepth: 2

mediawiki-api-base_
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
.. _mediawiki-api-base: https://addwiki.readthedocs.io/projects/mediawiki-api-base

.. image:: https://readthedocs.org/projects/addwiki-mediawiki-api-base/badge/?version=latest
    :target: https://addwiki.readthedocs.io/projects/mediawiki-api-base

.. image:: https://travis-ci.org/addwiki/mediawiki-api-base.svg?branch=master
    :target: https://travis-ci.org/addwiki/mediawiki-api-base

.. image:: https://scrutinizer-ci.com/g/addwiki/mediawiki-api-base/badges/coverage.png?b=master
    :target: https://scrutinizer-ci.com/g/addwiki/mediawiki-api-base/?branch=master

.. image:: https://scrutinizer-ci.com/g/addwiki/mediawiki-api-base/badges/quality-score.png?b=master
    :target: https://scrutinizer-ci.com/g/addwiki/mediawiki-api-base/?branch=master

.. image:: https://poser.pugx.org/addwiki/mediawiki-api-base/version.png
    :target: https://packagist.org/packages/addwiki/mediawiki-api-base

.. image:: https://poser.pugx.org/addwiki/mediawiki-api-base/d/total.png
    :target: https://packagist.org/packages/addwiki/mediawiki-api-base

This library provides basic access to the mediawiki api.
This library features simple methods allowing you to login, logout and do both GET and POST requests.
This library should work with most if not all mediawiki versions due to its simplicity.

mediawiki-api_
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
.. _mediawiki-api: https://addwiki.readthedocs.io/projects/mediawiki-api

.. image:: https://readthedocs.org/projects/addwiki-mediawiki-api/badge/?version=latest
    :target: https://addwiki.readthedocs.io/projects/mediawiki-api

.. image:: https://travis-ci.org/addwiki/mediawiki-api.svg?branch=master
    :target: https://travis-ci.org/addwiki/mediawiki-api

.. image:: https://scrutinizer-ci.com/g/addwiki/mediawiki-api/badges/coverage.png?b=master
    :target: https://scrutinizer-ci.com/g/addwiki/mediawiki-api/?branch=master

.. image:: https://scrutinizer-ci.com/g/addwiki/mediawiki-api/badges/quality-score.png?b=master
    :target: https://scrutinizer-ci.com/g/addwiki/mediawiki-api/?branch=master

.. image:: https://poser.pugx.org/addwiki/mediawiki-api/version.png
    :target: https://packagist.org/packages/addwiki/mediawiki-api

.. image:: https://poser.pugx.org/addwiki/mediawiki-api/d/total.png
    :target: https://packagist.org/packages/addwiki/mediawiki-api

This library adds classes specific to mediawiki-core api requests.

mediawiki-datamodel_
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
.. _mediawiki-datamodel: https://addwiki.readthedocs.io/projects/mediawiki-datamodel

.. image:: https://readthedocs.org/projects/addwiki-mediawiki-datamodel/badge/?version=latest
    :target: https://addwiki.readthedocs.io/projects/mediawiki-datamodel

.. image:: https://travis-ci.org/addwiki/mediawiki-datamodel.svg?branch=master
    :target: https://travis-ci.org/addwiki/mediawiki-datamodel

.. image:: https://scrutinizer-ci.com/g/addwiki/mediawiki-datamodel/badges/coverage.png?b=master
    :target: https://scrutinizer-ci.com/g/addwiki/mediawiki-datamodel/?branch=master

.. image:: https://scrutinizer-ci.com/g/addwiki/mediawiki-datamodel/badges/quality-score.png?b=master
    :target: https://scrutinizer-ci.com/g/addwiki/mediawiki-datamodel/?branch=master

.. image:: https://poser.pugx.org/addwiki/mediawiki-datamodel/version.png
    :target: https://packagist.org/packages/addwiki/mediawiki-datamodel

.. image:: https://poser.pugx.org/addwiki/mediawiki-datamodel/d/total.png
    :target: https://packagist.org/packages/addwiki/mediawiki-datamodel

This library adds classes trying to replicate the internal data structures and classes of mediawiki.
These are used by the api, db and dump libraries (as well as some other extensions).

wikibase-api_
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
.. _wikibase-api: https://addwiki.readthedocs.io/projects/wikibase-api

.. image:: https://readthedocs.org/projects/addwiki-wikibase-api/badge/?version=latest
    :target: https://addwiki.readthedocs.io/projects/wikibase-api

.. image:: https://travis-ci.org/addwiki/wikibase-api.svg?branch=master
    :target: https://travis-ci.org/addwiki/wikibase-api

.. image:: https://scrutinizer-ci.com/g/addwiki/wikibase-api/badges/coverage.png?b=master
    :target: https://scrutinizer-ci.com/g/addwiki/wikibase-api/?branch=master

.. image:: https://scrutinizer-ci.com/g/addwiki/wikibase-api/badges/quality-score.png?b=master
    :target: https://scrutinizer-ci.com/g/addwiki/wikibase-api/?branch=master

.. image:: https://poser.pugx.org/addwiki/wikibase-api/version.png
    :target: https://packagist.org/packages/addwiki/wikibase-api

.. image:: https://poser.pugx.org/addwiki/wikibase-api/d/total.png
    :target: https://packagist.org/packages/addwiki/wikibase-api

This library adds classes specific to using the wikibase api.

In Development
-------------------------------------

There are various libraries that have not yet had their first release, but have seen some development.

 - mediawiki-commands
 - wikibase-commands
 - wikimedia-commands

Other code
-------------------------------------

And there are other libraries that are in even worse state...

 - mediawiki-sitematrix-api
 - mediawiki-flow-api
 - mediawiki-services
 - mediawiki-dump

One day these will all be tied up into some sort of framework.
