<?php

/**
 * Simple collection object for transporting keys
 */
class SAML2_Certificate_KeyCollection implements Countable, IteratorAggregate
{
    /**
     * @var array
     */
    private $keys = array();

    /**
     * Add a key to the collection
     *
     * @param SAML2_Certificate_Key $key
     */
    public function add(SAML2_Certificate_Key $key)
    {
        $this->keys[] = $key;
    }

    /**
     * @param $index
     *
     * @return null|SAML2_Certificate_Key
     */
    public function get($index)
    {
        if (!isset($this->keys[$index])) {
            return NULL;
        }

        return $this->keys[$index];
    }

    public function count()
    {
        return count($this->keys);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->keys);
    }
}
