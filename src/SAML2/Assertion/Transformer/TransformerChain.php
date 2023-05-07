<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Transformer;

use SimpleSAML\SAML2\Assertion;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\IdentityProviderAware;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\Configuration\ServiceProviderAware;

class TransformerChain implements Transformer
{
    /**
     * @var \SimpleSAML\SAML2\Assertion\Transformer\Transformer[]
     */
    private array $transformers = [];


    /**
     * Constructor for TransformerChain
     *
     * @param \SimpleSAML\SAML2\Configuration\IdentityProvider $identityProvider
     * @param \SimpleSAML\SAML2\Configuration\ServiceProvider $serviceProvider
     */
    public function __construct(
        private IdentityProvider $identityProvider,
        private ServiceProvider $serviceProvider
    ) {
    }


    /**
     * @param \SimpleSAML\SAML2\Assertion\Transformer\Transformer $transformer
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
     * @param \SimpleSAML\SAML2\Assertion $assertion
     *
     * @return \SimpleSAML\SAML2\Assertion
     */
    public function transform(Assertion $assertion): Assertion
    {
        foreach ($this->transformers as $transformer) {
            $assertion = $transformer->transform($assertion);
        }

        return $assertion;
    }
}
