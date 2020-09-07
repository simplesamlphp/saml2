<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\XML\ds\Signature;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class for SAML 2 LogoutResponse messages.
 *
 * @package simplesamlphp/saml2
 */
class LogoutResponse extends AbstractStatusResponse
{
    /**
     * Convert XML into an LogoutResponse
     *
     * @param \DOMElement $xml
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'LogoutResponse', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, LogoutResponse::NS, InvalidDOMElementException::class);
        Assert::same('2.0', self::getAttribute($xml, 'Version'));

        $issueInstant = XMLUtils::xsDateTimeToTimestamp(self::getAttribute($xml, 'IssueInstant'));

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::countBetween($issuer, 0, 1);

        $status = Status::getChildrenOfClass($xml);
        Assert::count($status, 1);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one saml:Extensions element is allowed.');

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.');

        $response = new self(
            array_pop($status),
            array_pop($issuer),
            self::getAttribute($xml, 'ID'),
            $issueInstant,
            self::getAttribute($xml, 'InResponseTo', null),
            self::getAttribute($xml, 'Destination', null),
            self::getAttribute($xml, 'Consent', null),
            empty($extensions) ? null : array_pop($extensions)
        );

        if (!empty($signature)) {
            $response->setSignature($signature[0]);
            $response->messageContainedSignatureUponConstruction = true;
        }

        return $response;
    }
}
