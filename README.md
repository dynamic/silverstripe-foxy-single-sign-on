# SilverStripe Foxy Single Sign On

An add-on module for SilverStripe Foxy that allows single sign on with your Foxy shop.

[![Build Status](https://travis-ci.org/dynamic/silverstripe-foxy-single-sign-on.svg?branch=master)](https://travis-ci.org/dynamic/silverstripe-foxy-single-sign-on)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dynamic/silverstripe-foxy-single-sign-on/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dynamic/silverstripe-foxy-single-sign-on/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/dynamic/silverstripe-foxy-single-sign-on/badges/build.png?b=master)](https://scrutinizer-ci.com/g/dynamic/silverstripe-foxy-single-sign-on/build-status/master)
[![codecov](https://codecov.io/gh/dynamic/silverstripe-foxy-single-sign-on/branch/master/graph/badge.svg)](https://codecov.io/gh/dynamic/silverstripe-foxy-single-sign-on)

[![Latest Stable Version](https://poser.pugx.org/dynamic/silverstripe-foxy-single-sign-on/v/stable)](https://packagist.org/packages/dynamic/silverstripe-foxy-single-sign-on)
[![Total Downloads](https://poser.pugx.org/dynamic/silverstripe-foxy-single-sign-on/downloads)](https://packagist.org/packages/dynamic/silverstripe-foxy-single-sign-on)
[![Latest Unstable Version](https://poser.pugx.org/dynamic/silverstripe-foxy-single-sign-on/v/unstable)](https://packagist.org/packages/dynamic/silverstripe-foxy-single-sign-on)
[![License](https://poser.pugx.org/dynamic/silverstripe-foxy-single-sign-on/license)](https://packagist.org/packages/dynamic/silverstripe-foxy-single-sign-on)

## Requirements

* SilverStripe ^4.0
* SilverStripe Foxy API ^1.0


## Installation

```
composer require dynamic/silverstripe-foxy-single-sign-on ^1.0
```


## Configuration

**Ensure the authenticator is set to "sha1_v2.4"**


```yml
SilverStripe\security\Security:
  password_encryption_algorithm: 'sha1_v2.4'

```

**Ensure the Foxy store settings are set to `SHA-1, salted (suffix)` and `enable single sign on` is checked**

**Single sign on url should be set to `http://www.example.com/foxysso`**

[Advanced Store Settings](https://admin.foxycart.com/admin.php?ThisAction=EditAdvancedFeatures)

The value for `customer password hash config` should be `40` as that is the length of the salt in SilverStripe when using `sha1_v2.4`.


## Documentation
 * [Documentation readme](docs/en/readme.md)

Add links into your docs/<language> folder here unless your module only requires minimal documentation 
in that case, add here and remove the docs folder. You might use this as a quick table of content if you
mhave multiple documentation pages.


## Maintainers
 * [Dynamic](https://www.dynamicagency.com) (<dev@dynamicagency.com>)
 
 
## Bugtracker
Bugs are tracked in the issues section of this repository. Before submitting an issue please read over 
existing issues to ensure yours is unique. 
 
If the issue does look like a new bug:
 
 - Create a new issue
 - Describe the steps required to reproduce your issue, and the expected outcome. Unit tests, screenshots 
 and screencasts can help here.
 - Describe your environment as detailed as possible: SilverStripe version, Browser, PHP version, 
 Operating System, any installed SilverStripe modules.
 
Please report security issues to the module maintainers directly. Please don't file security issues in the bugtracker.
 
 
## Development and contribution
If you would like to make contributions to the module please ensure you raise a pull request and discuss with the module maintainers.


## License
See [License](license.md)
