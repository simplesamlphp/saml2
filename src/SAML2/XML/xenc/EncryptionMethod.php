<?php

declare(strict_types=1);

namespace SAML2\XML\xenc;

use DOMElement;
use SAML2\Constants;
use SAML2\XML\Chunk;
use Webmozart\Assert\Assert;

/**
 * A class implementing the xenc:EncryptionMethod element.
 *
 * @package simplesamlphp/saml2
 */
class EncryptionMethod extends AbstractXencElement
{
    /** @var string */
    protected $algorithm;

    /** @var int|null */
    protected $keySize = null;

    /** @var string|null */
    protected $oaepParams = null;

    /** @var \SAML2\XML\Chunk[] */
    protected $children = [];


    /**
     * EncryptionMethod constructor.
     *
     * @param string $algorithm
     * @param int|null $keySize
     * @param string|null $oaepParams
     * @param \SAML2\XML\Chunk[] $children
     */
    public function __construct(
        string $algorithm,
        ?int $keySize = null,
        ?string $oaepParams = null,
        array $children = []
    ) {
        $this->setAlgorithm($algorithm);
        $this->setKeySize($keySize);
        $this->setOAEPParams($oaepParams);
        $this->setChildren($children);
    }


    /**
     * Initialize an EncryptionMethod object from an existing XML.
     *
     * @param \DOMElement $xml
     * @return \SAML2\XML\md\EncryptionMethod
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'EncryptionMethod');
        Assert::same($xml->namespaceURI, static::NS);

        /** @var string $algorithm */
        $algorithm = self::getAttribute($xml, 'Algorithm');

        $keySize = null;
        $oaepParams = null;
        $children = [];
        foreach ($xml->childNodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            } elseif ($node->namespaceURI === Constants::NS_XENC) {
                if ($node->localName === 'KeySize') {
                    Assert::null($keySize, $node->tagName . ' cannot be set more than once.');
                    Assert::numeric($node->textContent, $node->tagName . ' must be numerical.');
                    $keySize = intval($node->textContent);
                    continue;
                }

                if ($node->localName === 'OAEPParams') {
                    Assert::null($oaepParams, $node->tagName . ' cannot be set more than once.');
                    $oaepParams = trim($node->textContent);
                    continue;
                }
            }

            $children[] = Chunk::fromXML($node);
        }

        return new static($algorithm, $keySize, $oaepParams, $children);
    }


    /**
     * Get the URI identifying the algorithm used by this encryption method.
     *
     * @return string
     */
    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }


    /**
     * Set the URI identifying the algorithm used by this encryption method.
     *
     * @param string $algorithm
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function setAlgorithm(string $algorithm): void
    {
        Assert::notEmpty($algorithm, 'Cannot set an empty algorithm in ' . static::NS_PREFIX . ':EncryptionMethod.');
        $this->algorithm = $algorithm;
    }


    /**
     * Get the size of the key used by this encryption method.
     *
     * @return int|null
     */
    public function getKeySize(): ?int
    {
        return $this->keySize;
    }


    /**
     * Set the size of the key used by this encryption method.
     *
     * @param int|null $keySize
     * @return void
     */
    protected function setKeySize(?int $keySize): void
    {
        $this->keySize = $keySize;
    }


    /**
     * Get the base64-encoded OAEP parameters.
     *
     * @return string
     */
    public function getOAEPParams(): ?string
    {
        return $this->oaepParams;
    }


    /**
     * Set the OAEP parameters.
     *
     * @param string|null $oaepParams The OAEP parameters, base64-encoded.
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function setOAEPParams(?string $oaepParams): void
    {
        if ($oaepParams === null) {
            return;
        }
        Assert::Eq(
            $oaepParams,
            base64_encode(base64_decode($oaepParams, true)),
            'OAEPParams must be base64-encoded.'
        );
        $this->oaepParams = $oaepParams;
    }


    /**
     * Get the children elements of this encryption method as chunks.
     *
     * @return \SAML2\XML\Chunk[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }


    /**
     * Set an array of chunks as children of this encryption method.
     *
     * @param \SAML2\XML\Chunk[] $children
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function setChildren(array $children): void
    {
        Assert::allIsInstanceOf(
            $children,
            Chunk::class,
            'All children elements of ' . static::NS_PREFIX . ':EncryptionMethod must be of type \SAML2\XML\Chunk.'
        );
        $this->children = $children;
    }


    /**
     * Convert this EncryptionMethod object to XML.
     *
     * @param \DOMElement|null $parent The element we should append this EncryptionMethod to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('Algorithm', $this->algorithm);

        if ($this->keySize !== null) {
            $keySize = $e->ownerDocument->createElementNS(Constants::NS_XENC, 'xenc:KeySize', strval($this->keySize));
            $e->appendChild($keySize);
        }

        if ($this->oaepParams !== null) {
            $oaepParams = $e->ownerDocument->createElementNS(Constants::NS_XENC, 'xenc:OAEPParams', $this->oaepParams);
            $e->appendChild($oaepParams);
        }

        foreach ($this->children as $child) {
            $child->toXML($e);
        }

        return $e;
    }
}
