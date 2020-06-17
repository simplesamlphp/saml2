<?php

declare(strict_types=1);

namespace SAML2\Assertion\Transformer;

use SAML2\Assertion\Exception\InvalidAssertionException;
use SAML2\Configuration\IdentityProvider;
use SAML2\Configuration\IdentityProviderAware;
use SAML2\XML\saml\Assertion;
use SimpleSAML\Assert\Assert;

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
