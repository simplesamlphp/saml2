<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ds;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\xenc\EncryptedData;
use SimpleSAML\SAML2\XML\xenc\EncryptedKey;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Chunk;

/**
 * Class representing a ds:KeyInfo element.
 *
 * @package simplesamlphp/saml2
 */
final class KeyInfo extends AbstractDsElement
{
    /**
     * The Id attribute on this element.
     *
     * @var string|null
     */
    protected $Id = null;

    /**
     * The various key information elements.
     *
     * Array with various elements describing this key.
     * Unknown elements will be represented by \SAML2\XML\Chunk.
     *
     * @var (\SimpleSAML\XML\Chunk|\SimpleSAML\SAML2\XML\ds\KeyName|\SimpleSAML\SAML2\XML\ds\X509Data|\SimpleSAML\SAML2\XML\xenc\EncryptedKey)[]
     */
    protected $info = [];


    /**
     * Initialize a KeyInfo element.
     *
     * @param (\SimpleSAML\XML\Chunk|\SimpleSAML\SAML2\XML\ds\KeyName|\SimpleSAML\SAML2\XML\ds\X509Data|\SimpleSAML\SAML2\XML\xenc\EncryptedKey)[] $info
     * @param string|null $Id
     */
    public function __construct(array $info, $Id = null)
    {
        $this->setInfo($info);
        $this->setId($Id);
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
    private function setId(string $id = null): void
    {
        $this->Id = $id;
    }


    /**
     * Collect the value of the info-property
     *
     * @return (\SimpleSAML\XML\Chunk|\SimpleSAML\SAML2\XML\ds\KeyName|\SimpleSAML\SAML2\XML\ds\X509Data|\SimpleSAML\SAML2\XML\xenc\EncryptedKey)[]
     */
    public function getInfo(): array
    {
        return $this->info;
    }


    /**
     * Set the value of the info-property
     *
     * @param (\SimpleSAML\XML\Chunk|\SimpleSAML\SAML2\XML\ds\KeyName|\SimpleSAML\SAML2\XML\ds\X509Data|\SimpleSAML\SAML2\XML\xenc\EncryptedKey)[] $info
     * @return void
     * @throws \SimpleSAML\Assert\AssertionFailedException  if $info contains
     *   anything other than KeyName, X509Data, EncryptedKey or Chunk
     */
    private function setInfo(array $info): void
    {
        Assert::notEmpty($info, 'ds:KeyInfo cannot be empty');
        Assert::allIsInstanceOfAny(
            $info,
            [Chunk::class, KeyName::class, X509Data::class, EncryptedKey::class],
            'KeyInfo can only contain instances of KeyName, X509Data, EncryptedKey or Chunk.'
        );
        $this->info = $info;
    }


    /**
     * Convert XML into a KeyInfo
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'KeyInfo', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, KeyInfo::NS, InvalidDOMElementException::class);

        $Id = self::getAttribute($xml, 'Id', null);
        $info = [];

        foreach ($xml->childNodes as $n) {
            if (!($n instanceof DOMElement)) {
                continue;
            } elseif ($n->namespaceURI === self::NS) {
                switch ($n->localName) {
                    case 'KeyName':
                        $info[] = KeyName::fromXML($n);
                        break;
                    case 'X509Data':
                        $info[] = X509Data::fromXML($n);
                        break;
                    default:
                        $info[] = new Chunk($n);
                        break;
                }
            } elseif ($n->namespaceURI === Constants::NS_XENC) {
                switch ($n->localName) {
                    case 'EncryptedData':
                        $info[] = EncryptedData::fromXML($n);
                        break;
                    case 'EncryptedKey':
                        $info[] = EncryptedKey::fromXML($n);
                        break;
                    default:
                        $info[] = new Chunk($n);
                        break;
                }
            } else {
                $info[] = new Chunk($n);
                break;
            }
        }

        return new self($info, $Id);
    }

    /**
     * Convert this KeyInfo to XML.
     *
     * @param \DOMElement|null $parent The element we should append this KeyInfo to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->Id !== null) {
            $e->setAttribute('Id', $this->Id);
        }

        foreach ($this->info as $n) {
            $n->toXML($e);
        }

        return $e;
    }
}
