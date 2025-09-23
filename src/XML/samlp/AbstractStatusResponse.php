<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\XMLSchema\Type\IDValue;
use SimpleSAML\XMLSchema\Type\NCNameValue;

/**
 * Base class for all SAML 2 response messages.
 *
 * Implements samlp:StatusResponseType. All of the elements in that type are
 * stored in the \SimpleSAML\SAML2\XML\AbstractMessage class, and this class is therefore more
 * or less empty. It is included mainly to make it easy to separate requests from
 * responses.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractStatusResponse extends AbstractMessage
{
    /**
     * Constructor for SAML 2 response messages.
     *
     * @param \SimpleSAML\XMLSchema\Type\IDValue $id
     * @param \SimpleSAML\SAML2\XML\samlp\Status $status
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue $issueInstant
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param \SimpleSAML\XMLSchema\Type\NCNameValue|null $inResponseTo
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $destination
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions|null $extensions
     *
     * @throws \Exception
     */
    protected function __construct(
        IDValue $id,
        protected Status $status,
        SAMLDateTimeValue $issueInstant,
        ?Issuer $issuer = null,
        protected ?NCNameValue $inResponseTo = null,
        ?SAMLAnyURIValue $destination = null,
        ?SAMLAnyURIValue $consent = null,
        ?Extensions $extensions = null,
    ) {
        parent::__construct($id, $issuer, $issueInstant, $destination, $consent, $extensions);
    }


    /**
     * Determine whether this is a successful response.
     *
     * @return bool true if the status code is success, false if not.
     */
    public function isSuccess(): bool
    {
        return strval($this->status->getStatusCode()->getValue()) === C::STATUS_SUCCESS;
    }


    /**
     * Retrieve the ID of the request this is a response to.
     *
     * @return \SimpleSAML\XMLSchema\Type\NCNameValue|null The ID of the request.
     */
    public function getInResponseTo(): ?NCNameValue
    {
        return $this->inResponseTo;
    }


    /**
     * Retrieve the status code.
     *
     * @return \SimpleSAML\SAML2\XML\samlp\Status The status code.
     */
    public function getStatus(): Status
    {
        return $this->status;
    }


    /**
     * Convert this message to an unsigned XML document.
     * This method does not sign the resulting XML document.
     *
     * @return \DOMElement The root element of the DOM tree
     */
    protected function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);

        if ($this->getInResponseTo() !== null) {
            $e->setAttribute('InResponseTo', $this->getInResponseTo()->getValue());
        }

        $this->getStatus()->toXML($e);

        return $e;
    }
}
