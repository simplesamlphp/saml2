<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\Type\CIDRValue;
use SimpleSAML\SAML2\Type\DomainValue;
use SimpleSAML\SAML2\Type\GeolocationValue;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\ExtendableElementTrait;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XML\SerializableElementInterface;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\XML\Constants\NS;

use function array_filter;
use function array_key_exists;
use function array_keys;

/**
 * Class for handling the metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 *
 * @package simplesamlphp/saml2
 */
final class DiscoHints extends AbstractMduiElement implements
    ArrayizableElementInterface,
    SchemaValidatableElementInterface
{
    use ExtendableElementTrait;
    use SchemaValidatableElementTrait;


    /** The namespace-attribute for the xs:any element */
    public const string XS_ANY_ELT_NAMESPACE = NS::OTHER;


    /**
     * Create a DiscoHints element.
     *
     * @param \SimpleSAML\XML\SerializableElementInterface[] $children
     * @param \SimpleSAML\SAML2\XML\mdui\IPHint[] $ipHint
     * @param \SimpleSAML\SAML2\XML\mdui\DomainHint[] $domainHint
     * @param \SimpleSAML\SAML2\XML\mdui\GeolocationHint[] $geolocationHint
     */
    public function __construct(
        array $children = [],
        protected array $ipHint = [],
        protected array $domainHint = [],
        protected array $geolocationHint = [],
    ) {
        Assert::maxCount($ipHint, C::UNBOUNDED_LIMIT);
        Assert::maxCount($domainHint, C::UNBOUNDED_LIMIT);
        Assert::maxCount($geolocationHint, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($ipHint, IPHint::class);
        Assert::allIsInstanceOf($domainHint, DomainHint::class);
        Assert::allIsInstanceOf($geolocationHint, GeolocationHint::class);

        $this->setElements($children);
    }


    /**
     * Collect the value of the IPHint-property
     *
     * @return \SimpleSAML\SAML2\XML\mdui\IPHint[]
     */
    public function getIPHint(): array
    {
        return $this->ipHint;
    }


    /**
     * Collect the value of the DomainHint-property
     *
     * @return \SimpleSAML\SAML2\XML\mdui\DomainHint[]
     */
    public function getDomainHint(): array
    {
        return $this->domainHint;
    }


    /**
     * Collect the value of the GeolocationHint-property
     *
     * @return \SimpleSAML\SAML2\XML\mdui\GeolocationHint[]
     */
    public function getGeolocationHint(): array
    {
        return $this->geolocationHint;
    }


    /**
     * Add the value to the elements-property
     *
     * @param \SimpleSAML\XML\SerializableElementInterface $child
     */
    public function addChild(SerializableElementInterface $child): void
    {
        $this->elements[] = $child;
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     */
    public function isEmptyElement(): bool
    {
        return empty($this->getElements())
            && empty($this->getIPHint())
            && empty($this->getDomainHint())
            && empty($this->getGeolocationHint());
    }


    /**
     * Convert XML into a DiscoHints
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'DiscoHints', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, DiscoHints::NS, InvalidDOMElementException::class);

        $IPHint = IPHint::getChildrenOfClass($xml);
        $DomainHint = DomainHint::getChildrenOfClass($xml);
        $GeolocationHint = GeolocationHint::getChildrenOfClass($xml);
        $children = self::getChildElementsFromXML($xml);

        return new static($children, $IPHint, $DomainHint, $GeolocationHint);
    }


    /**
     * Convert this DiscoHints to XML.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->getIPHint() as $hint) {
            $hint->toXML($e);
        }

        foreach ($this->getDomainHint() as $hint) {
            $hint->toXML($e);
        }

        foreach ($this->getGeolocationHint() as $hint) {
            $hint->toXML($e);
        }

        /** @var \SimpleSAML\XML\SerializableElementInterface $child */
        foreach ($this->getElements() as $child) {
            $child->toXML($e);
        }

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array{
     *   'children'?: array,
     *   'IPHint'?: array,
     *   'DomainHint'?: array,
     *   'GeolocationHint'?: array,
     * } $data
     */
    public static function fromArray(array $data): static
    {
        $data = self::processArrayContents($data);

        return new static(
            $data['children'] ?? [],
            $data['IPHint'] ?? [],
            $data['DomainHint'] ?? [],
            $data['GeolocationHint'] ?? [],
        );
    }


    /**
     * Validates an array representation of this object and returns the same array with
     * rationalized keys (casing) and parsed sub-elements.
     *
     * @param array{
     *   'children'?: array,
     *   'IPHint'?: array,
     *   'DomainHint'?: array,
     *   'GeolocationHint'?: array,
     * } $data
     * @return array{
     *   'children'?: array,
     *   'IPHint'?: array,
     *   'DomainHint'?: array,
     *   'GeolocationHint'?: array,
     * }
     */
    private static function processArrayContents(array $data): array
    {
        $data = array_change_key_case($data, CASE_LOWER);

        // Make sure the array keys are known for this kind of object
        Assert::allOneOf(
            array_keys($data),
            [
                'iphint',
                'domainhint',
                'geolocationhint',
                'children',
            ],
            ArrayValidationException::class,
        );

        $retval = [];

        if (array_key_exists('iphint', $data)) {
            Assert::isArray($data['iphint'], ArrayValidationException::class);
            Assert::allString($data['iphint'], ArrayValidationException::class);
            foreach ($data['iphint'] as $hint) {
                $retval['IPHint'][] = new IPHint(
                    CIDRValue::fromString($hint),
                );
            }
        }

        if (array_key_exists('domainhint', $data)) {
            Assert::isArray($data['domainhint'], ArrayValidationException::class);
            Assert::allString($data['domainhint'], ArrayValidationException::class);
            foreach ($data['domainhint'] as $hint) {
                $retval['DomainHint'][] = new DomainHint(
                    DomainValue::fromString($hint),
                );
            }
        }

        if (array_key_exists('geolocationhint', $data)) {
            Assert::isArray($data['geolocationhint'], ArrayValidationException::class);
            Assert::allString($data['geolocationhint'], ArrayValidationException::class);
            foreach ($data['geolocationhint'] as $hint) {
                $retval['GeolocationHint'][] = new GeolocationHint(
                    GeolocationValue::fromString($hint),
                );
            }
        }

        if (array_key_exists('children', $data)) {
            Assert::isArray($data['children'], ArrayValidationException::class);
            Assert::allIsInstanceOf(
                $data['children'],
                SerializableElementInterface::class,
                ArrayValidationException::class,
            );
            $retval['children'] = $data['children'];
        }

        return $retval;
    }


    /**
     * Create an array from this class
     *
     * @return array{
     *   'children'?: array,
     *   'IPHint'?: array,
     *   'DomainHint'?: array,
     *   'GeolocationHint'?: array,
     * }
     */
    public function toArray(): array
    {
        $data = [
            'IPHint' => [],
            'DomainHint' => [],
            'GeolocationHint' => [],
            'children' => $this->getElements(),
        ];

        foreach ($this->getIPHint() as $hint) {
            $data['IPHint'][] = $hint->getContent();
        }

        foreach ($this->getDomainHint() as $hint) {
            $data['DomainHint'][] = $hint->getContent();
        }

        foreach ($this->getGeolocationHint() as $hint) {
            $data['GeolocationHint'][] = $hint->getContent();
        }

        return array_filter($data);
    }
}
