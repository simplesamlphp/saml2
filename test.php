<?php

include('vendor/autoload.php');

use SimpleSAML\XML\DOMDocumentFactory;

//$doc = DOMDocumentFactory::fromString('<saml:AttributeValue xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"><saml:Audience>audience</saml:Audience></saml:AttributeValue>');
$doc = DOMDocumentFactory::fromString('<saml:AttributeValue xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">test</saml:AttributeValue>');
var_dump($doc->documentElement->childElementCount);
