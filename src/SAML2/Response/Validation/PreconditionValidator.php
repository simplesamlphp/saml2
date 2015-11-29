<?php

namespace SAML2\Response\Validation;

use SAML2\Configuration\Destination;
use SAML2\Response\Validation\ConstraintValidator\IsSuccessful;
use SAML2\Response\Validation\ConstraintValidator\DestinationMatches;

/**
 * Validates the preconditions that have to be met prior to processing of the response.
 */
class PreconditionValidator extends Validator
{
    public function __construct(Destination $destination)
    {
        // move to DI
        $this->addConstraintValidator(new IsSuccessful());
        $this->addConstraintValidator(
            new DestinationMatches($destination)
        );
    }
}
