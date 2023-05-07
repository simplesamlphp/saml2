<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ds;

use DOMElement;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\Chunk;
use SimpleSAML\Assert\Assert;

/**
 * Class representing a ds:KeyInfo element.
 *
 * @package SimpleSAMLphp
 */
class KeyInfo
{
    /**
     * The Id attribute on this element.
     *
     * @var string|null
     */
    private ?string $Id = null;

    /**
     * The various key information elements.
     *
     * Array with various elements describing this key.
     * Unknown elements will be represented by \SimpleSAML\XML\Chunk.
     *
     * @var (\SimpleSAML\XML\Chunk|\SimpleSAML\SAML2\XML\ds\KeyName|\SimpleSAML\SAML2\XML\ds\X509Data)[]
     */
    private array $info = [];


    /**
     * Initialize a KeyInfo element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if ($xml->hasAttribute('Id')) {
            $this->Id = $xml->getAttribute('Id');
        }

        foreach ($xml->childNodes as $n) {
            if (!($n instanceof DOMElement)) {
                continue;
            }

            if ($n->namespaceURI !== C::NS_XDSIG) {
                $this->info[] = new Chunk($n);
                continue;
            }
            switch ($n->localName) {
                case 'KeyName':
                    $this->info[] = new KeyName($n);
                    break;
                case 'X509Data':
                    $this->info[] = new X509Data($n);
                    break;
                default:
                    $this->info[] = new Chunk($n);
                    break;
            }
        }
    }


    /**
     * Collect the value of the Id-property
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->Id;
    }


    /**
     * Set the value of the Id-property
     *
     * @param string|null $id
     * @return void
     */
    public function setId(string $id = null): void
    {
        $this->Id = $id;
    }


    /**
     * Collect the value of the info-property
     *
     * @return array
     */
    public function getInfo(): array
    {
        return $this->info;
    }


    /**
     * Set the value of the info-property
     *
     * @param array $info
     * @return void
     */
    public function setInfo(array $info): void
    {
        $this->info = $info;
    }


    /**
     * Add the value to the info-property
     *
     * @param \SimpleSAML\XML\Chunk|\SimpleSAML\SAML2\XML\ds\KeyName|\SimpleSAML\SAML2\XML\ds\X509Data $info
     * @throws \Exception
     * @return void
     */
    public function addInfo($info): void
    {
        Assert::isInstanceOfAny(
            $info,
            [Chunk::class, KeyName::class, X509Data::class],
            'KeyInfo can only contain instances of KeyName, X509Data or Chunk.'
        );
        $this->info[] = $info;
    }


    /**
     * Convert this KeyInfo to XML.
     *
     * @param \DOMElement $parent The element we should append this KeyInfo to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(C::NS_XDSIG, 'ds:KeyInfo');
        $parent->appendChild($e);

        if ($this->Id !== null) {
            $e->setAttribute('Id', $this->Id);
        }

        foreach ($this->info as $n) {
            $n->toXML($e);
        }

        return $e;
    }
}
