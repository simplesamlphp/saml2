<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XMLSecurity\XML\xenc\AbstractEncryptionMethod;

/**
 * A class implementing the md:EncryptionMethod element.
 *
 * @package simplesamlphp/saml2
 */
class EncryptionMethod extends AbstractEncryptionMethod
{
    /** @var string */
    public const NS = C::NS_MD;

    /** @var string */
    public const NS_PREFIX = 'md';
}
