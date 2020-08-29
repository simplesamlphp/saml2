<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\SAML2\XML\SignedElementTrait;
use SimpleSAML\SAML2\XML\SignedElementInterface;

/**
 * Abstract class that represents a signed metadata element.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractSignedMdElement extends AbstractMdElement implements SignedElementInterface
{
    use SignedElementTrait;
}
