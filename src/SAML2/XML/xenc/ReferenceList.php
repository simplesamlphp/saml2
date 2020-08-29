<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\xenc;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\InvalidDOMElementException;

/**
 * A class containing a list of references to either encrypted data or encryption keys.
 *
 * @package simplesamlphp/saml2
 */
class ReferenceList extends AbstractXencElement
{
    /** @var \SimpleSAML\SAML2\XML\xenc\DataReference[] */
    protected $dataReferences;

    /** @var \SimpleSAML\SAML2\XML\xenc\KeyReference[] */
    protected $keyreferences;


    /**
     * ReferenceList constructor.
     *
     * @param \SimpleSAML\SAML2\XML\xenc\DataReference[] $dataReferences
     * @param \SimpleSAML\SAML2\XML\xenc\KeyReference[] $keyreferences
     */
    public function __construct(array $dataReferences, array $keyreferences = [])
    {
        $this->setDataReferences($dataReferences);
        $this->setKeyReferences($keyreferences);
        Assert::minCount(
            array_merge($this->dataReferences, $this->keyreferences),
            1,
            'At least one <xenc:DataReference> or <xenc:KeyReference> element required in <xenc:ReferenceList>.'
        );
    }


    /**
     * Get the list of DataReference objects.
     *
     * @return \SimpleSAML\SAML2\XML\xenc\DataReference[]
     */
    public function getDataReferences(): array
    {
        return $this->dataReferences;
    }


    /**
     * @param \SimpleSAML\SAML2\XML\xenc\DataReference[] $dataReferences
     */
    protected function setDataReferences(array $dataReferences): void
    {
        Assert::allIsInstanceOf(
            $dataReferences,
            DataReference::class,
            'All data references must be an instance of <xenc:DataReference>.'
        );

        $this->dataReferences = $dataReferences;
    }


    /**
     * Get the list of KeyReference objects.
     *
     * @return \SimpleSAML\SAML2\XML\xenc\KeyReference[]
     */
    public function getKeyReferences(): array
    {
        return $this->keyreferences;
    }


    /**
     * @param \SimpleSAML\SAML2\XML\xenc\KeyReference[] $keyReferences
     */
    protected function setKeyReferences(array $keyReferences): void
    {
        Assert::allIsInstanceOf(
            $keyReferences,
            KeyReference::class,
            'All key references must be an instance of <xenc:KeyReference>.'
        );

        $this->keyreferences = $keyReferences;
    }

    /**
     * @inheritDoc
     *
     * @throws \SimpleSAML\SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'ReferenceList', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, ReferenceList::NS, InvalidDOMElementException::class);

        $dataReferences = DataReference::getChildrenOfClass($xml);
        $keyReferences = KeyReference::getChildrenOfClass($xml);

        return new self($dataReferences, $keyReferences);
    }


    /**
     * @inheritDoc
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->dataReferences as $dref) {
            $dref->toXML($e);
        }

        foreach ($this->keyreferences as $kref) {
            $kref->toXML($e);
        }

        return $e;
    }
}
