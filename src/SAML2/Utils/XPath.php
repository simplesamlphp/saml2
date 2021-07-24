<?php

namespace SimpleSAML\SAML2\Utils;

use DOMNode;
use DOMXPath;
use SimpleSAML\SAML2\Constants as C;

/**
 * Compilation of utilities for XPath.
 *
 * @package simplesamlphp/saml2
 */
class XPath extends \SimpleSAML\XMLSecurity\Utils\XPath
{
    /**
     * Get a DOMXPath object that can be used to search for SAML elements.
     *
     * @param \DOMNode $node The document to associate to the DOMXPath object.
     *
     * @return \DOMXPath A DOMXPath object ready to use in the given document, with several
     *   saml-related namespaces already registered.
     */
    public static function getXPath(DOMNode $node): DOMXPath
    {
        $xp = parent::getXPath($node);
        $xp->registerNamespace('soap-env', C::NS_SOAP);
        $xp->registerNamespace('saml_protocol', C::NS_SAMLP);
        $xp->registerNamespace('saml_assertion', C::NS_SAML);
        $xp->registerNamespace('saml_metadata', C::NS_MD);

        return $xp;
    }
}
