<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Utils;

/**
 * @package simplesamlphp/saml2
 */
class Random extends \SimpleSAML\XML\Utils\Random
{
    /**
     * The fixed length of random identifiers.
     *
     * This results in the maximum of 160 bits entropy specified in paragraph 1.3.4 of the SAML 2.0 core specifications
     *
     * (41 - 1) / 2 = 20 → random_bytes(20) → 160 bits
     */
    public const int ID_LENGTH = 41;
}
