<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Process;

use SimpleSAML\SAML2\Metadata;

trait IdentityProviderAwareTrait
{
    protected Metadata\IdentityProvider $idpMetadata;

    /**
     * Set the IdP metadata.
     */
    public function setIdPMetadata(Metadata\IdentityProvider $idpMetadata): void
    {
        $this->idpMetadata = $idpMetadata;
    }
}
