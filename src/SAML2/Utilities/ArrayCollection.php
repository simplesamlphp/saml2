<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Utilities;

use ArrayIterator;
use Closure;
use SimpleSAML\SAML2\Exception\RuntimeException;

/**
 * Simple Array implementation of Collection.
 */
class ArrayCollection implements Collection
{
    /**
     * @var array
     */
    protected array $elements;


    /**
     * ArrayCollection constructor.
     *
     * @param array $elements
     */
    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
    }


    /**
     * @param mixed $element
     *
     */
    public function add($element): void
    {
        $this->elements[] = $element;
    }


    /**
     * @param mixed $key
     *
     * @return mixed|null
     */
    public function get($key)
    {
        return isset($this->elements[$key]) ? $this->elements[$key] : null;
    }


    /**
     * @param \Closure $f
     *
     * @return ArrayCollection
     */
    public function filter(Closure $f): Collection
    {
        return new self(array_filter($this->elements, $f));
    }


    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function set($key, $value): void
    {
        $this->elements[$key] = $value;
    }


    /**
     * @param mixed $element
     *
     */
    public function remove($element): void
    {
        $key = array_search($element, $this->elements);
        if ($key === false) {
            return;
        }
        unset($this->elements[$key]);
    }


    /**
     * @throws RuntimeException
     * @return bool|mixed
     */
    public function getOnlyElement()
    {
        if ($this->count() !== 1) {
            throw new RuntimeException(sprintf(
                __METHOD__ . ' requires that the collection has exactly one element, '
                . '"%d" elements found',
                $this->count()
            ));
        }

        return reset($this->elements);
    }


    /**
     * @return bool|mixed
     */
    public function first()
    {
        return reset($this->elements);
    }


    /**
     * @return bool|mixed
     */
    public function last()
    {
        return end($this->elements);
    }


    /**
     * @param \Closure $function
     *
     * @return ArrayCollection
     */
    public function map(Closure $function): ArrayCollection
    {
        return new self(array_map($function, $this->elements));
    }


    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->elements);
    }


    /**
     * @return \ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->elements);
    }


    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->elements[$offset]);
    }


    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->elements[$offset];
    }


    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->elements[$offset] = $value;
    }


    /**
     * @param $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->elements[$offset]);
    }
}
