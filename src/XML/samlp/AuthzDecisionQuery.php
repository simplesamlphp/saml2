<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooHighException;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooLowException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\saml\Action;
use SimpleSAML\SAML2\XML\saml\Evidence;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function version_compare;

/**
 * Class representing a SAML2 AuthzDecisionQuery
 *
 * @package simplesamlphp/saml2
 */
final class AuthzDecisionQuery extends AbstractSubjectQuery implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Constructor for SAML 2 AuthzDecisionQuery.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Subject $subject
     * @param string $resource
     * @param \SimpleSAML\SAML2\XML\saml\Action[] $action
     * @param \SimpleSAML\SAML2\XML\saml\Evidence $evidence
     * @param \SimpleSAML\SAML2\XML\saml\Issuer $issuer
     * @param string|null $id
     * @param string $version
     * @param \DateTimeImmutable $issueInstant
     * @param string|null $destination
     * @param string|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     */
    public function __construct(
        Subject $subject,
        DateTimeImmutable $issueInstant,
        protected string $resource,
        protected array $action,
        protected ?Evidence $evidence = null,
        ?Issuer $issuer = null,
        ?string $id = null,
        string $version = '2.0',
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null,
    ) {
        Assert::validURI($resource);
        Assert::maxCount($action, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($action, Action::class, SchemaViolationException::class);

        parent::__construct($subject, $issuer, $id, $version, $issueInstant, $destination, $consent, $extensions);
    }


    /**
     * Collect the value of the resource-property
     *
     * @return string
     */
    public function getResource(): string
    {
        return $this->resource;
    }


    /**
     * Collect the value of the action-property
     *
     * @return array
     */
    public function getAction(): array
    {
        return $this->action;
    }


    /**
     * Collect the value of the evidence-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\Evidence|null
     */
    public function getEvidence(): ?Evidence
    {
        return $this->evidence;
    }


    /**
     * Convert XML into an AuthzDecisionQuery
     *
     * @param \DOMElement $xml The XML element we should load
     *
     * @return static
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingElementException
     *   if one of the mandatory child-elements is missing
     * @throws \Exception if the authentication instant is not a valid timestamp.
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AuthzDecisionQuery', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AuthzDecisionQuery::NS, InvalidDOMElementException::class);

        $version = self::getAttribute($xml, 'Version');
        Assert::true(version_compare('2.0', $version, '<='), RequestVersionTooLowException::class);
        Assert::true(version_compare('2.0', $version, '>='), RequestVersionTooHighException::class);

        $id = self::getAttribute($xml, 'ID');
        Assert::validNCName($id); // Covers the empty string

        $destination = self::getOptionalAttribute($xml, 'Destination', null);
        $consent = self::getOptionalAttribute($xml, 'Consent', null);

        $issueInstant = self::getAttribute($xml, 'IssueInstant');
        // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
        $issueInstant = preg_replace('/([.][0-9]+Z)$/', 'Z', $issueInstant, 1);

        Assert::validDateTime($issueInstant, ProtocolViolationException::class);
        $issueInstant = new DateTimeImmutable($issueInstant);

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::countBetween($issuer, 0, 1);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount(
            $extensions,
            1,
            'Only one saml:Extensions element is allowed.',
            TooManyElementsException::class,
        );

        $subject = Subject::getChildrenOfClass($xml);
        Assert::notEmpty($subject, 'Missing subject in subject query.', MissingElementException::class);
        Assert::maxCount(
            $subject,
            1,
            'More than one <saml:Subject> in AuthzDecisionQuery',
            TooManyElementsException::class,
        );

        $action = Action::getChildrenOfClass($xml);
        Assert::minCount(
            $action,
            1,
            'Missing <saml:Action> in <saml:AuthzDecisionQuery>',
            MissingElementException::class,
        );

        $evidence = Evidence::getChildrenOfClass($xml);
        Assert::maxCount(
            $evidence,
            1,
            'Too many <saml:Evidence> in <saml:AuthzDecisionQuery>',
            TooManyElementsException::class,
        );

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.', TooManyElementsException::class);

        $request = new static(
            array_pop($subject),
            $issueInstant,
            self::getAttribute($xml, 'Resource'),
            $action,
            array_pop($evidence),
            array_pop($issuer),
            $id,
            $version,
            $destination,
            $consent,
            array_pop($extensions),
        );

        if (!empty($signature)) {
            $request->setSignature($signature[0]);
            $request->setXML($xml);
        }

        return $request;
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
        $e->setAttribute('Resource', $this->getResource());

        foreach ($this->getAction() as $action) {
            $action->toXML($e);
        }

        if ($this->getEvidence() !== null && !$this->getEvidence()->isEmptyElement()) {
            $this->getEvidence()->toXML($e);
        }

        return $e;
    }
}
