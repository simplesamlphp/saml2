<?php

declare(strict_types=1);

namespace SAML2;

use DOMDocument;
use SimpleSAML\XML\DOMDocumentFactory as BaseDOMDocumentFactory;

/**
 * @package SimpleSAMLphp
 * @deprecated Use \SimpleSAML\XML\DOMDocumentFactory instead (simplesamlphp/xml-common)
 */
class DOMDocumentFactory
{
    /**
     * Constructor for DOMDocumentFactory.
     * This class should never be instantiated
     */
    private function __construct()
    {
    }


    /**
     * @param string $xml
     *
     * @return \DOMDocument
     */
    public static function fromString(string $xml): DOMDocument
    {
        return BaseDOMDocumentFactory::fromString($xml);
    }


    /**
     * @param string $file
     *
     * @return \DOMDocument
     */
    public static function fromFile(string $file): DOMDocument
    {
        return BaseDOMDocumentFactory::fromFile($file);
    }


    /**
     * @return \DOMDocument
     */
    public static function create(): DOMDocument
    {
        return BaseDOMDocumentFactory::create('1.0', 'UTF-8');
    }
}
