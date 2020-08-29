<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\xenc\EncryptionMethod;

/**
 * A class implementing the md:EncryptionMethod element.
 *
 * @package simplesamlphp/saml2
 */
class EncryptionMethod extends EncryptionMethod
{
    /** @var string */
    public const NS = Constants::NS_MD;

    /** @var string */
    public const NS_PREFIX = 'md';
}
