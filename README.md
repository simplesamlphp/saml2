# SimpleSAMLphp SAML2 library

![CI](https://github.com/simplesamlphp/saml2/actions/workflows/php.yml/badge.svg)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/simplesamlphp/saml2/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/simplesamlphp/saml2/?branch=master)
[![Coverage Status](https://codecov.io/gh/simplesamlphp/saml2/branch/master/graph/badge.svg)](https://codecov.io/gh/simplesamlphp/saml2)
[![Type coverage](https://shepherd.dev/github/simplesamlphp/saml2/coverage.svg)](https://shepherd.dev/github/simplesamlphp/saml2)
[![Psalm Level](https://shepherd.dev/github/simplesamlphp/saml2/level.svg)](https://shepherd.dev/github/simplesamlphp/saml2)

A PHP library for SAML2 related functionality.

It is used by several products, most notably [SimpleSAMLphp](https://www.simplesamlphp.org) and [OpenConext](https://www.openconext.org).

## Before you use it

**DO NOT USE THIS LIBRARY UNLESS YOU ARE INTIMATELY FAMILIAR WITH THE SAML2 SPECIFICATION.**

If you are not familiar with the SAML2 specification and are simply looking to connect your application using SAML2,
you should probably use [SimpleSAMLphp](https://www.simplesamlphp.org).

Note that the **HTTP Artifact Binding and SOAP client do not work** outside of SimpleSAMLphp.

## Which version to pick?

The latest released version (`4.x` range) is the _preferred version_.
The `3.x branch` is our LTS branch and will be supported as long as supported releases of [SimpleSAMLphp](https://www.simplesamlphp.org) are using this branch.

All other branches (`3.x` and earlier) are no longer supported and will not receive any maintenance or
(security) fixes. Do not use these versions.

We conform to [Semantic Versioning](https://semver.org/).
Be sure to check the [UPGRADING.md](UPGRADING.md) file if you are upgrading from an older version. Here
you will find instructions on how to deal with BC breaking changes between versions.

## Usage

* Install with [Composer](https://getcomposer.org/doc/00-intro.md), run the following command in your project:

```bash
composer require simplesamlphp/saml2:^4.0
```

* Provide the required external dependencies by extending and implementing the ```\SimpleSAML\SAML2\Compat\AbstractContainer```
  then injecting it in the ContainerSingleton (see example below).

* **Make sure you've read the security section below**.

* Use at will.

Example:

```php
    // Use Composers autoloading
    require 'vendor/autoload.php';

    // Implement the Container interface (out of scope for example)
    require 'container.php';
    \SimpleSAML\SAML2\Compat\ContainerSingleton::setContainer($container);

    // Create Issuer
    $issuer = new \SimpleSAML\SAML2\XML\saml\Issuer('https://sp.example.edu');

    // Instantiate SAML2 Random utils
    $randomUtils = new \SimpleSAML\SAML2\Utils\Random();

    // Set up an AuthnRequest
    $request = new \SimpleSAML\SAML2\XML\samlp\AuthnRequest(
        $issuer,
        $randomUtils->generateId(),
        null,
        'https://idp.example.edu'
    );

    // Send it off using the HTTP-Redirect binding
    $binding = new \SimpleSAML\SAML2\HTTPRedirect();
    $binding->send($request);
```

## License

This library is licensed under the LGPL license version 2.1.
For more details see [LICENSE](https://raw.github.com/simplesamlphp/saml2/master/LICENSE).
