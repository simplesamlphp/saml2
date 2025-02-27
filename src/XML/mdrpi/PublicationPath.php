<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdrpi;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};

/**
 * Class for handling the mdrpi:PublicationPath element.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/saml-metadata-rpi/v1.0/saml-metadata-rpi-v1.0.pdf
 * @package simplesamlphp/saml2
 */
final class PublicationPath extends AbstractMdrpiElement implements
    ArrayizableElementInterface,
    SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Create/parse a mdrpi:PublicationPath element.
     *
     * @param \SimpleSAML\SAML2\XML\mdrpi\Publication[] $publication
     */
    public function __construct(
        protected array $publication = [],
    ) {
        Assert::maxCount($publication, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($publication, Publication::class);
    }


    /**
     * Collect the value of the Publication-property
     *
     * @return \SimpleSAML\SAML2\XML\mdrpi\Publication[]
     */
    public function getPublication(): array
    {
        return $this->publication;
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        return empty($this->publication);
    }


    /**
     * Convert XML into a PublicationPath
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'PublicationPath', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, PublicationPath::NS, InvalidDOMElementException::class);

        $Publication = Publication::getChildrenOfClass($xml);

        return new static($Publication);
    }


    /**
     * Convert this element to XML.
     *
     * @param \DOMElement|null $parent The element we should append to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->getPublication() as $pub) {
            $pub->toXML($e);
        }

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        Assert::allIsArray($data, ArrayValidationException::class);

        $publication = [];
        foreach ($data as $p) {
            $publication[] = Publication::fromArray($p);
        }

        return new static($publication);
    }


    /**
     * Create an array from this class
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [];
        foreach ($this->getPublication() as $p) {
            $data[] = $p->toArray();
        }

        return $data;
    }
}
