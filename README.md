# YAML file sort checker [![Build Status](https://travis-ci.org/mhujer/yaml-sort-checker.svg?branch=master)](https://travis-ci.org/mhujer/yaml-sort-checker)

[![Latest Stable Version](https://poser.pugx.org/mhujer/yaml-sort-checker/version.png)](https://packagist.org/packages/mhujer/yaml-sort-checker) [![Total Downloads](https://poser.pugx.org/mhujer/yaml-sort-checker/downloads.png)](https://packagist.org/packages/mhujer/yaml-sort-checker) [![License](https://poser.pugx.org/mhujer/yaml-sort-checker/license.svg)](https://packagist.org/packages/mhujer/yaml-sort-checker)

This library helps you to keep YAML file sorted to prevent unnecessary merge conflicts.

Typical example is when two developers register a new service in `services.yml`. If they both add it to the end, it unevitably will lead to a merge conflict. However, when the services are alphabetically sorted, the probability of merge conflict is much lower (because the added services probably won't clash).

![yaml-sort-checker DMEO](./docs/yaml-sort-checker-demo.png)

Usage
----
1. Install the latest version with `composer require-dev mhujer/yaml-sort-checker`
2. Create a configuration file `yaml-sort-checker.yml` in project root with list of the files for checking, see the  [example configuration for Symfony app](/docs/symfony-config/yaml-sort-checker.yml).
3. Run `vendor/bin/yaml-sort-checker` (depends on where you have your Composer bin directory)
4. Exclude the yaml keys you don't want to sort - e.g. it makes more sense to have them unsorted (see the [example configuration](/docs/symfony-config/yaml-sort-checker.yml))


Requirements
------------
Works with PHP 7.1 or higher.

Submitting bugs and feature requests
------------------------------------
Bugs and feature request are tracked on [GitHub](https://github.com/mhujer/yaml-sort-checker/issues)

Author
------
[Martin Hujer](https://www.martinhujer.cz) 

Changelog
----------

## 1.0.0 (2017-01-28)
- initial release
