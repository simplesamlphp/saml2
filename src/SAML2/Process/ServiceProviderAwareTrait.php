<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Process;

use SimpleSAML\SAML2\Metadata;

trait ServiceProviderAwareTrait
{
    protected Metadata\ServiceProvider $spMetadata;

    /**
     * Set the SP metadata.
     */
    public function setSPMetadata(Metadata\ServiceProvider $spMetadata): void
    {
        $this->spMetadata = $spMetadata;
    }
}
