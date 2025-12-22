<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\XML\IdentifierTrait;
use SimpleSAML\SAML2\XML\saml\IdentifierInterface;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\NameIDPolicy;
use SimpleSAML\XMLSchema\Type\IDValue;

/**
 * Class representing the samlp:NameIDMappingRequestType.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractNameIDMappingRequest extends AbstractRequest
{
    use IdentifierTrait;


    /**
     * Initialize a NameIDMappingRequest.
     *
     * @param \SimpleSAML\SAML2\XML\saml\IdentifierInterface $identifier
     * @param \SimpleSAML\SAML2\XML\samlp\NameIDPolicy $nameIdPolicy
     * @param \SimpleSAML\XMLSchema\Type\IDValue $id
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null $issueInstant
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $destination
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     */
    final public function __construct(
        IdentifierInterface $identifier,
        protected NameIDPolicy $nameIdPolicy,
        IDValue $id,
        ?Issuer $issuer = null,
        ?SAMLDateTimeValue $issueInstant = null,
        ?SAMLAnyURIValue $destination = null,
        ?SAMLAnyURIValue $consent = null,
        ?Extensions $extensions = null,
    ) {
        $this->setIdentifier($identifier);

        parent::__construct($id, $issuer, $issueInstant, $destination, $consent, $extensions);
    }


    /**
     * Retrieve the NameIDPolicy of this element.
     *
     * @return \SimpleSAML\SAML2\XML\samlp\NameIDPolicy
     */
    public function getNameIDPolicy(): NameIDPolicy
    {
        return $this->nameIdPolicy;
    }


    /**
     * Convert this NameIDMappingRequest to XML
     */
    public function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);

        $this->getIdentifier()->toXML($e);
        $this->getNameIDPolicy()->toXML($e);

        return $e;
    }
}
