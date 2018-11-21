<?php

declare(strict_types=1);

namespace SAML2\Response\Validation;

use SAML2\Response;

interface ConstraintValidator
{
    public function validate(Response $response, Result $result);
}
