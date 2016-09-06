<?php

namespace SAML2;

/**
 * Base class for NameIDType element.
 */
abstract class NameIDType extends BaseID
{
    /**
     * A URI reference representing the classification of string-based identifier information.
     *
     * @var string|null
     */
    private $format;

    /**
     * A A name identifier established by a service provider or affiliation of providers for the entity.
     *
     * @var string|null
     */
    private $spProvidedID;

    /**
     * Retrieve the name identifier established by a service provider or affiliation of providers for the entity.
     *
     * @return string name identifier established by a service provider or affiliation of providers for the entity
     */
    public function getSPProvidedID()
    {
        return $this->spProvidedID;
    }

    /**
     * Set the name identifier established by a service provider or affiliation of providers for the entity.
     *
     * @param string $spProvidedID name identifier established by a service provider or affiliation of providers for the entity
     */
    public function setSPProvidedID($spProvidedID)
    {
        assert('is_string($spProvidedID) || is_null($spProvidedID)');

        $this->spProvidedID = $spProvidedID;
    }

    /**
     * Retrieve the format.
     *
     * @return string format
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set the format.
     *
     * @param string $format format for the entity
     */
    public function setFormat($format)
    {
        assert('is_string($format) || is_null($format)');

        $this->format = $format;
    }
}
