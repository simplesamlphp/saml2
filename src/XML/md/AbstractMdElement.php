<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\AbstractElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractMdElement extends AbstractElement
{
    /** @var string */
    public const NS = C::NS_MD;

    /** @var string */
    public const NS_PREFIX = 'md';

    /** @var string */
    public const SCHEMA = 'resources/schemas/saml-schema-metadata-2.0.xsd';
}
