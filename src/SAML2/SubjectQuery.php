<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

use DOMElement;
use Exception;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;

use function count;

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
abstract class SubjectQuery extends Request
{
    /**
     * The NameId of the subject in the query.
     *
     * @var \SimpleSAML\SAML2\XML\saml\NameID|null
     */
    private ?NameID $nameId = null;


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
        $xpCache = XPath::getXPath($xml);

        /** @var \DOMElement[] $subject */
        $subject = XPath::xpQuery($xml, './saml_assertion:Subject', $xpCache);
        if (empty($subject)) {
            throw new MissingElementException('Missing subject in subject query.');
        } elseif (count($subject) > 1) {
            throw new TooManyElementsException('More than one <saml:Subject> in subject query.');
        }

        $xpCache = XPath::getXPath($subject[0]);
        /** @var \DOMElement[] $nameId */
        $nameId = XPath::xpQuery($subject[0], './saml_assertion:NameID', $xpCache);
        if (empty($nameId)) {
            throw new MissingElementException('Missing <saml:NameID> in <saml:Subject>.');
        } elseif (count($nameId) > 1) {
            throw new TooManyElementsException('More than one <saml:NameID> in <saml:Subject>.');
        }
        $this->nameId = NameID::fromXML($nameId[0]);
    }


    /**
     * Retrieve the NameId of the subject in the query.
     *
     * @return \SimpleSAML\SAML2\XML\saml\NameID|null The name identifier of the assertion.
     */
    public function getNameId(): ?NameID
    {
        return $this->nameId;
    }


    /**
     * Set the NameId of the subject in the query.
     *
     * @param \SimpleSAML\SAML2\XML\saml\NameID|null $nameId The name identifier of the assertion.
     * @return void
     */
    public function setNameId(NameID $nameId = null): void
    {
        $this->nameId = $nameId;
    }


    /**
     * Convert subject query message to an XML element.
     *
     * @return \DOMElement This subject query.
     */
    public function toUnsignedXML(): DOMElement
    {
        if ($this->nameId === null) {
            throw new MissingElementException('Cannot convert SubjectQuery to XML without a NameID set.');
        }
        $root = parent::toUnsignedXML();

        $subject = $root->ownerDocument->createElementNS(Constants::NS_SAML, 'saml:Subject');
        $root->appendChild($subject);

        $this->nameId->toXML($subject);

        return $root;
    }
}
