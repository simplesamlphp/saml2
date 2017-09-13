<?php

namespace SAML2;

/**
 * Class which implements the HTTP Holder-of-key binding.
 *
 * @package SimpleSAMLphp
 */
class HTTPHoK extends HTTPPost
{
    /**
     * Return the URN of this binding
     *
     * @return string The URN of the binding
     */
    public function getURN()
    {
        return SAML2_Const::BINDING_HOK_SSO;
    }

    /**
     * Receive a SAML 2 message sent using the HTTP Holder-of-key binding.
     *
     * Logs warning if client didn't present certificate with request
     * SSL_CLIENT_CERT is the server variable for Apache
     * CERT_SUBJECT is the server variable for IIS
     *
     * @return \SAML2\Message The received message.
     * @throws \Exception
     */
    public function receive()
    {
        // spec states that HoK binding requires client to present certificate
        // (http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-holder-of-key-browser-sso.html)
        if (!isset($_SERVER["SSL_CLIENT_CERT"]) && !isset($_SERVER["CERT_SUBJECT"])) {
             $logger = Utils::getContainer()->getLogger();
             $logger->warning('Missing client presented certificate.');
        }
        return parent::receive();
    }
}
