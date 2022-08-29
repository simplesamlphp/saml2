<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

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
     * @return string
     */
    public static function getXsiTypeName(): string;


    /**
     * Get the namespace for the element's xsi:type.
     *
     * @return string
     */
    public static function getXsiTypeNamespaceURI(): string;


    /**
     * Get the namespace-prefix for the element's xsi:type.
     *
     * @return string
     */
    public static function getXsiTypePrefix(): string;


    /**
     * Return the xsi:type value corresponding this element.
     *
     * @return string
     */
    public function getXsiType(): string;
}
