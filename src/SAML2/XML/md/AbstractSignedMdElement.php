<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\XMLSecurity\XML\SignableElementTrait;
use SimpleSAML\XMLSecurity\XML\SignableElementInterface;
use SimpleSAML\XMLSecurity\XML\SignedElementTrait;
use SimpleSAML\XMLSecurity\XML\SignedElementInterface;

/**
 * Abstract class that represents a signed metadata element.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractSignedMdElement extends AbstractMdElement implements SignableElementInterface, SignedElementInterface
{
    use SignableElementTrait;
    use SignedElementTrait;


    /**
     * @return array|null
     */
    public function getBlacklistedAlgorithms(): ?array
    {
        $container = ContainerSingleton::getInstance();
        return $container->getBlacklistedEncryptionAlgorithms();
    }
}
