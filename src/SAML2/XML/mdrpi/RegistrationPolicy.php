<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdrpi;

use InvalidArgumentException;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedURI;

/**
 * A localized name representing an entity's registration policy url.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/saml-metadata-rpi/v1.0/saml-metadata-rpi-v1.0.pdf
 *
 * @package simplesamlphp/saml2
 */
final class RegistrationPolicy extends AbstractLocalizedURI
{
    /** @var string */
    public const NS = 'urn:oasis:names:tc:SAML:metadata:rpi';

    /** @var string */
    public const NS_PREFIX = 'mdrpi';
}
