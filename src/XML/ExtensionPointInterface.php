<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

use SimpleSAML\XMLSchema\Type\{AnyURIValue, NCNameValue, QNameValue};

/**
 * Interface for several extension points objects.
 *
 * @package simplesamlphp/saml2
 */
interface ExtensionPointInterface
{
    /**
     * Get the local name for the element's xsi:type.
     *
     * @return \SimpleSAML\XMLSchema\Type\NCNameValue
     */
    public static function getXsiTypeName(): NCNameValue;


    /**
     * Get the namespace for the element's xsi:type.
     *
     * @return \SimpleSAML\XMLSchema\Type\AnyURIValue
     */
    public static function getXsiTypeNamespaceURI(): AnyURIValue;


    /**
     * Get the namespace-prefix for the element's xsi:type.
     *
     * @return \SimpleSAML\XMLSchema\Type\NCNameValue
     */
    public static function getXsiTypePrefix(): NCNameValue;


    /**
     * Return the xsi:type value corresponding this element.
     *
     * @return \SimpleSAML\XMLSchema\Type\QNameValue
     */
    public function getXsiType(): QNameValue;
}
