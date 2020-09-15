<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\Assertion\Validation\AssertionConstraintValidator;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\Configuration\ServiceProviderAware;

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
     */
    public function setServiceProvider(ServiceProvider $serviceProvider): void
    {
        $this->serviceProvider = $serviceProvider;
    }


    /**
     * @param \SimpleSAML\SAML2\XML\saml\Assertion $assertion
     * @param \SimpleSAML\SAML2\Assertion\Validation\Result $result
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException if assertions are false
     */
    public function validate(Assertion $assertion, Result $result): void
    {
        Assert::notEmpty($this->serviceProvider);

        $intendedAudiences = $assertion->getValidAudiences();
        if ($intendedAudiences === null) {
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
