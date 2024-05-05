<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\Decision;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use ValueError;

use function array_pop;
use function sprintf;

/**
 * Class representing a SAML2 AuthzDecisionStatement
 *
 * @package simplesamlphp/saml2
 */
final class AuthzDecisionStatement extends AbstractStatementType
{
    /**
     * Initialize an AuthzDecisionStatement.
     *
     * @param string $resource
     * @param \SimpleSAML\SAML2\XML\Decision $decision
     * @param \SimpleSAML\SAML2\XML\saml\Action[] $action
     * @param \SimpleSAML\SAML2\XML\saml\Evidence|null $evidence
     */
    public function __construct(
        protected string $resource,
        protected Decision $decision,
        protected array $action,
        protected ?Evidence $evidence = null,
    ) {
        Assert::validURI($resource);
        Assert::maxCount($action, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($action, Action::class, SchemaViolationException::class);
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
     * Collect the value of the decision-property
     *
     * @return \SimpleSAML\SAML2\XML\Decision
     */
    public function getDecision(): Decision
    {
        return $this->decision;
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
     * Convert XML into an AuthzDecisionStatement
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
        Assert::same($xml->localName, 'AuthzDecisionStatement', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AuthzDecisionStatement::NS, InvalidDOMElementException::class);

        $action = Action::getChildrenOfClass($xml);
        Assert::minCount(
            $action,
            1,
            'Missing <saml:Action> in <saml:AuthzDecisionStatement>',
            MissingElementException::class,
        );

        $evidence = Evidence::getChildrenOfClass($xml);
        Assert::maxCount(
            $evidence,
            1,
            'Too many <saml:Evidence> in <saml:AuthzDecisionStatement>',
            TooManyElementsException::class,
        );

        $decision = self::getAttribute($xml, 'Decision');
        try {
            $decision = Decision::from($decision);
        } catch (ValueError) {
            throw new ProtocolViolationException(sprintf('Unknown value \'%s\' for Decision attribute.', $decision));
        }

        return new static(
            self::getAttribute($xml, 'Resource'),
            $decision,
            $action,
            array_pop($evidence),
        );
    }


    /**
     * Convert this AuthzDecisionStatement to XML.
     *
     * @param \DOMElement|null $parent The element we should append this AuthzDecisionStatement to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $e->setAttribute('Resource', $this->getResource());
        $e->setAttribute('Decision', $this->getDecision()->value);

        foreach ($this->getAction() as $action) {
            $action->toXML($e);
        }

        if ($this->getEvidence() !== null && !$this->getEvidence()->isEmptyElement()) {
            $this->getEvidence()->toXML($e);
        }

        return $e;
    }
}
