[![Coverage Status](https://coveralls.io/repos/github/sugarcrm-developers/php-rest-client/badge.svg?branch=main)](https://coveralls.io/github/sugarcrm-developers/php-rest-client?branch=main)
![Packagist Downloads](https://img.shields.io/packagist/dt/sugarcrm-developers/php-rest-client)
![Packagist Version](https://img.shields.io/packagist/v/sugarcrm-developers/php-rest-client)
# SugarCRM PHP REST Client

## Overview
A simple and intuitive Library for accessing a SugarCRM's REST v10+ API. Allows for Object Oriented design around accessing data from a SugarCRM system, so you can easily get your integration project underway.

## Usage
Add to any project using composer:
```
"require": {
    "sugarcrm-developers/php-rest-client": "^1.*"
}
```

### TLS certificate verification
TLS certificates are verified by default. To disable verification for a local development instance only, pass `false` as the third constructor argument. To trust a custom CA bundle, pass its file path:
```php
$client = new \Sugarcrm\REST\Client\SugarApi($server, $credentials);                 // verifies (default)
$client = new \Sugarcrm\REST\Client\SugarApi($server, $credentials, false);          // verification off (dev only)
$client = new \Sugarcrm\REST\Client\SugarApi($server, $credentials, '/path/ca.pem'); // custom CA bundle
```
Existing instances can use `setVerifyTls()` / `getVerifyTls()`.

__Check out the [Wiki](https://github.com/sugarcrm-developers/php-rest-client/wiki)__ for detailed information on 
* Installation
* Usage
* Customization
* Included endpoints
* Architecture overview

## Developing the PHP REST Client

#### Requirements:
* v1.x
  * [php 8.0+](https://php.org/)
  * [Composer](https://getcomposer.org/)

#### Steps:
1. With PHP installed, navigate to the repo folder (rest-php-client) via terminal.
2. Run 'composer install'

### Contributing:
See [CONTRIBUTING](CONTRIBUTING.md) for how you can contribute changes back into this project.


