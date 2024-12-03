<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Process;

use SimpleSAML\SAML2\Metadata;

interface ServiceProviderAwareInterface
{
    /**
     * Set the SP metadata.
     */
    public function setSPMetadata(Metadata\ServiceProvider $spMetadata): void;
}
