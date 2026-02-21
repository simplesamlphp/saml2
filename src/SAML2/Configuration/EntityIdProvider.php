<?php

declare(strict_types=1);

namespace SAML2\Configuration;

/**
 * Interface \SAML2\Configuration\EntityIdProvider
 */
interface EntityIdProvider
{
    /**
     */
    public function getEntityId(): ?string;
}
