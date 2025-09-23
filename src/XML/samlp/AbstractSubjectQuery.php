<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\XMLSchema\Type\IDValue;

/**
 * Base class for SAML 2 subject query messages.
 *
 * This base class can be used for various requests which ask for
 * information about a particular subject.
 *
 * Note that this class currently only handles the simple case - where the
 * subject doesn't contain any sort of subject confirmation requirements.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractSubjectQuery extends AbstractRequest
{
    /**
     * Constructor for SAML 2 response messages.
     *
     * @param \SimpleSAML\XMLSchema\Type\IDValue $id
     * @param \SimpleSAML\SAML2\XML\saml\Subject $subject
     * @param \SimpleSAML\SAML2\XML\saml\Issuer $issuer
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue $issueInstant
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $destination
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     */
    protected function __construct(
        IDValue $id,
        protected Subject $subject,
        ?Issuer $issuer = null,
        ?SAMLDateTimeValue $issueInstant = null,
        ?SAMLAnyURIValue $destination = null,
        ?SAMLAnyURIValue $consent = null,
        ?Extensions $extensions = null,
    ) {
        parent::__construct($id, $issuer, $issueInstant, $destination, $consent, $extensions);
    }


    /**
     * Collect the value of the subject
     *
     * @return \SimpleSAML\SAML2\XML\saml\Subject
     */
    public function getSubject(): Subject
    {
        return $this->subject;
    }


    /**
     * Convert this message to an unsigned XML document.
     * This method does not sign the resulting XML document.
     *
     * @return \DOMElement The root element of the DOM tree
     */
    protected function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        Assert::notEmpty($this->subject, 'Cannot convert SubjectQuery to XML without a Subject set.');

        $parent = parent::toUnsignedXML($parent);

        $this->getSubject()->toXML($parent);

        return $parent;
    }
}
