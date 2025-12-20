<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSecurity\XML\xenc\AbstractEncryptionMethod;

/**
 * A class implementing the md:EncryptionMethod element.
 *
 * @package simplesamlphp/saml2
 */
class EncryptionMethod extends AbstractEncryptionMethod implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    public const string NS = AbstractMdElement::NS;

    public const string NS_PREFIX = AbstractMdElement::NS_PREFIX;

    public const string SCHEMA = AbstractMdElement::SCHEMA;
}
