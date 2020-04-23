<?php

declare(strict_types=1);

namespace SAML2\XML;

use DOMElement;
use SAML2\Compat\ContainerSingleton;
use SAML2\XML\saml\BaseID;
use SAML2\XML\saml\EncryptedID;
use SAML2\XML\saml\NameID;
use SAML2\XML\saml\IdentifierInterface;
use Webmozart\Assert\Assert;

/**
 * Trait grouping common functionality for elements that can hold identifiers.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
trait IdentifierTrait
{
    /**
     * The identifier for this element.
     *
     * @var \SAML2\XML\saml\IdentifierInterface|null
     */
    protected $identifier = null;


    /**
     * Collect the value of the identifier-property
     *
     * @return \SAML2\XML\saml\IdentifierInterface|null
     */
    public function getIdentifier(): ?IdentifierInterface
    {
        return $this->identifier;
    }


    /**
     * Set the value of the identifier-property
     *
     * @param \SAML2\XML\saml\IdentifierInterface|null $identifier
     * @return void
     */
    protected function setIdentifier(?IdentifierInterface $identifier): void
    {
        $this->identifier = $identifier;
    }


    /**
     * Retrieve an identifier of any type from XML
     *
     * @param \DOMElement $xml
     * @return \SAML2\XML\saml\IdentifierInterface|null
     */
    protected static function getIdentifierFromXML(DOMElement $xml): ?IdentifierInterface
    {
        $class = static::NS_PREFIX . ':' . self::getClassName(static::class);

        $baseId = BaseID::getChildrenOfClass($xml);
        $nameId = NameID::getChildrenOfClass($xml);
        $encryptedId = EncryptedID::getChildrenOfClass($xml);

        // We accept only one of BaseID, NameID or EncryptedID
        Assert::maxCount($baseId, 1, 'More than one <saml:BaseID> in <' . $class . '>.');
        Assert::maxCount($nameId, 1, 'More than one <saml:NameID> in <' . $class . '>.');
        Assert::maxCount($encryptedId, 1, 'More than one <saml:EncryptedID> in <' . $class . '>.');

        $identifiers = array_merge($baseId, $nameId, $encryptedId);
        Assert::maxCount(
            $identifiers,
            1,
            'A <' . $class . '> can contain exactly one of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>.'
        );

        /** @psalm-var \SAML2\XML\saml\IdentifierInterface|null $identifier */
        $identifier = array_pop($identifiers);

        if ($identifier !== null) {
            // check if the identifier is a BaseID that we can process
            if ($identifier instanceof BaseID) {
                $type = $identifier->getType();
                $container = ContainerSingleton::getInstance();

                /** @var \SAML2\XML\saml\CustomIdentifierInterface $handler */
                $handler = $container->getIdentifierHandler($type);

                if ($handler !== null) {
                    // we have a handler, use it for this id
                    $list = $xml->getElementsByTagNameNS(BaseID::NS, 'BaseID');

                    /** @var \DOMElement $element */
                    $element = $list->item(0);
                    $identifier = $handler::fromXML($element);
                }
            }
        }

        return $identifier;
    }
}
