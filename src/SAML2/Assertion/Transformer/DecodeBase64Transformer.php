<?php

declare(strict_types=1);

namespace SAML2\Assertion\Transformer;

use SAML2\XML\saml\Assertion;
use SAML2\Configuration\IdentityProvider;
use SAML2\Configuration\IdentityProviderAware;
use Webmozart\Assert\Assert;

class DecodeBase64Transformer implements
    TransformerInterface,
    IdentityProviderAware
{
    /**
     * @var \SAML2\Configuration\IdentityProvider
     */
    private $identityProvider;


    /**
     * @param \SAML2\Configuration\IdentityProvider $identityProvider
     * @return void
     */
    public function setIdentityProvider(IdentityProvider $identityProvider): void
    {
        $this->identityProvider = $identityProvider;
    }


    /**
     * @param \SAML2\XML\saml\Assertion $assertion
     * @return \SAML2\XML\saml\Assertion
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function transform(Assertion $assertion): Assertion
    {
        Assert::notEmpty($this->identityProvider);

        if (!$this->identityProvider->hasBase64EncodedAttributes()) {
            return $assertion;
        }

        $attributes = $assertion->getAttributes();
        $keys = array_keys($attributes);
        $decoded = array_map([$this, 'decodeValue'], $attributes);

        $attributes = array_combine($keys, $decoded);

        $assertion->setAttributes($attributes);
        return $assertion;
    }


    /**
     * @param string $value
     * @return array
     */
    private function decodeValue(string $value): array
    {
        $elements = explode('_', $value);
        return array_map('base64_decode', $elements);
    }
}
