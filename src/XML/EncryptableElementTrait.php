<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\XMLSecurity\XML\EncryptableElementTrait as ParentEncryptableElementTrait;

/**
 * Trait aggregating functionality for elements that are encrypted.
 *
 * @package simplesamlphp/saml2
 */
trait EncryptableElementTrait
{
    use ParentEncryptableElementTrait;


    /**
     * @return array|null
     */
    public function getBlacklistedAlgorithms(): ?array
    {
        $container = ContainerSingleton::getInstance();
        return $container->getBlacklistedEncryptionAlgorithms();
    }
}
