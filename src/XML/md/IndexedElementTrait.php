<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\Assert\Assert;

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
     * @var int
     */
    protected int $index;

    /**
     * Whether this endpoint is the default.
     *
     * @var bool|null
     */
    protected ?bool $isDefault = null;


    /**
     * Collect the value of the index property.
     *
     * @return int
     */
    public function getIndex(): int
    {
        return $this->index;
    }


    /**
     * Set the value of the index property.
     *
     * @param int $index
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    protected function setIndex(int $index): void
    {
        Assert::range($index, 0, 65535);
        $this->index = $index;
    }


    /**
     * Collect the value of the isDefault property.
     *
     * @return bool|null
     */
    public function getIsDefault(): ?bool
    {
        return $this->isDefault;
    }


    /**
     * Set the value of the isDefault property.
     *
     * @param bool|null $flag
     */
    protected function setIsDefault(?bool $flag): void
    {
        $this->isDefault = $flag;
    }
}
