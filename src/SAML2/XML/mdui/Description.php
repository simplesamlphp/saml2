<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;

/**
 * A localized name representing an entity's description.
 *
 * @package simplesamlphp/saml2
 */
final class Description extends AbstractLocalizedName
{
    /** @var string */
    public const NS = 'urn:oasis:names:tc:SAML:metadata:ui';

    /** @var string */
    public const NS_PREFIX = 'mdui';
}
