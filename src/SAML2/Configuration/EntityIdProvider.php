<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Configuration;

/**
 * Interface \SimpleSAML\SAML2\Configuration\EntityIdProvider
 */
interface EntityIdProvider
{
    /**
     * @return null|string
     */
    public function getEntityId(): ?string;
}
