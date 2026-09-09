<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Process\ConstraintValidation\Response;

use SimpleSAML\SAML2\{Binding, Metadata};
use SimpleSAML\SAML2\Exception\Protocol\ResourceNotRecognizedException;
use SimpleSAML\SAML2\Process\{ServiceProviderAwareInterface, ServiceProviderAwareTrait};
use SimpleSAML\SAML2\Process\ConstraintValidation\ConstraintValidatorInterface;
use SimpleSAML\XML\SerializableElementInterface;

final class DestinationMatches implements ConstraintValidatorInterface
{
    /**
     * DestinationMatches constructor.
     *
     * @param \SimpleSAML\SAML2\Metadata\ServiceProvider $spMetadata
     * @param \SimpleSAML\SAML2\Binding $binding
     */
    public function __construct(
        private Metadata\ServiceProvider $spMetadata,
        private Binding $binding,
    ) {
    }


    /**
     * @param \SimpleSAML\XML\SerializableElementInterface $response
     */
    public function validate(SerializableElementInterface $response): void
    {
        // Validate that the destination matches the appropriate endpoint from the SP-metadata
        foreach ($this->spMetadata->getAssertionConsumerService() as $assertionConsumerService) {
            if ($assertionConsumerService->getLocation() === $response->getDestination()) {
                if (Binding::getBinding($assertionConsumerService->getBinding()) instanceof $this->binding) {
                    return;
                }
            }
        }
        throw new ResourceNotRecognizedException();
    }
}
