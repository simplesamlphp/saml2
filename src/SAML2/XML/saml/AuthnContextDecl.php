<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use SAML2\XML\Chunk;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML2 AuthnContextDecl
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package SimpleSAMLphp
 */
class AuthnContextDecl extends \SAML2\XML\AbstractConvertable
{
    /** @var \SAML2\XML\Chunk */
    private $decl;


    /**
     * Initialize an AuthnContextDecl.
     *
     * @param \SAML2\XML\Chunk $decl
     */
    public function __construct(Chunk $decl)
    {
        $this->setDecl($decl);
    }


    /**
     * Collect the value of the decl-property
     *
     * @return \SAML2\XML\Chunk
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function getDecl(): Chunk
    {
        return $this->decl;
    }


    /**
     * Set the value of the decl-property
     *
     * @param \SAML2\XML\Chunk $decl
     * @return void
     */
    private function setDecl(Chunk $decl): void
    {
        $this->decl = $decl;
    }


    /**
     * Convert XML into a AuthnContextDecl
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\saml\AuthnContextDecl
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AuthnContextDecl');
        Assert::same($xml->namespaceURI, Constants::NS_SAML);

        /** @psalm-var \DOMElement $xml->childNodes[1] */
        return new self(new Chunk($xml->childNodes[1]));
    }


    /**
     * Convert this AuthContextDecl to XML.
     *
     * @param \DOMElement|null $parent The element we should append this AuthnContextDecl to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        if ($parent === null) {
            $doc = DOMDocumentFactory::create();
            $e = $doc->createElementNS(Constants::NS_SAML, 'saml:AuthnContextDecl');
            $doc->appendChild($e);
        } else {
            $e = $parent->ownerDocument->createElementNS(Constants::NS_SAML, 'saml:AuthnContextDecl');
            $parent->appendChild($e);
        }

        $e->appendChild($e->ownerDocument->importNode($this->decl->getXML(), true));

        return $e;
    }
}
