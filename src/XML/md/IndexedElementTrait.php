<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\XMLSchema\Type\{BooleanValue, UnsignedShortValue};

/**
 * Trait adding methods to handle elements that can be indexed.
 *
 * @package simplesamlphp/saml2
 */
trait IndexedElementTrait
{
    /**
     * The index for this endpoint.
     *
     * @var \SimpleSAML\XMLSchema\Type\UnsignedShortValue
     */
    protected UnsignedShortValue $index;

    /**
     * Whether this endpoint is the default.
     *
     * @var \SimpleSAML\XMLSchema\Type\BooleanValue|null
     */
    protected ?BooleanValue $isDefault = null;


    /**
     * Collect the value of the index property.
     *
     * @return \SimpleSAML\XMLSchema\Type\UnsignedShortValue
     */
    public function getIndex(): UnsignedShortValue
    {
        return $this->index;
    }


    /**
     * Set the value of the index property.
     *
     * @param \SimpleSAML\XMLSchema\Type\UnsignedShortValue $index
     */
    protected function setIndex(UnsignedShortValue $index): void
    {
        $this->index = $index;
    }


    /**
     * Collect the value of the isDefault property.
     *
     * @return \SimpleSAML\XMLSchema\Type\BooleanValue|null
     */
    public function getIsDefault(): ?BooleanValue
    {
        return $this->isDefault;
    }


    /**
     * Set the value of the isDefault property.
     *
     * @param  \SimpleSAML\XMLSchema\Type\BooleanValue|null $flag
     */
    protected function setIsDefault(?BooleanValue $flag): void
    {
        $this->isDefault = $flag;
    }
}
