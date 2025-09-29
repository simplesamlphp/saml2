<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\XML\EncryptableElementTrait;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSecurity\Backend\EncryptionBackend;
use SimpleSAML\XMLSecurity\XML\EncryptableElementInterface;

use function array_change_key_case;
use function array_filter;
use function array_key_exists;
use function array_keys;

/**
 * Class representing the saml:NameID element.
 *
 * @package simplesamlphp/saml2
 */
final class NameID extends NameIDType implements
    EncryptableElementInterface,
    SchemaValidatableElementInterface
{
    use EncryptableElementTrait;
    use SchemaValidatableElementTrait;


    /**
     * Initialize a saml:NameID
     *
     * @param string $value
     * @param string|null $NameQualifier
     * @param string|null $SPNameQualifier
     * @param string|null $Format
     * @param string|null $SPProvidedID
     */
    public function __construct(
        string $value,
        ?string $NameQualifier = null,
        ?string $SPNameQualifier = null,
        ?string $Format = null,
        ?string $SPProvidedID = null,
    ) {
        switch ($Format) {
            case C::NAMEID_EMAIL_ADDRESS:
                Assert::email(
                    $value,
                    "The content %s of the NameID was not in the format specified by the Format attribute",
                );
                break;
            case C::NAMEID_ENTITY:
                /* 8.3.6: he NameQualifier, SPNameQualifier, and SPProvidedID attributes MUST be omitted. */
                Assert::null($NameQualifier, "Entity Identifier included a disallowed NameQualifier attribute.");
                Assert::null($SPNameQualifier, "Entity Identifier included a disallowed SPNameQualifier attribute.");
                Assert::null($SPProvidedID, "Entity Identifier included a disallowed SPProvidedID attribute.");
                break;
            case C::NAMEID_PERSISTENT:
                /* 8.3.7: Persistent name identifier values MUST NOT exceed a length of 256 characters. */
                Assert::maxLength(
                    $value,
                    256,
                    "Persistent name identifier values MUST NOT exceed a length of 256 characters.",
                );
                break;
            case C::NAMEID_TRANSIENT:
                /* 8.3.8: Transient name identifier values MUST NOT exceed a length of 256 characters. */
                Assert::maxLength(
                    $value,
                    256,
                    "Transient name identifier values MUST NOT exceed a length of 256 characters.",
                );
                break;
        }

        parent::__construct($value, $NameQualifier, $SPNameQualifier, $Format, $SPProvidedID);
    }


    public function getEncryptionBackend(): ?EncryptionBackend
    {
        // return the encryption backend you want to use,
        // or null if you are fine with the default
        return null;
    }


    /**
     * Create a class from an array
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        $data = self::processArrayContents($data);

        return new static(
            $data['value'],
            $data['NameQualifier'] ?? null,
            $data['SPNameQualifier'] ?? null,
            $data['Format'] ?? null,
            $data['SPProvidedID'] ?? null,
        );
    }


    /**
     * Validates an array representation of this object and returns the same array with
     * rationalized keys (casing) and parsed sub-elements.
     *
     * @param array $data
     * @return array $data
     */
    private static function processArrayContents(array $data): array
    {
        $data = array_change_key_case($data, CASE_LOWER);

        // Make sure the array keys are known for this kind of object
        Assert::allOneOf(
            array_keys($data),
            [
                'value',
                'format',
                'namequalifier',
                'spnamequalifier',
                'spprovidedid',
            ],
            ArrayValidationException::class,
        );

        Assert::keyExists($data, 'value', ArrayValidationException::class);
        Assert::string($data['value'], ArrayValidationException::class);
        $retval = ['value' => $data['value']];

        if (array_key_exists('format', $data)) {
            Assert::string($data['format'], ArrayValidationException::class);
            $retval['Format'] = $data['format'];
        }

        if (array_key_exists('namequalifier', $data)) {
            Assert::string($data['namequalifier'], ArrayValidationException::class);
            $retval['NameQualifier'] = $data['namequalifier'];
        }

        if (array_key_exists('spnamequalifier', $data)) {
            Assert::string($data['spnamequalifier'], ArrayValidationException::class);
            $retval['SPNameQualifier'] = $data['spnamequalifier'];
        }

        if (array_key_exists('spprovidedid', $data)) {
            Assert::string($data['spprovidedid'], ArrayValidationException::class);
            $retval['SPProvidedID'] = $data['spprovidedid'];
        }

        return $retval;
    }


    /**
     * Create an array from this class
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [
            'value' => $this->getContent(),
            'Format' => $this->getFormat(),
            'NameQualifier' => $this->getNameQualifier(),
            'SPNameQualifier' => $this->getSPNameQualifier(),
            'SPProvidedID' => $this->getSPProvidedID(),
        ];

        return array_filter($data);
    }
}
