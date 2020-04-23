<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\XML\ds\Signature;
use SAML2\XML\saml\Issuer;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Class for SAML 2 LogoutResponse messages.
 *
 * @package SimpleSAMLphp
 */
class LogoutResponse extends AbstractStatusResponse
{
    /**
     * Convert XML into an LogoutResponse
     *
     * @param \DOMElement $xml
     * @return self
     * @throws \Exception
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'LogoutResponse');
        Assert::same($xml->namespaceURI, LogoutResponse::NS);
        Assert::same('2.0', self::getAttribute($xml, 'Version'));

        $id = self::getAttribute($xml, 'ID');
        $issueInstant = Utils::xsDateTimeToTimestamp(self::getAttribute($xml, 'IssueInstant'));
        $inResponseTo = self::getAttribute($xml, 'InResponseTo', null);
        $destination = self::getAttribute($xml, 'Destination', null);
        $consent = self::getAttribute($xml, 'Consent', null);

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
            empty($issuer) ? null : array_pop($issuer),
            $id,
            $issueInstant,
            $inResponseTo,
            $destination,
            $consent,
            empty($extensions) ? null : array_pop($extensions)
        );

        if (!empty($signature)) {
            $response->setSignature($signature[0]);
            $response->messageContainedSignatureUponConstruction = true;
        }

        return $response;
    }
}
