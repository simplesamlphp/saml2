<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use SAML2\Constants;
use SAML2\Utilities\Temporal;
use SAML2\Utils;
use SAML2\XML\saml\Issuer;

/**
 * Base class for all SAML 2 request messages.
 *
 * Implements samlp:RequestAbstractType. All of the elements in that type is
 * stored in the \SAML2\XML\AbstractMessage class, and this class is therefore empty. It
 * is included mainly to make it easy to separate requests from responses.
 *
 * @package SimpleSAMLphp
 */
abstract class AbstractRequest extends AbstractMessage
{
    /**
     * Constructor for SAML 2 subject query messages.
     *
     * @param string $version
     * @param string|null $destination
     * @param string|null $consent
     * @param \SAML2\XML\saml\Issuer|null $issuer
     */
    protected function __construct(
        string $version,
        string $destination = null,
        string $consent = null,
        Issuer $issuer = null
    ) {
        $this->setId(Utils::getContainer()->generateId());
        $this->setVersion($version);
        $this->setIssueInstant(Temporal::getTime());
        $this->setDestination($destination);
        $this->setConsent($consent);
        $this->setIssuer($issuer);
        //$this->setExtensions($extensions);
    }


    /**
     * Retrieve the version of this message.
     *
     * @return string The version of this message
     */
    public function getVersion(): string
    {
        return $this->version;
    }


    /**
     * Set the version of this message.
     *
     * @param string $version The version of this message
     * @return void
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }
}
