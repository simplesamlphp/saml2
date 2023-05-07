<?php

declare(strict_types=1);

namespace SAML2\Assertion\Transformer;

use SAML2\Assertion;
use SAML2\Configuration\IdentityProvider;
use SAML2\Configuration\IdentityProviderAware;
use SAML2\Configuration\ServiceProvider;
use SAML2\Configuration\ServiceProviderAware;

class TransformerChain implements Transformer
{
    /**
     * @var \SAML2\Assertion\Transformer\Transformer[]
     */
    private array $transformers = [];


    /**
     * Constructor for TransformerChain
     *
     * @param IdentityProvider $identityProvider
     * @param ServiceProvider $serviceProvider
     */
    public function __construct(
        private IdentityProvider $identityProvider,
        private ServiceProvider $serviceProvider
    ) {
    }


    /**
     * @param Transformer $transformer
     * @return void
     */
    public function addTransformerStep(Transformer $transformer): void
    {
        if ($transformer instanceof IdentityProviderAware) {
            $transformer->setIdentityProvider($this->identityProvider);
        }

        if ($transformer instanceof ServiceProviderAware) {
            $transformer->setServiceProvider($this->serviceProvider);
        }

        $this->transformers[] = $transformer;
    }


    /**
     * @param \SAML2\Assertion $assertion
     *
     * @return \SAML2\Assertion
     */
    public function transform(Assertion $assertion): Assertion
    {
        foreach ($this->transformers as $transformer) {
            $assertion = $transformer->transform($assertion);
        }

        return $assertion;
    }
}
