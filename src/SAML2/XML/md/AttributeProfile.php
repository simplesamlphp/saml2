<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\XMLStringElementTrait;

/**
 * Class implementing AttributeProfile.
 *
 * @package simplesamlphp/saml2
 */
final class AttributeProfile extends AbstractMdElement
{
    use XMLStringElementTrait;


    /**
     * Validate the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     * @throws \Exception on failure
     * @return void
     */
    protected function validateContent(string $content): void
    {
        Assert::notEmpty($content, 'AttributeProfile cannot be empty');
    }


    /**
     * Convert XML into a AttributeProfile
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   If the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AttributeProfile', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AttributeProfile::NS, InvalidDOMElementException::class);

        return new self($xml->textContent);
    }
}
