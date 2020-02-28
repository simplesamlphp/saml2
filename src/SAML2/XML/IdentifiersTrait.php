<?php

declare(strict_types=1);

namespace SAML2\XML;

use SAML2\XML\saml\BaseID;
use SAML2\XML\saml\NameID;
use SAML2\XML\saml\EncryptedID;

/**
 * Trait grouping common functionality for elements that can hold identifiers.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
trait IdentifiersTrait
{
    /**
     * The BaseID this Subject.
     *
     * @var \SAML2\XML\saml\BaseID|null
     */
    protected $baseId = null;

    /**
     * The NameID this Subject.
     *
     * @var \SAML2\XML\saml\NameID|null
     */
    protected $nameId = null;

    /**
     * The EncryptedID this Subject.
     *
     * @var \SAML2\XML\saml\EncryptedID|null
     */
    protected $encryptedId = null;


    /**
     * Collect the value of the baseId-property
     *
     * @return \SAML2\XML\saml\BaseID|null
     */
    public function getBaseID(): ?BaseID
    {
        return $this->baseId;
    }


    /**
     * Set the value of the baseId-property
     *
     * @param \SAML2\XML\saml\BaseID|null
     * @return void
     */
    private function setBaseID(?BaseID $baseId): void
    {
        $this->baseId = $baseId;
    }


    /**
     * Collect the value of the nameId-property
     *
     * @return \SAML2\XML\saml\NameID|null
     */
    public function getNameID(): ?NameID
    {
        return $this->nameId;
    }


    /**
     * Set the value of the nameId-property
     *
     * @param \SAML2\XML\saml\NameID|null
     * @return void
     */
    private function setNameID(?NameID $nameId): void
    {
        $this->nameId = $nameId;
    }


    /**
     * Collect the value of the encryptedId-property
     *
     * @return \SAML2\XML\saml\EncryptedID|null
     */
    public function getEncryptedID(): ?EncryptedID
    {
        return $this->encryptedId;
    }


    /**
     * Set the value of the encryptedId-property
     *
     * @param \SAML2\XML\saml\EncryptedID|null
     * @return void
     */
    private function setEncryptedID(?EncryptedID $encryptedId): void
    {
        $this->encryptedId = $encryptedId;
    }
}
