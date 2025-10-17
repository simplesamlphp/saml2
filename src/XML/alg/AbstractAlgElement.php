<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\alg;

use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\AbstractElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @see http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-algsupport-v1.0-cs01.pdf
 * @package simplesamlphp/saml2
 */
abstract class AbstractAlgElement extends AbstractElement
{
    /** @var string */
    public const NS = C::NS_ALG;

    /** @var string */
    public const NS_PREFIX = 'alg';

    /** @var string */
    public const SCHEMA = 'resources/schemas/sstc-saml-metadata-algsupport-v1.0.xsd';
}
