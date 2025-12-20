<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\XML\saml\AbstractStatement;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XMLSchema\Constants as C_XSI;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Type\QNameValue;

/**
 * Example class to demonstrate how Statement can be extended.
 *
 * @package simplesamlphp\saml2
 */
final class CustomStatement extends AbstractStatement
{
    protected const string XSI_TYPE_NAME = 'CustomStatementType';

    protected const string XSI_TYPE_NAMESPACE = C::NAMESPACE;

    protected const string XSI_TYPE_PREFIX = 'ssp';


    /**
     * CustomStatement constructor.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Audience[] $audience
     */
    public function __construct(
        protected array $audience,
    ) {
        Assert::allIsInstanceOf($audience, Audience::class);

        parent::__construct(
            QNameValue::fromString(
                '{' . self::XSI_TYPE_NAMESPACE . '}' . self::XSI_TYPE_PREFIX . ':' . self::XSI_TYPE_NAME,
            ),
        );
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
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Statement', InvalidDOMElementException::class);
        Assert::notNull($xml->namespaceURI, InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AbstractStatement::NS, InvalidDOMElementException::class);
        Assert::true(
            $xml->hasAttributeNS(C_XSI::NS_XSI, 'type'),
            'Missing required xsi:type in <saml:Statement> element.',
            InvalidDOMElementException::class,
        );

        $type = $xml->getAttributeNS(C_XSI::NS_XSI, 'type');
        Assert::same($type, self::XSI_TYPE_PREFIX . ':' . self::XSI_TYPE_NAME);

        $audience = Audience::getChildrenOfClass($xml);

        return new static($audience);
    }


    /**
     * Convert this Statement to XML.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        foreach ($this->audience as $audience) {
            $audience->toXML($e);
        }

        return $e;
    }
}
