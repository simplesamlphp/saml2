<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdattr;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeStatement;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

use function array_filter;
use function array_merge;
use function sprintf;

/**
 * Class for handling the EntityAttributes metadata extension.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-metadata-attr-cs-01.pdf
 * @package simplesamlphp/saml2
 */
final class EntityAttributes extends AbstractMdattrElement
{
    /**
     * Array with child elements.
     *
     * The elements can be \SimpleSAML\SAML2\XML\saml\Attribute or \SimpleSAML\SAML2\XML\saml\Assertion elements.
     *
     * @var (\SimpleSAML\SAML2\XML\saml\Assertion|\SimpleSAML\SAML2\XML\saml\Attribute)[]
     */
    protected array $children = [];


    /**
     * Create a EntityAttributes element.
     *
     * @param (\SimpleSAML\SAML2\XML\saml\Assertion|\SimpleSAML\SAML2\XML\saml\Attribute)[] $children
     */
    public function __construct(array $children)
    {
        $this->setChildren($children);
    }


    /**
     * Collect the value of the children-property
     *
     * @return (\SimpleSAML\SAML2\XML\saml\Assertion|\SimpleSAML\SAML2\XML\saml\Attribute)[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }


    /**
     * Set the value of the childen-property
     *
     * @param (\SimpleSAML\SAML2\XML\saml\Assertion|\SimpleSAML\SAML2\XML\saml\Attribute)[] $children
     * @return void
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    private function setChildren(array $children): void
    {
        Assert::allIsInstanceOfAny($children, [Assertion::class, Attribute::class]);

        $assertions = array_filter($children, function ($child) {
            return $child instanceof Assertion;
        });

        foreach ($assertions as $assertion) {
            $statements = array_merge(
                $assertion->getAttributeStatements(),
                $assertion->getAuthnStatements(),
                $assertion->getStatements()
            );

            Assert::allIsInstanceOf(
                $statements,
                AttributeStatement::class,
                '<saml:Asssertion> elements in an <mdattr:EntityAttributes> may only contain AttributeStatements',
                ProtocolViolationException::class
            );
            Assert::count(
                $statements,
                1,
                'One (and only one) <saml:AttributeStatement> MUST be included '
                . 'in a <saml:Assertion> inside a <mdattr:EntityAttribute>',
                ProtocolViolationException::class
            );
            Assert::notNull(
                Assertion::fromXML($assertion->toXML())->getSignature(),
                'Every <saml:Assertion> inside a <mdattr:EntityAttributes> must be individually signed',
                ProtocolViolationException::class
            );

            $subject = $assertion->getSubject();
            Assert::notNull(
                $subject,
                'Every <saml:Assertion> inside a <mdattr:EntityAttributes> must contain a Subject',
                ProtocolViolationException::class
            );

            Assert::isEmpty(
                $subject?->getSubjectConfirmation(),
                'Every <saml:Assertion> inside a <mdattr:EntityAttributes> must NOT contain any SubjectConfirmation',
                ProtocolViolationException::class
            );

            $nameId = $subject?->getIdentifier();
            Assert::isInstanceOf(
                $nameId,
                NameID::class,
                'Every <saml:Assertion> inside a <mdattr:EntityAttributes> must contain a NameID',
                ProtocolViolationException::class
            );
            Assert::same(
                $nameId?->getFormat(),
                C::NAMEID_ENTITY,
                sprintf('The NameID format must be %s', C::NAMEID_ENTITY),
                ProtocolViolationException::class
            );
        }

        $this->children = $children;
    }


    /**
     * Add the value to the children-property
     *
     * @param \SimpleSAML\SAML2\XML\saml\Assertion|\SimpleSAML\SAML2\XML\saml\Attribute $child
     * @return void
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    public function addChild($child): void
    {
        $this->setChildren(array_merge($this->children, [$child]));
    }


    /**
     * Convert XML into a EntityAttributes
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'EntityAttributes', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, EntityAttributes::NS, InvalidDOMElementException::class);

        $children = [];
        $xpCache = XPath::getXPath($xml);

        /** @var \DOMElement $node */
        foreach (XPath::xpQuery($xml, './saml_assertion:Attribute|./saml_assertion:Assertion', $xpCache) as $node) {
            if ($node->localName === 'Attribute') {
                $children[] = Attribute::fromXML($node);
            } elseif ($node->localName === 'Assertion') {
                $children[] = Assertion::fromXML($node);
            } else {
                throw new ProtocolViolationException('Illegal content in mdattr:EntityAttributes message.');
            }
        }

        return new static($children);
    }


    /**
     * Convert this EntityAttributes to XML.
     *
     * @param \DOMElement|null $parent The element we should append to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->getChildren() as $child) {
            $child->toXML($e);
        }

        return $e;
    }
}
