<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Transformer;

use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\IdentityProviderAware;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\Configuration\ServiceProviderAware;

class TransformerChain implements TransformerInterface
{
    /** @var \SimpleSAML\SAML2\Assertion\Transformer\TransformerInterface[] */
    private $transformers = [];

    /** @var \SimpleSAML\SAML2\Configuration\IdentityProvider */
    private $identityProvider;

    /** @var \SimpleSAML\SAML2\Configuration\ServiceProvider */
    private $serviceProvider;


    /**
     * Constructor for TransformerChain
     *
     * @param \SimpleSAML\SAML2\Configuration\IdentityProvider $identityProvider
     * @param \SimpleSAML\SAML2\Configuration\ServiceProvider $serviceProvider
     */
    public function __construct(
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider
    ) {
        $this->identityProvider = $identityProvider;
        $this->serviceProvider  = $serviceProvider;
    }


    /**
     * @param \SimpleSAML\SAML2\Assertion\Transformer\TransformerInterface $transformer
     * @return void
     */
    public function addTransformerStep(TransformerInterface $transformer): void
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
     * @param \SimpleSAML\SAML2\XML\saml\Assertion $assertion
     *
     * @return \SimpleSAML\SAML2\XML\saml\Assertion
     */
    public function transform(Assertion $assertion): Assertion
    {
        foreach ($this->transformers as $transformer) {
            $assertion = $transformer->transform($assertion);
        }

        return $assertion;
    }
}
