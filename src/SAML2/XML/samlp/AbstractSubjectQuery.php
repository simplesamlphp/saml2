<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use Exception;
use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\saml\NameID;
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
    /**
     * The NameId of the subject in the query.
     *
     * @var \SAML2\XML\saml\NameID
     */
    private $nameId;


    /**
     * Constructor for SAML 2 subject query messages.
     *
     * @param string $tagName The tag name of the root element.
     * @param \DOMElement|null $xml The input message.
     */
    protected function __construct(string $tagName, DOMElement $xml = null)
    {
        parent::__construct($tagName, $xml);

        if ($xml === null) {
            return;
        }

        $this->parseSubject($xml);
    }


    /**
     * Parse subject in query.
     *
     * @param \DOMElement $xml The SubjectQuery XML element.
     * @throws \Exception
     * @return void
     */
    private function parseSubject(DOMElement $xml): void
    {
        /** @var \DOMElement[] $subject */
        $subject = Utils::xpQuery($xml, './saml_assertion:Subject');
        if (empty($subject)) {
            throw new Exception('Missing subject in subject query.');
        } elseif (count($subject) > 1) {
            throw new Exception('More than one <saml:Subject> in subject query.');
        }

        /** @var \DOMElement[] $nameId */
        $nameId = Utils::xpQuery($subject[0], './saml_assertion:NameID');
        if (empty($nameId)) {
            throw new Exception('Missing <saml:NameID> in <saml:Subject>.');
        } elseif (count($nameId) > 1) {
            throw new Exception('More than one <saml:NameID> in <saml:Subject>.');
        }
        $this->nameId = NameID::fromXML($nameId[0]);
    }


    /**
     * Retrieve the NameId of the subject in the query.
     *
     * @return \SAML2\XML\saml\NameID The name identifier of the assertion.
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function getNameId(): NameID
    {
        Assert::notEmpty($this->nameId);

        return $this->nameId;
    }


    /**
     * Set the NameId of the subject in the query.
     *
     * @param \SAML2\XML\saml\NameID $nameId The name identifier of the assertion.
     * @return void
     */
    public function setNameId(NameID $nameId): void
    {
        $this->nameId = $nameId;
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
        Assert::notEmpty($this->nameId, 'Cannot convert SubjectQuery to XML without a NameID set.');

        $parent = parent::toXML();

        $subject = $parent->ownerDocument->createElementNS(Constants::NS_SAML, 'saml:Subject');
        $parent->appendChild($subject);

        $this->nameId->toXML($subject);

        return $parent;
    }
}
