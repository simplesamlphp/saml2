<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\XML\saml\{AbstractBaseID, EncryptedID, IdentifierInterface, NameID};
use SimpleSAML\XMLSchema\Exception\TooManyElementsException;

use function array_pop;

/**
 * Trait grouping common functionality for elements that can hold identifiers.
 *
 * @package simplesamlphp/saml2
 */
trait IdentifierTrait
{
    /**
     * The identifier for this element.
     *
     * @var \SimpleSAML\SAML2\XML\saml\IdentifierInterface|null
     */
    protected ?IdentifierInterface $identifier = null;


    /**
     * Collect the value of the identifier-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\IdentifierInterface|null
     */
    public function getIdentifier(): ?IdentifierInterface
    {
        return $this->identifier;
    }


    /**
     * Set the value of the identifier-property
     *
     * @param \SimpleSAML\SAML2\XML\saml\IdentifierInterface|null $identifier
     */
    protected function setIdentifier(?IdentifierInterface $identifier): void
    {
        $this->identifier = $identifier;
    }


    /**
     * Retrieve an identifier of any type from XML
     *
     * @param \DOMElement $xml
     * @return \SimpleSAML\SAML2\XML\saml\IdentifierInterface|null
     * @throws \SimpleSAML\XMLSchema\Exception\TooManyElementsException if too many child-elements of a type are specified
     */
    protected static function getIdentifierFromXML(DOMElement $xml): ?IdentifierInterface
    {
        $class = static::NS_PREFIX . ':' . self::getClassName(static::class);

        $baseId = AbstractBaseID::getChildrenOfClass($xml);
        $nameId = NameID::getChildrenOfClass($xml);
        $encryptedId = EncryptedID::getChildrenOfClass($xml);

        // We accept only one of BaseID, NameID or EncryptedID
        Assert::maxCount(
            $baseId,
            1,
            'More than one <saml:BaseID> in <' . $class . '>.',
            TooManyElementsException::class,
        );
        Assert::maxCount(
            $nameId,
            1,
            'More than one <saml:NameID> in <' . $class . '>.',
            TooManyElementsException::class,
        );
        Assert::maxCount(
            $encryptedId,
            1,
            'More than one <saml:EncryptedID> in <' . $class . '>.',
            TooManyElementsException::class,
        );

        $identifiers = array_merge($baseId, $nameId, $encryptedId);
        Assert::maxCount(
            $identifiers,
            1,
            'A <' . $class . '> can contain exactly one of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>.',
            TooManyElementsException::class,
        );

        /** @psalm-var \SimpleSAML\SAML2\XML\saml\IdentifierInterface|null $identifier */
        $identifier = array_pop($identifiers);

        return $identifier;
    }
}
