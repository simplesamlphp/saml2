<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\XMLURIElementTrait;

/**
 * Class representing a samlp:RequesterID element.
 *
 * @package simplesaml/saml2
 */
final class RequesterID extends AbstractSamlpElement
{
    use XMLURIElementTrait;


    /**
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->setContent($content);
    }


    /**
     * Convert XML into an RequesterID
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   If the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'RequesterID', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, RequesterID::NS, InvalidDOMElementException::class);

        return new self($xml->textContent);
    }
}

