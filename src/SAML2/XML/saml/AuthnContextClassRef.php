<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML2 AuthnContextClassRef
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package SimpleSAMLphp
 */
class AuthnContextClassRef extends \SAML2\XML\AbstractConvertable
{
    /** @var string */
    private $classRef;


    /**
     * Initialize an AuthnContextClassRef.
     *
     * @param string $classRef
     */
    public function __construct(string $classRef)
    {
        $this->setClassRef($classRef);
    }


    /**
     * Collect the value of the classRef-property
     *
     * @return string
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function getClassRef(): string
    {
        Assert::stringNotEmpty($this->classRef);

        return $this->classRef;
    }


    /**
     * Set the value of the classRef-property
     *
     * @param string $name
     * @return void
     */
    public function setClassRef(string $classRef): void
    {
        $classRef = trim($classRef);
        Assert::stringNotEmpty($classRef);
        $this->classRef = $classRef;
    }


    /**
     * Convert XML into a AuthnContextClassRef
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
        return new self($xml->textContent);
    }


    /**
     * Convert this AuthContextClassRef to XML.
     *
     * @param \DOMElement|null $parent The element we should append this AuthnContextClassRef to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        Assert::stringNotEmpty($this->classRef, 'Cannot convert AuthnContextClassRef to XML without a ClassRef set');

        if ($parent === null) {
            $doc = DOMDocumentFactory::create();
            $e = $doc->createElementNS(Constants::NS_SAML, 'saml:AuthnContextClassRef');
            $doc->appendChild($e);
        } else {
            $e = $parent->ownerDocument->createElementNS(Constants::NS_SAML, 'saml:AuthnContextClassRef');
            $parent->appendChild($e);
        }

        $e->textContent = $this->classRef;

        return $e;
    }
}
