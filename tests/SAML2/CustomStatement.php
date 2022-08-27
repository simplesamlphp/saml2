<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DOMElement;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\Statement;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\Assert\Assert;

use function explode;

/**
 * @covers \SimpleSAML\Test\SAML2\CustomStatement
 * @package simplesamlphp\saml2
 */
final class CustomStatement extends Statement
{
    /** @var string */
    protected const NS_XSI_TYPE_NAME = 'CustomStatement';

    /** @var string */
    protected const NS_XSI_TYPE_NAMESPACE = C::NAMESPACE;

    /** @var string */
    protected const NS_XSI_TYPE_PREFIX = 'ssp';

    /** @var \SimpleSAML\SAML2\XML\saml\Audience[] $audience */
    protected array $audience;


    /**
     * CustomStatement constructor.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Audience[] $audience
     */
    public function __construct(array $audience)
    {
        $this->setAudience($audience);
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
     * Set the value of the audience-attribute
     *
     * @param \SimpleSAML\SAML2\XML\saml\Audience[] $audience
     */
    protected function setAudience(array $audience): void
    {
        Assert::allIsInstanceOf($audience, Audience::class);
        $this->audience = $audience;
    }


    /**
     * Convert XML into an Statement
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\saml\Statement
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Statement', InvalidDOMElementException::class);
        Assert::notNull($xml->namespaceURI, InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Statement::NS, InvalidDOMElementException::class);
        Assert::true(
            $xml->hasAttributeNS(C::NS_XSI, 'type'),
            'Missing required xsi:type in <saml:Statement> element.',
            InvalidDOMElementException::class
        );

        $type = $xml->getAttributeNS(C::NS_XSI, 'type');
        Assert::same($type, self::getXsiType());

        $audience = Audience::getChildrenOfClass($xml);

        return new self($audience);
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
