<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

/**
 * A collection of constants used in this library for unit testing.
 *
 * @package simplesamlphp/saml2
 */
class Constants extends \SimpleSAML\SAML2\Constants
{
    public const ENTITY_IDP = 'https://simplesamlphp.org/idp/metadata';

    public const ENTITY_SP = 'https://simplesamlphp.org/sp/metadata';

    public const ENTITY_OTHER = 'https://example.org/metadata';

    public const ENTITY_URN = 'urn:x-simplesamlphp:phpunit:entity';

    public const AUTHNCONTEXT_CLASS_REF_LOA1 = 'https://simplesamlphp.org/loa1';

    public const AUTHNCONTEXT_CLASS_REF_LOA2 = 'https://simplesamlphp.org/loa2';

    public const AUTHNCONTEXT_CLASS_REF_URN = 'urn:x-simplesamlphp:phpunit:loa3';

    public const ATTR_URN = 'urn:x-simplesamlphp:phpunit:attribute';

    public const LOCATION_A = 'https://simplesamlphp.org/some/endpoint';

    public const LOCATION_B = 'https://simplesamlphp.org/other/endpoint';

    public const NAMESPACE = 'urn:x-simplesamlphp:namespace';

    public const PROFILE_1 = 'urn:x-simplesamlphp:profile:one';

    public const PROFILE_2 = 'urn:x-simplesamlphp:profile:two';

    public const PROTOCOL = 'urn:x-simplesamlphp:protocol';

    public const MSGID = '_s2a0da3504aff978b0f8c80f6a62c713c4a2f64c5b';
}
