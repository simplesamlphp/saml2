<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use SAML2\Constants;

/**
 * A class implementing the md:EncryptionMethod element.
 *
 * @package simplesamlphp/saml2
 */
class EncryptionMethod extends \SAML2\XML\xenc\EncryptionMethod
{
    /** @var string */
    public const NS = Constants::NS_MD;

    /** @var string */
    public const NS_PREFIX = 'md';
}
