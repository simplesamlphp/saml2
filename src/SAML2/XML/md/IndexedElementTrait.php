<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use Webmozart\Assert\Assert;

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
    protected $index;

    /**
     * Whether this endpoint is the default.
     *
     * @var bool|null
     */
    protected $isDefault = null;


    /**
     * Collect the value of the index property.
     *
     * @return int
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function getIndex(): int
    {
        Assert::notEmpty($this->index);

        return $this->index;
    }


    /**
     * Set the value of the index property.
     *
     * @param int $index
     */
    protected function setIndex(int $index): void
    {
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
        if ($flag === null) {
            return;
        }

        $this->isDefault = $flag;
    }
}
