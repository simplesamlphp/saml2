SimpleSAMLphp SAML2 library
===========================

A PHP library for SAML2 related functionality. Extracted from [SimpleSAMLphp](http://www.simplesaml.org),
used by [OpenConext](http://www.openconext.org).
This library is a collaboration between [UNINETT](http://uninett.no) and [SURFnet](http://surfnet.nl).


Before you use it
-----------------
**DO NOT USE THIS LIBRARY UNLESS YOU ARE INTIMATELY FAMILIAR WITH THE SAML2 SPECIFICATION.**

If you are not familiar with the SAML2 specification and are simply looking to connect your application using SAML2,
you should probably use [SimpleSAMLphp](http://www.simplesaml.org).

While this library is tagged as stable it is currently not very developer friendly and it's API is likely to change
significantly in the future. It is however a starting point for collaboration between parties.
So let us know what you would like to see in a PHP SAML2 library.

Note that the **HTTP Artifact Binding and SOAP client not work** outside of SimpleSAMLphp.


Usage
-----

* Install with [Composer](http://getcomposer.org/doc/00-intro.md), add the following in your composer.json:

```json
{
    "require": {
        "simplesamlphp/saml2": "0.1.*"
    }
}
```

Then run ```composer update```.

* Inject the required dependencies in the DI Container using [Pimple](http://pimple.sensiolabs.org).

* Use at will.
Example:
```php
    // Use Composers autoloading
    require 'vendor/autoload.php';

    // Set up the container (out of scope for example)
    require 'container.php';
    SAML2_Compat_ContainerSingleton::setContainer($container);

    // Set up an AuthnRequest
    $request = new SAML2_AuthnRequest();
    $request->setId(SAML2_Utils::generateId());
    $request->setIssuer('https://sp.example.edu');
    $request->setDestination('https://idp.example.edu');

    // Send it off using the HTTP-Redirect binding
    $binding = new SAML2_HTTPRedirect();
    $binding->send($request);
```


Dependencies
------------

### logger
Type: Object

[PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) compatible Logger.

### id_generator_fn
Type: callable
Arguments: None

Must conform to SAML2 spec, generated values:

-  Must be between 128 and 160 bit in length
-  Must not collide with other identifiers generated (must be 'globally unique')
-  Must start with a letter or underscore
-  Can only contain letters, digits, underscores, hyphens, and periods.

### debug_message_fn
Type: callable
Arguments: \(DOMElement|string xmlMessage, string type\)

Hook to log an incoming message.
Type can be either:

- **in** XML received from third party
- **out** XML that will be sent to third party
- **encrypt** XML that is about to be encrypted
- **decrypt** XML that was just decrypted

### redirect_fn
Type: callable
Arguments: (string $url, array $data)

Make the user send a GET request to the given URL, with the given data appended.

### redirect_post_fn
Type: callable
Arguments: \(string $url, array $data\)

Make the user send a POST request to the given URL, with the given data.


License
-------
This library is licensed under the LGPL license version 2.1. For more details see [LICENSE](https://raw.github.com/simplesamlphp/saml2/master/LICENSE).
