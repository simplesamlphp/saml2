<?php

namespace SAML2;

/**
 * Base class for BaseID element.
 */
abstract class BaseID
{
    /**
     * The security or administrative domain that qualifies the identifier.
     * This attribute provides a means to federate identifiers from disparate user stores without collision.
     *
     * @var string|null
     */
    private $nameQualifier;

    /**
     * Further qualifies an identifier with the name of a service provider or affiliation of providers.
     * This attribute provides an additional means to federate identifiers on the basis of the relying party or parties.
     *
     * @var string|null
     */
    private $spNameQualifier;

    /**
     * Represent an entity by a string-valued.
     *
     * @var string
     */
    private $entity;

    /**
     * Constructor for SAML 2 BaseID.
     *
     * @param string $entity The entity name
     */
    protected function __construct($entity)
    {
        $this->setEntity($entity);
    }

    /**
     * Retrieve the entity name.
     *
     * @return string The entity name
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set the the entity name.
     *
     * @param string $entity The entity name
     */
    public function setEntity($entity)
    {
        assert('is_string($entity)');

        $this->entity = $entity;
    }

    /**
     * Retrieve the name qualifier.
     *
     * @return string The name qualifier
     */
    public function getNameQualifier()
    {
        return $this->nameQualifier;
    }

    /**
     * Set the name qualifier.
     *
     * @param string $namequalifier The name qualifier
     */
    public function setNameQualifier($namequalifier)
    {
        assert('is_string($namequalifier) || is_null($namequalifier)');

        $this->nameQualifier = $namequalifier;
    }

    /**
     * Retrieve the service provider name qualifier·.
     *
     * @return string The service provider name qualifier
     */
    public function getSPNameQualifier()
    {
        return $this->spNameQualifier;
    }

    /**
     * Set the service provider name qualifier.
     *
     * @param string $spnamequalifier The service provider name qualifier
     */
    public function setSPNameQualifier($spnamequalifier)
    {
        assert('is_string($spnamequalifier) || is_null($spnamequalifier)');

        $this->spNameQualifier = $spnamequalifier;
    }

    public function __toString()
    {
        return $this->entity;
    }
}
