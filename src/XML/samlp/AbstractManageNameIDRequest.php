<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\XML\IdentifierTrait;
use SimpleSAML\SAML2\XML\saml\EncryptedID;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\samlp\NewEncryptedID;
use SimpleSAML\SAML2\XML\samlp\NewID;
use SimpleSAML\SAML2\XML\samlp\Terminate;
use SimpleSAML\XMLSchema\Type\IDValue;

/**
 * Class representing the samlp:ManageNameIDRequestType.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractManageNameIDRequest extends AbstractRequest
{
    use IdentifierTrait;


    /**
     * Initialize a ManageNameIDRequest.
     *
     * @param \SimpleSAML\SAML2\XML\saml\NameID|\SimpleSAML\SAML2\XML\saml\EncryptedID $identifier
     * @param (
     *   \SimpleSAML\SAML2\XML\samlp\NewID|
     *   \SimpleSAML\SAML2\XML\samlp\NewEncryptedID|
     *   \SimpleSAML\SAML2\XML\samlp\Terminate
     * ) $newIdentifier
     * @param \SimpleSAML\XMLSchema\Type\IDValue $id
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null $issueInstant
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $destination
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     */
    final public function __construct(
        NameID|EncryptedID $identifier,
        protected NewID|NewEncryptedID|Terminate $newIdentifier,
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
     * Retrieve the new identifier.
     *
     * @return (
     *   \SimpleSAML\SAML2\XML\samlp\NewID|
     *   \SimpleSAML\SAML2\XML\samlp\NewEncryptedID|
     *   \SimpleSAML\SAML2\XML\samlp\Terminate
     * )
     */
    public function getNewIdentifier(): NewID|NewEncryptedID|Terminate
    {
        return $this->newIdentifier;
    }


    /**
     * Convert this ManageNameIDRequest to XML
     */
    public function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);

        $this->getIdentifier()->toXML($e);
        $this->getNewIdentifier()->toXML($e);

        return $e;
    }
}
