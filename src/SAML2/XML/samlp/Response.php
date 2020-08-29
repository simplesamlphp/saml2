<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Exception\InvalidDOMElementException;
use SimpleSAML\SAML2\Exception\MissingElementException;
use SimpleSAML\SAML2\Exception\TooManyElementsException;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\ds\Signature;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\EncryptedAssertion;
use SimpleSAML\SAML2\XML\saml\Issuer;

/**
 * Class for SAML 2 Response messages.
 *
 * @package simplesamlphp/saml2
 */
class Response extends AbstractStatusResponse
{
    /**
     * The assertions in this response.
     *
     * @var (\SimpleSAML\SAML2\XML\saml\Assertion|\SimpleSAML\SAML2\XML\saml\EncryptedAssertion)[]
     */
    protected $assertions = [];


    /**
     * Constructor for SAML 2 response messages.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\Status $status
     * @param \SimpleSAML\SAML2\XML\saml\Issuer $issuer
     * @param string $id
     * @param int $issueInstant
     * @param string $inResponseTo
     * @param string|null $destination
     * @param string|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     * @param (\SimpleSAML\SAML2\XML\saml\Assertion|\SimpleSAML\SAML2\XML\saml\EncryptedAssertion)[] $assertions
     */
    public function __construct(
        Status $status,
        ?Issuer $issuer = null,
        ?string $id = null,
        ?int $issueInstant = null,
        ?string $inResponseTo = null,
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null,
        array $assertions = []
    ) {
        parent::__construct(
            $status,
            $issuer,
            $id,
            $issueInstant,
            $inResponseTo,
            $destination,
            $consent,
            $extensions
        );

        $this->setAssertions($assertions);
    }


    /**
     * Retrieve the assertions in this response.
     *
     * @return \SimpleSAML\SAML2\XML\saml\Assertion[]|\SimpleSAML\SAML2\XML\saml\EncryptedAssertion[]
     */
    public function getAssertions(): array
    {
        return $this->assertions;
    }


    /**
     * Set the assertions that should be included in this response.
     *
     * @param (\SimpleSAML\SAML2\XML\saml\Assertion|\SimpleSAML\SAML2\XML\saml\EncryptedAssertion)[] $assertions The assertions.
     * @return void
     */
    protected function setAssertions(array $assertions): void
    {
        Assert::allIsInstanceOfAny($assertions, [Assertion::class, EncryptedAssertion::class]);

        $this->assertions = $assertions;
    }


    /**
     * Convert XML into a Response element.
     *
     * @param \DOMElement $xml The input message.
     * @return self
     *
     * @throws \SimpleSAML\SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\SAML2\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\SAML2\Exception\MissingElementException if one of the mandatory child-elements is missing
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Response', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Response::NS, InvalidDOMElementException::class);
        Assert::same('2.0', self::getAttribute($xml, 'Version'));

        $id = self::getAttribute($xml, 'ID');
        /** @psalm-suppress PossiblyNullArgument */
        $issueInstant = Utils::xsDateTimeToTimestamp(self::getAttribute($xml, 'IssueInstant'));
        $inResponseTo = self::getAttribute($xml, 'InResponseTo', null);
        $destination = self::getAttribute($xml, 'Destination', null);
        $consent = self::getAttribute($xml, 'Consent', null);

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::countBetween($issuer, 0, 1);

        $status = Status::getChildrenOfClass($xml);
        Assert::minCount($status, 1, MissingElementException::class);
        Assert::maxCount($status, 1, TooManyElementsException::class);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one saml:Extensions element is allowed.', TooManyElementsException::class);

        $assertions = [];
        foreach ($xml->childNodes as $node) {
            if ($node->namespaceURI !== Constants::NS_SAML) {
                continue;
            } elseif (!($node instanceof DOMElement)) {
                continue;
            }

            if ($node->localName === 'Assertion') {
                $assertions[] = new Assertion($node);
            } elseif ($node->localName === 'EncryptedAssertion') {
                $assertions[] = EncryptedAssertion::fromXML($node);
            }
        }

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.', TooManyElementsException::class);

        $response = new self(
            array_pop($status),
            empty($issuer) ? null : array_pop($issuer),
            $id,
            $issueInstant,
            $inResponseTo,
            $destination,
            $consent,
            empty($extensions) ? null : array_pop($extensions),
            $assertions
        );

        if (!empty($signature)) {
            $response->setSignature($signature[0]);
            $response->messageContainedSignatureUponConstruction = true;
        }

        return $response;
    }


    /**
     * Convert the response message to an XML element.
     *
     * @return \DOMElement This response.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        foreach ($this->assertions as $assertion) {
            $assertion->toXML($e);
        }

        // Test for an Issuer
        $responseElements = Utils::xpQuery($e, './saml_assertion:Issuer');
        $issuer = empty($responseElements) ? null : $responseElements[0];

        if ($this->signingKey !== null) {
            Utils::insertSignature($this->signingKey, $this->certificates, $e, $issuer->nextSibling);
        }
        return $e;
    }
}
