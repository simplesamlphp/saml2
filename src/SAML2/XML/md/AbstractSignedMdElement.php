<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use SAML2\XML\SignedElementTrait;
use SAML2\XML\SignedElementInterface;

/**
 * Abstract class that represents a signed metadata element.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractSignedMdElement extends AbstractMdElement implements SignedElementInterface
{
    use SignedElementTrait;
}
