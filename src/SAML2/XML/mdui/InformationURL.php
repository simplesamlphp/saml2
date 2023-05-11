<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use InvalidArgumentException;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedURI;

/**
 * A localized name representing an entity's information url.
 *
 * @package simplesamlphp/saml2
 */
final class InformationURL extends AbstractLocalizedURI
{
    /** @var string */
    public const NS = 'urn:oasis:names:tc:SAML:metadata:ui';

    /** @var string */
    public const NS_PREFIX = 'mdui';
}
