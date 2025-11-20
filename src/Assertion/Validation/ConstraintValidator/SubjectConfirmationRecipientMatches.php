<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator;

use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationConstraintValidator;
use SimpleSAML\SAML2\Configuration\Destination;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;

use function sprintf;

class SubjectConfirmationRecipientMatches implements SubjectConfirmationConstraintValidator
{
    /**
     * Constructor for SubjectConfirmationRecipientMatches
     * @param \SimpleSAML\SAML2\Configuration\Destination $destination
     */
    public function __construct(
        private Destination $destination,
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
        $recipient = $subjectConfirmation->getSubjectConfirmationData()?->getRecipient();

        if ($recipient !== null && !$recipient->equals((string)$this->destination)) {
            $result->addError(sprintf(
                'Recipient in SubjectConfirmationData ("%s") does not match the current destination ("%s")',
                $recipient,
                (string)$this->destination,
            ));
        }
    }
}
