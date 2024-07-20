<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Base64ElementTrait;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

/**
 * Class for SAML artifacts.
 *
 * @package simplesamlphp/saml2
 */
final class Artifact extends AbstractSamlpElement
{
    use Base64ElementTrait;

    /**
     * Initialize an artifact.
     *
     * @param string $content
     */
    public function __construct(
        string $content,
    ) {
        $this->setContent($content);
    }


    /**
     * Convert XML into an Artifact
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   If the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Artifact', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Artifact::NS, InvalidDOMElementException::class);

        return new static($xml->textContent);
    }
}
