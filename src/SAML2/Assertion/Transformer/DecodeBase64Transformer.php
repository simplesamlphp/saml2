<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Transformer;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assertion\Exception\InvalidAssertionException;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\IdentityProviderAware;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeStatement;
use SimpleSAML\SAML2\XML\saml\AttributeValue;

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
     * @return void
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

        $statements = [];
        $attributeStatements = $assertion->getAttributeStatements();
        foreach ($attributeStatements as $attributeStatement) {
            $attributes = $attributeStatement->getAttributes();
            foreach ($attributes as $attribute) {
                $values = [];
                foreach ($attribute->getAttributeValues() as $encodedValue) {
                    $encoded = $encodedValue->getValue();
                    foreach ($this->decodeValue($encoded) as $decoded) {
                        $values[] = new AttributeValue($decoded);
                    }
                }
                $decodedAttributes[] = new Attribute($attribute->getName(), $attribute->getNameFormat(), $attribute->getFriendlyName(), $values, $attribute->getAttributesNS());
            }
            $statements[] = new AttributeStatement($decodedAttributes);
        }

        $statements = array_merge($statements, $assertion->getAuthnStatements(), $assertion->getStatements());

        return new Assertion(
            $assertion->getIssuer(),
            $assertion->getId(),
            $assertion->getIssueInstant(),
            $assertion->getSubject(),
            $assertion->getConditions(),
            $statements
        );

//        $attributes = $assertion->getAttributes();
//        $decodedAttributes = [];
//        foreach ($attributes as $name => $values) {
//            $decodedAttributes[$name] = [];
//            foreach ($values as $value) {
//                $decoded = $this->decodeValue($value);
//                $decodedAttributes[$name] = array_merge($decodedAttributes[$name], $decoded);
//            }
//        }
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
