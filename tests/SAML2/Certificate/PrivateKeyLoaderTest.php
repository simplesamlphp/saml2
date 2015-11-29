<?php

namespace SAML2\Certificate;

use SAML2\Configuration\PrivateKey;

class PrivateKeyLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \SAML2\Certificate\PrivateKeyLoader
     */
    private $privateKeyLoader;

    public function setUp()
    {
        $this->privateKeyLoader = new PrivateKeyLoader();
    }

    /**
     * @group        certificate
     * @test
     * @dataProvider privateKeyTestProvider
     *
     * @param \SAML2\Configuration\PrivateKey $configuredKey
     */
    public function loading_a_configured_private_key_returns_a_certificate_private_key(
        PrivateKey $configuredKey
    ) {
        $resultingKey = $this->privateKeyLoader->loadPrivateKey($configuredKey);

        $this->assertInstanceOf('\SAML2\Certificate\PrivateKey', $resultingKey);
        $this->assertEquals($resultingKey->getKeyAsString(), "This would normally contain the private key data.\n");
        $this->assertEquals($resultingKey->getPassphrase(), $configuredKey->getPassPhrase());
    }

    /**
     * Dataprovider for 'loading_a_configured_private_key_returns_a_certificate_private_key'
     *
     * @return array
     */
    public function privateKeyTestProvider()
    {
        return array(
            'no passphrase'   => array(
                new PrivateKey(
                    dirname(__FILE__) . '/File/a_fake_private_key_file.pem',
                    PrivateKey::NAME_DEFAULT
                )
            ),
            'with passphrase' => array(
                new PrivateKey(
                    dirname(__FILE__) . '/File/a_fake_private_key_file.pem',
                    PrivateKey::NAME_DEFAULT,
                    'foo bar baz'
                )
            ),
        );
    }
}
