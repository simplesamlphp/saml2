<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\AbstractStatement;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

/**
 * Example class to demonstrate how Statement can be extended.
 *
 * @package simplesamlphp\saml2
 */
final class CustomStatement extends AbstractStatement
{
    /** @var string */
    protected const XSI_TYPE_NAME = 'CustomStatementType';

    /** @var string */
    protected const XSI_TYPE_NAMESPACE = C::NAMESPACE;

    /** @var string */
    protected const XSI_TYPE_PREFIX = 'ssp';


    /**
     * CustomStatement constructor.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Audience[] $audience
     */
    public function __construct(
        protected array $audience,
    ) {
        Assert::allIsInstanceOf($audience, Audience::class);

        parent::__construct(self::XSI_TYPE_PREFIX . ':' . self::XSI_TYPE_NAME);
    }


    /**
     * Get the value of the audience-attribute.
     *
     * @return \SimpleSAML\SAML2\XML\saml\Audience[]
     */
    public function getAudience(): array
    {
        return $this->audience;
    }


    /**
     * Convert XML into an Statement
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\saml\Statement
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Statement', InvalidDOMElementException::class);
        Assert::notNull($xml->namespaceURI, InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AbstractStatement::NS, InvalidDOMElementException::class);
        Assert::true(
            $xml->hasAttributeNS(C::NS_XSI, 'type'),
            'Missing required xsi:type in <saml:Statement> element.',
            InvalidDOMElementException::class
        );

        $type = $xml->getAttributeNS(C::NS_XSI, 'type');
        Assert::same($type, self::XSI_TYPE_PREFIX . ':' . self::XSI_TYPE_NAME);

        $audience = Audience::getChildrenOfClass($xml);

        return new static($audience);
    }


    /**
     * Convert this Statement to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this BaseID.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        foreach ($this->audience as $audience) {
            $audience->toXML($e);
        }

        return $e;
    }
}
