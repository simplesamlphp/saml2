<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationConstraintValidator;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\SAML2\XML\samlp\Response;

class SubjectConfirmationResponseToMatches implements
    SubjectConfirmationConstraintValidator
{
    /** @var Response */
    private $response;


    /**
     * Constructor for SubjectConfirmationResponseToMatches
     * @param \SimpleSAML\SAML2\XML\samlp\Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }


    /**
     * @param \SimpleSAML\SAML2\XML\saml\SubjectConfirmation $subjectConfirmation
     * @param \SimpleSAML\SAML2\Assertion\Validation\Result $result
     * @return void
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException if assertions are false
     */
    public function validate(SubjectConfirmation $subjectConfirmation, Result $result): void
    {
        $data = $subjectConfirmation->getSubjectConfirmationData();
        Assert::notNull($data);

        /** @psalm-suppress PossiblyNullReference */
        $inResponseTo = $data->getInResponseTo();
        if ($inResponseTo && ($this->getInResponseTo() !== false) && ($this->getInResponseTo() !== $inResponseTo)) {
            $result->addError(sprintf(
                'InResponseTo in SubjectConfirmationData ("%s") does not match the Response InResponseTo ("%s")',
                $inResponseTo,
                strval($this->getInResponseTo())
            ));
        }
    }


    /**
     * @return string|bool
     */
    private function getInResponseTo()
    {
        $inResponseTo = $this->response->getInResponseTo();
        if ($inResponseTo === null) {
            return false;
        }

        return $inResponseTo;
    }
}
