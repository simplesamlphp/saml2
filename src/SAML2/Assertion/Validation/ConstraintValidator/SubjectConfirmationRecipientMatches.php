<?php

declare(strict_types=1);

namespace SAML2\Assertion\Validation\ConstraintValidator;

use SAML2\Assertion\Validation\Result;
use SAML2\Assertion\Validation\SubjectConfirmationConstraintValidator;
use SAML2\Configuration\Destination;
use SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\Assert\Assert;

use function sprintf;
use function strval;

class SubjectConfirmationRecipientMatches implements
    SubjectConfirmationConstraintValidator
{
    /**
     * Constructor for SubjectConfirmationRecipientMatches
     * @param Destination $destination
     */
    public function __construct(
        private Destination $destination
    ) {
    }


    /**
     * @param SubjectConfirmation $subjectConfirmation
     * @param Result $result
     * @return void
     */
    public function validate(SubjectConfirmation $subjectConfirmation, Result $result): void
    {
        $data = $subjectConfirmation->getSubjectConfirmationData();
        Assert::notNull($data);

        /** @psalm-suppress PossiblyNullReference */
        $recipient = $data->getRecipient();
        if ($recipient && !$this->destination->equals(new Destination($recipient))) {
            $result->addError(sprintf(
                'Recipient in SubjectConfirmationData ("%s") does not match the current destination ("%s")',
                $recipient,
                strval($this->destination)
            ));
        }
    }
}
