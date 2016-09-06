<?php

namespace SAML2;

/**
 * Base class for NameID element.
 */
class Issuer extends NameIDType
{
    public function __construct($entity)
    {
        parent::__construct($entity);
    }
}
