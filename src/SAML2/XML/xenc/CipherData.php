<?php

declare(strict_types=1);

namespace SAML2\XML\xenc;

use DOMElement;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Class representing <xenc:CipherData>.
 *
 * @package simplesamlphp/saml2
 */
class CipherData extends AbstractXencElement
{
    /** @var string */
    protected $cipherValue;


    /**
     * CipherData constructor.
     *
     * @param string $cipherValue
     */
    public function __construct(string $cipherValue)
    {
        $this->setCipherValue($cipherValue);
    }


    /**
     * Get the string value of the <xenc:CipherValue> element inside this CipherData object.
     *
     * @return string
     */
    public function getCipherValue(): string
    {
        return $this->cipherValue;
    }


    /**
     * @param string $cipherValue
     */
    protected function setCipherValue(string $cipherValue)
    {
        Assert::regex($cipherValue, '/[a-zA-Z0-9_\-=\+\/]/', 'Invalid data in <xenc:CipherValue>.');
        $this->cipherValue = $cipherValue;
    }


    /**
     * @inheritDoc
     * @throws \Exception
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'CipherData');
        Assert::same($xml->namespaceURI, CipherData::NS);

        $cv = Utils::xpQuery($xml, './xenc:CipherValue');
        Assert::notEmpty($cv, 'Missing CipherValue element in <xenc:CipherData>');
        Assert::count($cv, 1, 'More than one CipherValue element in <xenc:CipherData');

        return new self($cv[0]->textContent);
    }


    /**
     * @inheritDoc
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $cv = $e->ownerDocument->createElementNS($this::NS, $this::NS_PREFIX . ':CipherValue');
        $cv->textContent = $this->cipherValue;
        $e->appendChild($cv);

        return $e;
    }
}
