<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator;

use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationConstraintValidator;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\SAML2\XML\samlp\Response;

use function sprintf;

class SubjectConfirmationResponseToMatches implements SubjectConfirmationConstraintValidator
{
    /**
     * Constructor for SubjectConfirmationResponseToMatches
     *
     * @param \SimpleSAML\SAML2\XML\samlp\Response $response
     */
    public function __construct(
        private Response $response,
    ) {
    }


    /**
     * @param \SimpleSAML\SAML2\XML\saml\SubjectConfirmation $subjectConfirmation
     * @param \SimpleSAML\SAML2\Assertion\Validation\Result $result
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException if assertions are false
     */
    public function validate(SubjectConfirmation $subjectConfirmation, Result $result): void
    {
        $inResponseTo = $subjectConfirmation->getSubjectConfirmationData()?->getInResponseTo();

        if (
            $inResponseTo !== null
            && $this->getInResponseTo() !== null
            && !$inResponseTo->equals($this->getInResponseTo())
        ) {
            $result->addError(sprintf(
                'InResponseTo in SubjectConfirmationData ("%s") does not match the Response InResponseTo ("%s")',
                $inResponseTo,
                $this->getInResponseTo(),
            ));
        }
    }


    /**
     * @return string|null
     */
    private function getInResponseTo(): ?string
    {
        return $this->response->getInResponseTo()?->getValue();
    }
}
