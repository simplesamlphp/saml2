SimpleSAMLphp SAML2 library
===========================
[![Build Status](https://travis-ci.org/simplesamlphp/saml2.png?branch=feature/fix-build)](https://travis-ci.org/simplesamlphp/saml2) [![Coverage Status](https://img.shields.io/coveralls/simplesamlphp/saml2.svg)](https://coveralls.io/r/simplesamlphp/saml2)


A PHP library for SAML2 related functionality. Extracted from [SimpleSAMLphp](https://www.simplesamlphp.org),
used by [OpenConext](https://www.openconext.org).
This library started as a collaboration between [UNINETT](https://www.uninett.no) and [SURFnet](https://www.surfnet.nl) but everyone is invited to contribute.


Before you use it
-----------------
**DO NOT USE THIS LIBRARY UNLESS YOU ARE INTIMATELY FAMILIAR WITH THE SAML2 SPECIFICATION.**

If you are not familiar with the SAML2 specification and are simply looking to connect your application using SAML2,
you should probably use [SimpleSAMLphp](https://www.simplesamlphp.org).

While this library is tagged as stable it is currently not very developer friendly and its API is likely to change
significantly in the future. It is however a starting point for collaboration between parties.
So let us know what you would like to see in a PHP SAML2 library.

Note that the **HTTP Artifact Binding and SOAP client do not work** outside of SimpleSAMLphp.

Which version to pick?
----------------------
It is **strongly recommended** to use the latest stable version of the `3.x` range as that is the currently supported version. 

The `1.x` range should be considered deprecated. This means it will receive fixes and, if required,
 functionality may be backported. This version should only be relied on if migrating the project to
 the `2.x` range cannot be done yet.

The `0.x` range is discontinued and will no longer receive any fixes or features. The `0.x` range however
 is functionally the same as the `1.x` range. Should your project or a dependency of your project rely on a `0.x` version
 [composer inline aliasing](https://getcomposer.org/doc/articles/aliases.md#require-inline-alias) will help, by using
 `composer require "simplesamlphp/saml2:1.7.0 as 0.8"` allows to install 1.7.0 as if 0.8 were installed.

Usage
-----

* Install with [Composer](https://getcomposer.org/doc/00-intro.md), run the following command in your project:

```bash
composer require simplesamlphp/saml2:^3.0
```

* Provide the required external dependencies by extending and implementing the ```SAML2\Compat\AbstractContainer```
  then injecting it in the ContainerSingleton (see example below).

* **Make sure you've read the security section below**

* Use at will.
Example:
```php
    // Use Composers autoloading
    require 'vendor/autoload.php';

    // Implement the Container interface (out of scope for example)
    require 'container.php';
    SAML2\Compat\ContainerSingleton::setContainer($container);

    // Set up an AuthnRequest
    $request = new SAML2\AuthnRequest();
    $request->setId($container->generateId());
    $request->setIssuer('https://sp.example.edu');
    $request->setDestination('https://idp.example.edu');

    // Send it off using the HTTP-Redirect binding
    $binding = new SAML2\HTTPRedirect();
    $binding->send($request);
```

Security
--------
* Should you need to create a DOMDocument instance, use the `SAML2\DOMDocumentFactory` to create DOMDocuments from
  either a string (`SAML2\DOMDocumentFactory::fromString($theXmlAsString)`), a file (`SAML2\DOMDocumentFactory::fromFile($pathToTheFile)`)
  or just a new instance (`SAML2\DOMDocumentFactory::create()`). This in order to protect yourself against the
  [XXE Processing Vulnerability](https://www.owasp.org/index.php/XML_External_Entity_(XXE)_Processing), as well as
  [XML Entity Expansion](https://phpsecurity.readthedocs.org/en/latest/Injection-Attacks.html#defenses-against-xml-entity-expansion) attacks

License
-------
This library is licensed under the LGPL license version 2.1.
For more details see [LICENSE](https://raw.github.com/simplesamlphp/saml2/master/LICENSE).
