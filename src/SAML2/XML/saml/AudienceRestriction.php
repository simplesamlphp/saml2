<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use Webmozart\Assert\Assert;

/**
 * SAML AudienceRestriction data type.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class AudienceRestriction extends AbstractSamlElement
{
    /** @var \SAML2\XML\saml\Audience[] */
    protected $audience = [];


    /**
     * Initialize a saml:AudienceRestriction
     *
     * @param \SAML2\XML\saml\Audience[] $audience
     */
    public function __construct(array $audience)
    {
        $this->setAudience($audience);
    }


    /**
     * Collect the audience
     *
     * @return \SAML2\XML\saml\Audience[]
     */
    public function getAudience(): array
    {
        return $this->audience;
    }


    /**
     * Set the value of the Audience-property
     *
     * @param \SAML2\XML\saml\Audience[] $audience
     * @return void
     */
    private function setAudience(array $audience): void
    {
        $this->audience = $audience;
    }


    /**
     * Convert XML into an AudienceRestriction
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AudienceRestriction');
        Assert::same($xml->namespaceURI, AudienceRestriction::NS);

        $audience = Audience::getChildrenOfClass($xml);

        return new self($audience);
    }


    /**
     * Convert this Audience to XML.
     *
     * @param \DOMElement|null $element The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this AudienceRestriction.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->audience as $audience) {
            $audience->toXML($e);
        }

        return $e;
    }
}
