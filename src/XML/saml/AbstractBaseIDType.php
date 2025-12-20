<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\SAMLStringValue;

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
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $NameQualifier
     *   The security or administrative domain that qualifies the identifier.
     *   This attribute provides a means to federate identifiers from disparate user stores without collision.
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $SPNameQualifier
     *   Further qualifies an identifier with the name of a service provider or affiliation of providers. This
     *   attribute provides an additional means to federate identifiers on the basis of the relying party or parties.
     */
    protected function __construct(
        protected ?SAMLStringValue $NameQualifier = null,
        protected ?SAMLStringValue $SPNameQualifier = null,
    ) {
        Assert::nullOrNotWhitespaceOnly($NameQualifier);
        Assert::nullOrNotWhitespaceOnly($SPNameQualifier);
    }


    /**
     * Convert this BaseID to XML.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->getNameQualifier() !== null) {
            $e->setAttribute('NameQualifier', $this->getNameQualifier()->getValue());
        }

        if ($this->getSPNameQualifier() !== null) {
            $e->setAttribute('SPNameQualifier', $this->getSPNameQualifier()->getValue());
        }

        return $e;
    }
}
