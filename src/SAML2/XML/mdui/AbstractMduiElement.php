<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use SimpleSAML\XML\AbstractElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractMduiElement extends AbstractElement
{
    /** @var string */
    public const NS = 'urn:oasis:names:tc:SAML:metadata:ui';

    /** @var string */
    public const NS_PREFIX = 'mdui';
}
