<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use SimpleSAML\SAML2\Metadata\IdentityProvider;
use SimpleSAML\SAML2\Metadata\ServiceProvider;
use SimpleSAML\SAML2\MetadataProviderInterface;

use function sha1;

final class MockMetadataProvider implements MetadataProviderInterface
{
    /** @var \SimpleSAML\SAML2\Metadata\AbstractProvider[] $entities */
    protected array $entities;


    /**
     * @param \SimpleSAML\SAML2\Metadata\AbstractProvider[] $entities
     */
    public function __construct(array $entities)
    {
        $this->entities = $entities;
    }


    /**
     * Find IdP-metadata based on a SHA-1 hash of the entityID. Return `null` if not found.
     */
    public function getIdPMetadataForSha1(string $hash): ?IdentityProvider
    {
        foreach ($this->entities as $entity) {
            if (($entity instanceof IdentityProvider) && ($hash === sha1($entity->getEntityId()))) {
                return $entity;
            }
        }

        return null;
    }


    /**
     * Find SP-metadata based on a SHA-1 hash of the entityID. Return `null` if not found.
     */
    public function getSPMetadataForSha1(string $hash): ?ServiceProvider
    {
        foreach ($this->entities as $entity) {
            if (($entity instanceof ServiceProvider) && ($hash === sha1($entity->getEntityId()))) {
                return $entity;
            }
        }

        return null;
    }


    /**
     * Find IdP-metadata based on an entityID. Return `null` if not found.
     */
    public function getIdPMetadata(string $entityId): ?IdentityProvider
    {
        foreach ($this->entities as $entity) {
            if (($entity instanceof IdentityProvider) && ($entityId === $entity->getEntityId())) {
                return $entity;
            }
        }

        return null;
    }


    /**
     * Find SP-metadata based on an entityID. Return `null` if not found.
     */
    public function getSPMetadata(string $entityId): ?ServiceProvider
    {
        foreach ($this->entities as $entity) {
            if (($entity instanceof ServiceProvider) && ($entityId === $entity->getEntityId())) {
                return $entity;
            }
        }

        return null;
    }
}
