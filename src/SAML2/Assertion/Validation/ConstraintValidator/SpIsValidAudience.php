<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator;

use SimpleSAML\SAML2\Assertion;
use SimpleSAML\SAML2\Assertion\Validation\AssertionConstraintValidator;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\Configuration\ServiceProviderAware;

use function implode;
use function in_array;
use function sprintf;
use function strval;

class SpIsValidAudience implements
    AssertionConstraintValidator,
    ServiceProviderAware
{
    /**
     * @var \SimpleSAML\SAML2\Configuration\ServiceProvider
     */
    private ServiceProvider $serviceProvider;


    /**
     * @param \SimpleSAML\SAML2\Configuration\ServiceProvider $serviceProvider
     * @return void
     */
    public function setServiceProvider(ServiceProvider $serviceProvider): void
    {
        $this->serviceProvider = $serviceProvider;
    }


    /**
     * @param \SimpleSAML\SAML2\Assertion $assertion
     * @param \SimpleSAML\SAML2\Assertion\Validation\Result $result
     * @return void
     */
    public function validate(Assertion $assertion, Result $result): void
    {
        $intendedAudiences = $assertion->getValidAudiences();
        if (empty($intendedAudiences)) {
            return;
        }

        $entityId = $this->serviceProvider->getEntityId();
        if (!in_array($entityId, $intendedAudiences, true)) {
            $result->addError(sprintf(
                'The configured Service Provider [%s] is not a valid audience for the assertion. Audiences: [%s]',
                strval($entityId),
                implode('], [', $intendedAudiences)
            ));
        }
    }
}
