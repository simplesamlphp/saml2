<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\Protocol\{RequestVersionTooHighException, RequestVersionTooLowException};
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLDateTimeValue, SAMLStringValue};
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};
use SimpleSAML\XMLSchema\Exception\{InvalidDOMElementException, TooManyElementsException};
use SimpleSAML\XMLSchema\Type\{IDValue, NCNameValue};
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_pop;
use function version_compare;

/**
 * The \SimpleSAML\SAML2\XML\samlp\ArtifactResponse,
 *  is the response to the \SimpleSAML\SAML2\XML\samlp\ArtifactResolve.
 *
 * @package simplesamlphp/saml2
 */
class ArtifactResponse extends AbstractStatusResponse implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Constructor for SAML 2 ArtifactResponse.
     *
     * @param \SimpleSAML\XMLSchema\Type\IDValue $id
     * @param \SimpleSAML\SAML2\XML\samlp\Status $status
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue $issueInstant
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param \SimpleSAML\XMLSchema\Type\NCNameValue|null $inResponseTo
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $destination
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions|null $extensions
     * @param \SimpleSAML\SAML2\XML\samlp\AbstractMessage|null $message
     */
    final public function __construct(
        IDValue $id,
        Status $status,
        SAMLDateTimeValue $issueInstant,
        ?Issuer $issuer = null,
        ?NCNameValue $inResponseTo = null,
        ?SAMLAnyURIValue $destination = null,
        ?SAMLAnyURIValue $consent = null,
        ?Extensions $extensions = null,
        protected ?AbstractMessage $message = null,
    ) {
        parent::__construct(
            $id,
            $status,
            $issueInstant,
            $issuer,
            $inResponseTo,
            $destination,
            $consent,
            $extensions,
        );
    }


    /**
     * Collect the value of the any-property
     *
     * @return \SimpleSAML\SAML2\XML\samlp\AbstractMessage|null
     */
    public function getMessage(): ?AbstractMessage
    {
        return $this->message;
    }


    /**
     * Convert XML into an ArtifactResponse
     *
     * @param \DOMElement $xml
     * @return static
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'ArtifactResponse', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, ArtifactResponse::NS, InvalidDOMElementException::class);

        $version = self::getAttribute($xml, 'Version', SAMLStringValue::class);
        Assert::true(version_compare('2.0', $version->getValue(), '<='), RequestVersionTooLowException::class);
        Assert::true(version_compare('2.0', $version->getValue(), '>='), RequestVersionTooHighException::class);

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::countBetween($issuer, 0, 1);

        // find message; it should come last, after the Status-element
        $status = XPath::xpQuery($xml, './saml_protocol:Status', XPath::getXPath($xml));
        $status = $status[0];
        $message = null;

        /** @psalm-suppress RedundantCondition */
        for ($child = $status->nextSibling; $child !== null; $child = $child->nextSibling) {
            if ($child instanceof DOMElement) {
                $message = MessageFactory::fromXML($child);
                break;
            }
            /* Ignore comments and text nodes. */
        }

        $status = Status::getChildrenOfClass($xml);
        Assert::count($status, 1);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount(
            $extensions,
            1,
            'Only one saml:Extensions element is allowed.',
            TooManyElementsException::class,
        );

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount(
            $signature,
            1,
            'Only one ds:Signature element is allowed.',
            TooManyElementsException::class,
        );

        $response = new static(
            self::getAttribute($xml, 'ID', IDValue::class),
            array_pop($status),
            self::getAttribute($xml, 'IssueInstant', SAMLDateTimeValue::class),
            empty($issuer) ? null : array_pop($issuer),
            self::getOptionalAttribute($xml, 'InResponseTo', NCNameValue::class, null),
            self::getOptionalAttribute($xml, 'Destination', SAMLAnyURIValue::class, null),
            self::getOptionalAttribute($xml, 'Consent', SAMLAnyURIValue::class, null),
            empty($extensions) ? null : array_pop($extensions),
            $message,
        );

        if (!empty($signature)) {
            $response->setSignature($signature[0]);
            $response->setXML($xml);
        }

        return $response;
    }


    /**
     * Convert this message to an unsigned XML document.
     * This method does not sign the resulting XML document.
     *
     * @return \DOMElement The root element of the DOM tree
     */
    protected function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);

        $this->getMessage()?->toXML($e);

        return $e;
    }
}
