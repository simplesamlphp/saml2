<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\XML\SignableElementTrait;
use SimpleSAML\SAML2\XML\SignedElementTrait;
use SimpleSAML\XMLSecurity\XML\SignableElementInterface;
use SimpleSAML\XMLSecurity\XML\SignedElementInterface;

use function method_exists;

/**
 * Abstract class that represents a signed metadata element.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractSignedMdElement extends AbstractMdElement implements
    SignableElementInterface,
    SignedElementInterface
{
    use SignableElementTrait;
    use SignedElementTrait {
        SignedElementTrait::getBlacklistedAlgorithms insteadof SignableElementTrait;
    }


    /**
     * The original signed XML
     *
     * @var \DOMElement
     */
    protected DOMElement $xml;


    /**
     * Get the XML element.
     *
     * @return \DOMElement
     */
    public function getXML(): DOMElement
    {
        return $this->xml;
    }


    /**
     * Set the XML element.
     *
     * @param \DOMElement $xml
     */
    protected function setXML(DOMElement $xml): void
    {
        $this->xml = $xml;
    }


    /**
     * @param \DOMElement|null $parent The EntityDescriptor we should append this SPSSODescriptor to.
     * @return \DOMElement
     * @throws \Exception
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        if ($this->isSigned() === true && $this->signer === null) {
            // We already have a signed document and no signer was set to re-sign it
            if ($parent === null) {
                return $this->getXML();
            }

            $node = $parent->ownerDocument?->importNode($this->getXML(), true);
            $parent->appendChild($node);
            return $parent;
        }

        $e = $this->toUnsignedXML($parent);
        // This is a dirty hack, but if we add the xsi-type on AbstractRoleDescriptor we cannot
        // get the tests to pass because the attribute-order is messed up. This has something
        // to do with the fact that toUnsignedXML's recursive nature.
        if (method_exists(static::class, 'getXsiTypePrefix')) {
            $e->setAttributeNS(
                'http://www.w3.org/2000/xmlns/',
                'xmlns:' . static::getXsiTypePrefix(),
                static::getXsiTypeNamespaceURI(),
            );
        }

        if ($this->signer !== null) {
            $signedXML = $this->doSign($e);
            $signedXML->insertBefore($this->signature?->toXML($signedXML), $signedXML->firstChild);
            return $signedXML;
        }

        return $e;
    }


    /**
     * @param  \DOMElement|null $parent
     * @return \DOMElement
     */
    abstract public function toUnsignedXML(?DOMElement $parent = null): DOMElement;
}
