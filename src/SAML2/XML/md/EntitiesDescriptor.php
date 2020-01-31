<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\SignedElementTrait;
use SAML2\Utils;
use SAML2\XML\Chunk;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML 2 EntitiesDescriptor element.
 *
 * @package SimpleSAMLphp
 */
final class EntitiesDescriptor extends AbstractMetadataDocument
{
    /**
     * The name of this entity collection.
     *
     * @var string|null
     */
    private $Name = null;

    /**
     * Child EntityDescriptor and EntitiesDescriptor elements.
     *
     * @var (\SAML2\XML\md\EntityDescriptor|\SAML2\XML\md\EntitiesDescriptor)[]
     */
    private $children = [];


    /**
     * Initialize an EntitiesDescriptor.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(DOMElement $xml = null)
    {
        parent::__construct($xml);

        if ($xml === null) {
            return;
        }

        if ($xml->hasAttribute('ID')) {
            $this->ID = $xml->getAttribute('ID');
        }
        if ($xml->hasAttribute('validUntil')) {
            $this->validUntil = Utils::xsDateTimeToTimestamp($xml->getAttribute('validUntil'));
        }
        if ($xml->hasAttribute('cacheDuration')) {
            $this->cacheDuration = $xml->getAttribute('cacheDuration');
        }
        if ($xml->hasAttribute('Name')) {
            $this->Name = $xml->getAttribute('Name');
        }

        $this->Extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($this->Extensions, 1, 'Only one md:Extensions element is allowed.');

        /** @var \DOMElement $node */
        foreach (Utils::xpQuery($xml, './saml_metadata:EntityDescriptor|./saml_metadata:EntitiesDescriptor') as $node) {
            if ($node->localName === 'EntityDescriptor') {
                $this->children[] = EntityDescriptor::fromXML($node);
            } else {
                $this->children[] = new EntitiesDescriptor($node);
            }
        }
    }


    /**
     * Collect the value of the Name property.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->Name;
    }


    /**
     * Set the value of the Name property.
     *
     * @param string|null $name
     * @return void
     */
    public function setName(string $name = null): void
    {
        $this->Name = $name;
    }


    /**
     * Collect the value of the children property.
     *
     * @return (\SAML2\XML\md\EntityDescriptor|\SAML2\XML\md\EntitiesDescriptor)[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }


    /**
     * Set the value of the childen property.
     *
     * @param array $children
     * @return void
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }


    /**
     * Add the value to the children property.
     *
     * @param \SAML2\XML\md\EntityDescriptor|\SAML2\XML\md\EntitiesDescriptor $child
     * @return void
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function addChild($child): void
    {
        Assert::isInstanceOfAny($child, [EntityDescriptor::class, EntitiesDescriptor::class]);
        $this->children[] = $child;
    }


    /**
     * Convert XML into a EntitiesDescriptor
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
        // @TODO: Actually fill this method with something useful;  this is a dummy!!
        return new self(new DOMElement('root'));
    }


    /**
     * Convert this EntitiesDescriptor to XML.
     *
     * @param \DOMElement|null $parent The EntitiesDescriptor we should append this EntitiesDescriptor to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        if ($parent === null) {
            $doc = DOMDocumentFactory::create();
            $e = $doc->createElementNS(Constants::NS_MD, 'md:EntitiesDescriptor');
            $doc->appendChild($e);
        } else {
            $e = $parent->ownerDocument->createElementNS(Constants::NS_MD, 'md:EntitiesDescriptor');
            $parent->appendChild($e);
        }

        if ($this->ID !== null) {
            $e->setAttribute('ID', $this->ID);
        }

        if ($this->validUntil !== null) {
            $e->setAttribute('validUntil', gmdate('Y-m-d\TH:i:s\Z', $this->validUntil));
        }

        if ($this->cacheDuration !== null) {
            $e->setAttribute('cacheDuration', $this->cacheDuration);
        }

        if ($this->Name !== null) {
            $e->setAttribute('Name', $this->Name);
        }

        if (!empty($this->Extensions)) {
            $this->Extensions->toXML($e);
        }

        foreach ($this->children as $node) {
            $node->toXML($e);
        }

        $this->signElement($e, $e->firstChild);

        return $e;
    }
}
