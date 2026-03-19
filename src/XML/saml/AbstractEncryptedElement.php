<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use SimpleSAML\SAML2\XML\EncryptedElementTrait;
use SimpleSAML\XMLSecurity\XML\EncryptedElementInterface;

/**
 * Class representing an encrypted element.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractEncryptedElement extends AbstractSamlElement implements EncryptedElementInterface
{
    use EncryptedElementTrait;
}
