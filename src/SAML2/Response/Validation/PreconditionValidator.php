<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Response\Validation;

use SimpleSAML\SAML2\Configuration\Destination;
use SimpleSAML\SAML2\Response\Validation\ConstraintValidator\DestinationMatches;
use SimpleSAML\SAML2\Response\Validation\ConstraintValidator\IsSuccessful;

/**
 * Validates the preconditions that have to be met prior to processing of the response.
 */
class PreconditionValidator extends Validator
{
    /**
     * Constructor for PreconditionValidator
     *
     * @param \SimpleSAML\SAML2\Configuration\Destination $destination
     */
    public function __construct(Destination $destination)
    {
        // move to DI
        $this->addConstraintValidator(new IsSuccessful());
        $this->addConstraintValidator(
            new DestinationMatches($destination)
        );
    }
}
