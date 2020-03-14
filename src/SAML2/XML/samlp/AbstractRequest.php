<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use Exception;
use SAML2\Utilities\Temporal;
use SAML2\Utils;
use SAML2\XML\saml\Issuer;
use Webmozart\Assert\Assert;

/**
 * Base class for all SAML 2 request messages.
 *
 * Implements samlp:RequestAbstractType. All of the elements in that type is
 * stored in the \SAML2\XML\AbstractMessage class, and this class is therefore empty. It
 * is included mainly to make it easy to separate requests from responses.
 *
 * @package SimpleSAMLphp
 */
abstract class AbstractRequest extends AbstractMessage
{
}
