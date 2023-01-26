<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\Assert\Assert;

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
     * @param \SimpleSAML\SAML2\XML\saml\Subject $subject
     * @param \SimpleSAML\SAML2\XML\saml\Issuer $issuer
     * @param string|null $id
     * @param string $version
     * @param int $issueInstant
     * @param string|null $destination
     * @param string|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     */
    protected function __construct(
        protected Subject $subject,
        ?Issuer $issuer = null,
        ?string $id = null,
        string $version = '2.0',
        ?int $issueInstant = null,
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null,
    ) {
        parent::__construct($issuer, $id, $version, $issueInstant, $destination, $consent, $extensions);
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
