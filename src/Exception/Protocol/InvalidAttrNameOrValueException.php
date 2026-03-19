<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception\Protocol;

use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * A SAML error indicating that unexpected or invalid content was encountered
 *   within a <saml:Attribute> or <saml:AttributeValue> element.
 *
 * @package simplesamlphp/saml2
 */
class InvalidAttrNameOrValueException extends ProtocolViolationException
{
    public const string DEFAULT_MESSAGE = 'Invalid attribute name or value.';
}
