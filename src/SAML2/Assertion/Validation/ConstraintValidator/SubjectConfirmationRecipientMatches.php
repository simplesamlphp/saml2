<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationConstraintValidator;
use SimpleSAML\SAML2\Configuration\Destination;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;

use function sprintf;
use function strval;

class SubjectConfirmationRecipientMatches implements
    SubjectConfirmationConstraintValidator
{
    /**
     * @var \SimpleSAML\SAML2\Configuration\Destination
     */
    private Destination $destination;


    /**
     * Constructor for SubjectConfirmationRecipientMatches
     * @param \SimpleSAML\SAML2\Configuration\Destination $destination
     */
    public function __construct(Destination $destination)
    {
        $this->destination = $destination;
    }


    /**
     * @param \SimpleSAML\SAML2\XML\saml\SubjectConfirmation $subjectConfirmation
     * @param \SimpleSAML\SAML2\Assertion\Validation\Result $result
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException if assertions are false
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
