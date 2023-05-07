<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DOMElement;
use SimpleSAML\SAML2\SignedElementHelper;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SimpleSAML\SAML2\SignedElementHelperMock
 */
class SignedElementHelperMock extends SignedElementHelper
{
    /**
     * @param \DOMElement $xml
     */
    public function __construct(DOMElement $xml = null)
    {
        parent::__construct($xml);
    }


    /**
     * @return \DOMElement
     */
    public function toSignedXML(): DOMElement
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
