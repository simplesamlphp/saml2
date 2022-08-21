<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\XMLURIElementTrait;

/**
 * Class representing a samlp:GetComplete element.
 *
 * @package simplesaml/saml2
 */
final class GetComplete extends AbstractSamlpElement
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
     * Convert XML into an GetComplete
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   If the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'GetComplete', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, GetComplete::NS, InvalidDOMElementException::class);

        return new self($xml->textContent);
    }
}

