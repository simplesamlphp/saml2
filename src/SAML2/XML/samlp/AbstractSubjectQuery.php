<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\XML\saml\Issuer;
use SAML2\XML\saml\Subject;
use Webmozart\Assert\Assert;

/**
 * Base class for SAML 2 subject query messages.
 *
 * This base class can be used for various requests which ask for
 * information about a particular subject.
 *
 * Note that this class currently only handles the simple case - where the
 * subject doesn't contain any sort of subject confirmation requirements.
 *
 * @package SimpleSAMLphp
 */
abstract class AbstractSubjectQuery extends AbstractRequest
{
    /** @var \SAML2\XML\saml\Subject */
    protected $subject;



    /**
     * Constructor for SAML 2 response messages.
     *
     * @param \SAML2\XML\saml\Subject $subject
     * @param \SAML2\XML\saml\Issuer $issuer
     * @param string $id
     * @param string $version
     * @param int $issueInstant
     * @param string|null $destination
     * @param string|null $consent
     * @param \SAML2\XML\samlp\Extensions $extensions
     */
    protected function __construct(
        Subject $subject,
        ?Issuer $issuer = null,
        ?string $id = null,
        ?string $version = '2.0',
        ?int $issueInstant = null,
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null
    ) {
        parent::__construct($issuer, $id, $version, $issueInstant, $destination, $consent, $extensions);

        $this->setSubject($subject);
    }


    /**
     * Collect the value of the subject
     *
     * @return \SAML2\XML\saml\Subject
     */
    public function getSubject(): Subject
    {
        return $this->subject;
    }


    /**
     * Set the value of the subject-property
     * @param \SAML2\XML\saml\Subject $subject
     *
     * @return void
     */
    private function setSubject(Subject $subject): void
    {
        $this->subject = $subject;
    }


    /**
     * Convert subject query message to an XML element.
     *
     * @return \DOMElement This subject query.
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        Assert::null($parent);
        Assert::notEmpty($this->subject, 'Cannot convert SubjectQuery to XML without a Subject set.');

        $parent = parent::toXML();

        $this->subject->toXML($parent);

        return $parent;
    }
}
