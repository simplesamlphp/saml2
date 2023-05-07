<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Transformer;

use SimpleSAML\SAML2\Assertion;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\IdentityProviderAware;

use function explode;
use function array_combine;
use function array_keys;
use function array_map;

class DecodeBase64Transformer implements
    Transformer,
    IdentityProviderAware
{
    /**
     * @var \SimpleSAML\SAML2\Configuration\IdentityProvider
     */
    private IdentityProvider $identityProvider;


    /**
     * @param \SimpleSAML\SAML2\Configuration\IdentityProvider $identityProvider
     * @return void
     */
    public function setIdentityProvider(IdentityProvider $identityProvider): void
    {
        $this->identityProvider = $identityProvider;
    }


    /**
     * @param \SimpleSAML\SAML2\Assertion $assertion
     * @return \SimpleSAML\SAML2\Assertion
     */
    public function transform(Assertion $assertion): Assertion
    {
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
