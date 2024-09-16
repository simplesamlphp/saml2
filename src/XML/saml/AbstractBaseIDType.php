<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;

/**
 * SAML BaseID data type.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractBaseIDType extends AbstractSamlElement implements BaseIdentifierInterface
{
    use IDNameQualifiersTrait;


    /**
     * Initialize a saml:BaseIDAbstractType from scratch
     *
     * @param string|null $NameQualifier
     *   The security or administrative domain that qualifies the identifier.
     *   This attribute provides a means to federate identifiers from disparate user stores without collision.
     * @param string|null $SPNameQualifier
     *   Further qualifies an identifier with the name of a service provider or affiliation of providers. This
     *   attribute provides an additional means to federate identifiers on the basis of the relying party or parties.
     */
    protected function __construct(
        protected ?string $NameQualifier = null,
        protected ?string $SPNameQualifier = null,
    ) {
        Assert::nullOrNotWhitespaceOnly($NameQualifier);
        Assert::nullOrNotWhitespaceOnly($SPNameQualifier);
    }


    /**
     * Convert this BaseID to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this BaseID.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->getNameQualifier() !== null) {
            $e->setAttribute('NameQualifier', $this->getNameQualifier());
        }

        if ($this->getSPNameQualifier() !== null) {
            $e->setAttribute('SPNameQualifier', $this->getSPNameQualifier());
        }

        return $e;
    }
}
