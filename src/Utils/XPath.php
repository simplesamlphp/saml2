<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Utils;

use Dom;
use SimpleSAML\SAML2\Constants as C;

/**
 * Compilation of utilities for XPath.
 *
 * @package simplesamlphp/saml2
 */
class XPath extends \SimpleSAML\XMLSecurity\Utils\XPath
{
    /**
     * Get a Dom\XPath object that can be used to search for SAML elements.
     *
     * @param \Dom\Node $node The document to associate to the Dom\XPath object.
     * @param bool $autoregister Whether to auto-register all namespaces used in the document
     *
     * @return \Dom\XPath A Dom\XPath object ready to use in the given document, with several
     *   saml-related namespaces already registered.
     */
    public static function getXPath(Dom\Node $node, bool $autoregister = false): Dom\XPath
    {
        $xp = parent::getXPath($node, $autoregister);

        $xp->registerNamespace('saml_protocol', C::NS_SAMLP);
        $xp->registerNamespace('saml_assertion', C::NS_SAML);
        $xp->registerNamespace('saml_metadata', C::NS_MD);

        return $xp;
    }
}
