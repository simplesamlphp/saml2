<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdattr;

use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\AbstractElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractMdattrElement extends AbstractElement
{
    /** @var string */
    public const NS = C::NS_MDATTR;

    /** @var string */
    public const NS_PREFIX = 'mdattr';

    /** @var string */
    public const SCHEMA = 'resources/schemas/sstc-metadata-attr.xsd';
}
