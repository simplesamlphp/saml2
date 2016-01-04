<?php

namespace SAML2;

/**
 * Class \SAML2\SignedElementHelperMock
 */
class SignedElementHelperMock extends SignedElementHelper
{
    /**
     * @param \DOMElement $xml
     */
    public function __construct(\DOMElement $xml = null)
    {
        parent::__construct($xml);
    }

    /**
     * @return \DOMElement
     */
    public function toSignedXML()
    {
        $doc = DOMDocumentFactory::create();
        $root = $doc->createElement('root');
        $doc->appendChild($root);

        $child = $doc->createElement('child');
        $root->appendChild($child);

        $txt = $doc->createTextNode('sometext');
        $child->appendChild($txt);

        $this->signElement($root, $child);

        return $root;
    }
}
