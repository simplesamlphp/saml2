<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooHighException;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooLowException;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\saml\Action;
use SimpleSAML\SAML2\XML\saml\Evidence;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\MissingElementException;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;
use SimpleSAML\XMLSchema\Exception\TooManyElementsException;
use SimpleSAML\XMLSchema\Type\IDValue;
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
     * @param \SimpleSAML\XMLSchema\Type\IDValue $id
     * @param \SimpleSAML\SAML2\XML\saml\Subject $subject
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $resource
     * @param \SimpleSAML\SAML2\XML\saml\Action[] $action
     * @param \SimpleSAML\SAML2\XML\saml\Evidence $evidence
     * @param \SimpleSAML\SAML2\XML\saml\Issuer $issuer
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue $issueInstant
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $destination
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     */
    public function __construct(
        IDVaLue $id,
        Subject $subject,
        SAMLDateTimeValue $issueInstant,
        protected SAMLAnyURIValue $resource,
        protected array $action,
        protected ?Evidence $evidence = null,
        ?Issuer $issuer = null,
        ?SAMLAnyURIValue $destination = null,
        ?SAMLAnyURIValue $consent = null,
        ?Extensions $extensions = null,
    ) {
        Assert::maxCount($action, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($action, Action::class, SchemaViolationException::class);

        parent::__construct($id, $subject, $issuer, $issueInstant, $destination, $consent, $extensions);
    }


    /**
     * Collect the value of the resource-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLAnyURIValue
     */
    public function getResource(): SAMLAnyURIValue
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
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingElementException
     *   if one of the mandatory child-elements is missing
     * @throws \Exception if the authentication instant is not a valid timestamp.
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AuthzDecisionQuery', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AuthzDecisionQuery::NS, InvalidDOMElementException::class);

        $version = self::getAttribute($xml, 'Version', SAMLStringValue::class);
        Assert::true(version_compare('2.0', $version->getValue(), '<='), RequestVersionTooLowException::class);
        Assert::true(version_compare('2.0', $version->getValue(), '>='), RequestVersionTooHighException::class);

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
            self::getAttribute($xml, 'ID', IDValue::class),
            array_pop($subject),
            self::getAttribute($xml, 'IssueInstant', SAMLDateTimeValue::class),
            self::getAttribute($xml, 'Resource', SAMLAnyURIValue::class),
            $action,
            array_pop($evidence),
            array_pop($issuer),
            self::getOptionalAttribute($xml, 'Destination', SAMLAnyURIValue::class, null),
            self::getOptionalAttribute($xml, 'Consent', SAMLAnyURIValue::class, null),
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
        $e->setAttribute('Resource', $this->getResource()->getValue());

        foreach ($this->getAction() as $action) {
            $action->toXML($e);
        }

        if ($this->getEvidence() !== null && !$this->getEvidence()->isEmptyElement()) {
            $this->getEvidence()->toXML($e);
        }

        return $e;
    }
}
