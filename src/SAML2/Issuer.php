<?php

namespace SAML2;

/**
 * Base class for NameID element.
 *
 * @package SimpleSAMLphp
 */
class Issuer extends NameIDType {

    public function __construct($entity) {
        parent::__construct($entity);
    }

}
