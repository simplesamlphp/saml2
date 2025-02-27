<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};
use SimpleSAML\XMLSecurity\XML\xenc\AbstractEncryptionMethod;

/**
 * A class implementing the md:EncryptionMethod element.
 *
 * @package simplesamlphp/saml2
 */
class EncryptionMethod extends AbstractEncryptionMethod implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /** @var string */
    public const NS = AbstractMdElement::NS;

    /** @var string */
    public const NS_PREFIX = AbstractMdElement::NS_PREFIX;

    /** @var string */
    public const SCHEMA = AbstractMdElement::SCHEMA;
}
