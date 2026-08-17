<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\aslo;

use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XMLSchema\Type\BooleanValue;

/**
 * @package simplesamlphp/saml2
 */
trait SupportsAsynchronousTrait
{
    /** @var \SimpleSAML\XMLSchema\Type\BooleanValue|null */
    protected ?BooleanValue $supportsAsynchronous = null;


    /**
     * @return \SimpleSAML\XMLSchema\Type\BooleanValue|null
     */
    public function getSupportsAsynchronous(): ?BooleanValue
    {
        return $this->supportsAsynchronous;
    }


    /**
     * @param array<\SimpleSAML\XML\Attribute> $namespacedAttributes
     */
    private function setSupportsAsynchronous(array $namespacedAttributes = []): void
    {
        foreach ($namespacedAttributes as $attr) {
            if ($attr->getNamespaceURI() === C::NS_ASLO && $attr->getAttrName() === 'supportsAsynchronous') {
                $this->supportsAsynchronous = BooleanValue::fromString($attr->getAttrValue()->getValue());
                return;
            }
        }
    }
}
