<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\aslo;

use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\AbstractElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @see https://docs.oasis-open.org/security/saml/Post2.0/saml-async-slo/v1.0/saml-async-slo-v1.0.pdf
 * @package simplesamlphp/saml2
 */
abstract class AbstractAsloElement extends AbstractElement
{
    public const string NS = C::NS_ASLO;

    public const string NS_PREFIX = 'aslo';

    public const string SCHEMA = 'resources/schemas/saml-async-slo-v1.0.xsd';
}
