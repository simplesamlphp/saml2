<?php

interface SAML2_Utilities_Collection extends ArrayAccess, Countable, IteratorAggregate
{
    /**
     * Add an element to the collection
     *
     * @param $element
     *
     * @return $this|SAML2_Utilities_Collection
     */
    public function add($element);

    /**
     * @param callable $filterFunction
     *
     * @return SAML2_Utilities_Collection
     */
    public function filter(Closure $filterFunction);

    /**
     * Get the element at index
     *
     * @param mixed $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * @param $element
     */
    public function remove($element);

    /**
     * Set the value for index
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function set($key, $value);
}
