<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

use SimpleSAML\SAML2\Metadata\IdentityProvider;
use SimpleSAML\SAML2\Metadata\ServiceProvider;

interface MetadataProviderInterface
{
    /**
     * Find IdP-metadata based on a SHA-1 hash of the entityID. Return `null` if not found.
     */
    public function getIdPMetadataForSha1(string $hash): ?IdentityProvider;


    /**
     * Find SP-metadata based on a SHA-1 hash of the entityID. Return `null` if not found.
     */
    public function getSPMetadataForSha1(string $hash): ?ServiceProvider;


    /**
     * Find IdP-metadata based on an entityID. Return `null` if not found.
     */
    public function getIdPMetadata(string $entityId): ?IdentityProvider;


    /**
     * Find SP-metadata based on an entityID. Return `null` if not found.
     */
    public function getSPMetadata(string $entityId): ?ServiceProvider;
}
