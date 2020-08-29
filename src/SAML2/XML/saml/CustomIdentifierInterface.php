<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

/**
 * Interface that must be implemented by custom extensions of BaseID.
 *
 * @package simplesamlphp/saml2
 */
interface CustomIdentifierInterface extends BaseIdentifierInterface
{
    /**
     * Return the xsi:type value corresponding this identifier.
     *
     * @return string
     */
    public static function getXsiType(): string;
}
