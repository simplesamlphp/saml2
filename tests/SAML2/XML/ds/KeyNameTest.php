<?php

declare(strict_types=1);

namespace SAML2\XML\ds;

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\ds\KeyNameTest
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class KeyNameTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $keyName = new KeyName('testkey');

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $keyName->toXML($document->firstChild);

        $keyNameElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'KeyName\' and namespace-uri()=\'' . KeyName::NS . '\']'
        );
        $this->assertCount(1, $keyNameElements);
        $keyNameElement = $keyNameElements[0];
        $this->assertEquals('testkey', $keyNameElement->textContent);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<ds:KeyName xmlns:ds="' . KeyName::NS . '">testkey</ds:KeyName>'
        );

        $keyInfo = keyName::fromXML($document->firstChild);
        $this->assertEquals('testkey', $keyInfo->getName());
    }
}
