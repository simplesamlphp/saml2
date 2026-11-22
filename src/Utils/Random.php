<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Utils;

use function bin2hex;
use function random_bytes;

/**
 * @package simplesamlphp/saml2
 */
class Random
{
    /**
     * The fixed length of random identifiers.
     *
     * This results in the maximum of 160 bits entropy specified in paragraph 1.3.4 of the SAML 2.0 core specifications
     *
     * (41 - 1) / 2 = 20 → random_bytes(20) → 160 bits
     */
    public const int ID_LENGTH = 41;


    /**
     * This function will generate a unique ID that is valid for use
     * in an xs:ID attribute
     */
    public function generateID(): string
    {
        return '_' . bin2hex(random_bytes((self::ID_LENGTH - 1) / 2));
    }
}
