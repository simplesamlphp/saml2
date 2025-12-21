<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdrpi;

use SimpleSAML\SAML2\XML\md\AbstractLocalizedURI;

/**
 * A localized name representing an entity's usage policy.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/saml-metadata-rpi/v1.0/saml-metadata-rpi-v1.0.pdf
 *
 * @package simplesamlphp/saml2
 */
final class UsagePolicy extends AbstractLocalizedURI
{
    public const string NS = 'urn:oasis:names:tc:SAML:metadata:rpi';

    public const string NS_PREFIX = 'mdrpi';
}
