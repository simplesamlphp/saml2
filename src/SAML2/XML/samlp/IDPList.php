<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class for handling SAML2 IDPList.
 *
 * @package simplesamlphp/saml2
 */
final class IDPList extends AbstractSamlpElement
{
    /** @var \SimpleSAML\SAML2\XML\samlp\IDPEntry[] */
    protected array $IDPEntry;

    /** @var \SimpleSAML\SAML2\XML\samlp\GetComplete|null */
    protected ?GetComplete $getComplete;


    /**
     * Initialize an IDPList element.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\IDPEntry[] $idpEntry
     * @param \SimpleSAML\SAML2\XML\samlp\GetComplete|null $getComplete
     */
    public function __construct(array $idpEntry, ?GetComplete $getComplete = null)
    {
        $this->setIdpEntry($idpEntry);
        $this->setGetComplete($getComplete);
    }


    /**
     * @return \SimpleSAML\SAML2\XML\samlp\IDPEntry[]
     */
    public function getIdpEntry(): array
    {
        return $this->IDPEntry;
    }


    /**
     * @param \SimpleSAML\SAML2\XML\samlp\IDPEntry[] $idpEntry
     */
    private function setIdpEntry(array $idpEntry): void
    {
        Assert::minCount($idpEntry, 1, 'At least one samlp:IDPEntry must be specified.');
        Assert::allIsInstanceOf($idpEntry, IDPEntry::class);

        $this->IDPEntry = $idpEntry;
    }


    /**
     * @return \SimpleSAML\SAML2\XML\samlp\GetComplete|null
     */
    public function getGetComplete(): ?GetComplete
    {
        return $this->getComplete;
    }


    /**
     * @param \SimpleSAML\SAML2\XML\samlp\GetComplete|null $getComplete
     */
    private function setGetComplete(?GetComplete $getComplete): void
    {
        $this->getComplete = $getComplete;
    }


    /**
     * Convert XML into a IDPList-element
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\samlp\IDPList
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingElementException if one of the mandatory child-elements is missing
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'IDPList', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, IDPList::NS, InvalidDOMElementException::class);

        $idpEntry = IDPEntry::getChildrenOfClass($xml);
        Assert::minCount($idpEntry, 1, 'At least one <samlp:IDPEntry> must be specified.', MissingElementException::class);

        $getComplete = GetComplete::getChildrenOfClass($xml);
        Assert::maxCount($getComplete, 1, 'Only one <samlp:GetComplete> element is allowed.', TooManyElementsException::class);

        return new self(
            $idpEntry,
            empty($getComplete) ? null : array_pop($getComplete)
        );
    }


    /**
     * Convert this IDPList to XML.
     *
     * @param \DOMElement|null $parent The element we should append this IDPList to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->IDPEntry as $idpEntry) {
            $idpEntry->toXML($e);
        }

        if (!is_null($this->getComplete)) {
            $this->getComplete->toXML($e);
        }

        return $e;
    }
}
