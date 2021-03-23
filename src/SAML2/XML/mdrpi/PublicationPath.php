<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdrpi;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class for handling the mdrpi:PublicationPath element.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/saml-metadata-rpi/v1.0/saml-metadata-rpi-v1.0.pdf
 * @package simplesamlphp/saml2
 */
final class PublicationPath extends AbstractMdrpiElement
{
    /**
     * This is array of Publication objects.
     *
     * @var \SimpleSAML\SAML2\XML\mdrpi\Publication[]
     */
    protected array $Publication = [];


    /**
     * Create/parse a mdrpi:PublicationPath element.
     *
     * @param \SimpleSAML\SAML2\XML\mdrpi\Publication[] $Publication
     */
    public function __construct(
        array $Publication = []
    ) {
        $this->setPublication($Publication);
    }


    /**
     * Collect the value of the Publication-property
     *
     * @return \SimpleSAML\SAML2\XML\mdrpi\Publication[]
     */
    public function getPublication(): array
    {
        return $this->Publication;
    }


    /**
     * Set the value of the Publication-property
     *
     * @param \SimpleSAML\SAML2\XML\mdrpi\Publication[] $Publication
     */
    private function setPublication(array $Publication): void
    {
        Assert::allIsInstanceOf($Publication, Publication::class);

        $this->Publication = $Publication;
    }


   /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        return empty($this->Publication);
    }


    /**
     * Convert XML into a PublicationPath
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'PublicationPath', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, PublicationPath::NS, InvalidDOMElementException::class);

        $Publication = Publication::getChildrenOfClass($xml);

        return new self($Publication);
    }


    /**
     * Convert this element to XML.
     *
     * @param \DOMElement|null $parent The element we should append to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->Publication as $pub) {
            $pub->toXML($e);
        }

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): object
    {
        $publication = [];
        foreach ($data as $p) {
            $publication[] = Publication::fromArray($p);
        }

        return new self($publication);
    }


    /**
     * Create an array from this class
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [];
        foreach ($this->Publication as $p) {
            $data[] = $p->toArray();
        }

        return $data;
    }
}
