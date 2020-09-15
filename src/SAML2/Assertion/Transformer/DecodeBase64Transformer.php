<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Transformer;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assertion\Exception\InvalidAssertionException;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\IdentityProviderAware;
use SimpleSAML\SAML2\XML\saml\Assertion;

class DecodeBase64Transformer implements
    TransformerInterface,
    IdentityProviderAware
{
    /**
     * @var \SimpleSAML\SAML2\Configuration\IdentityProvider
     */
    private IdentityProvider $identityProvider;


    /**
     * @param \SimpleSAML\SAML2\Configuration\IdentityProvider $identityProvider
     */
    public function setIdentityProvider(IdentityProvider $identityProvider): void
    {
        $this->identityProvider = $identityProvider;
    }


    /**
     * @param \SimpleSAML\SAML2\XML\saml\Assertion $assertion
     * @return \SimpleSAML\SAML2\XML\saml\Assertion
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException if assertions are false
     */
    public function transform(Assertion $assertion): Assertion
    {
        Assert::notEmpty($this->identityProvider);

        if (!$this->identityProvider->hasBase64EncodedAttributes()) {
            return $assertion;
        }

        $attributes = $assertion->getAttributes();
        $decodedAttributes = [];
        foreach ($attributes as $name => $values) {
            $decodedAttributes[$name] = [];
            foreach ($values as $value) {
                $decoded = $this->decodeValue($value);
                $decodedAttributes[$name] = array_merge($decodedAttributes[$name], $decoded);
            }
        }
        $assertion->setAttributes($decodedAttributes);
        return $assertion;
    }


    /**
     * @param string $value
     * @return array
     */
    private function decodeValue(string $value): array
    {
        $elements = explode('_', $value);
        $decoded = [];
        foreach ($elements as $element) {
            $result = base64_decode($element, true);
            if ($result === false) {
                throw new InvalidAssertionException(sprintf('Invalid base64 encoded attribute value "%s"', $element));
            }
            $decoded[] = $result;
        }
        return $decoded;
    }
}
