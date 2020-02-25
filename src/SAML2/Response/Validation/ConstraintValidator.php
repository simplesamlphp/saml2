<?php

declare(strict_types=1);

namespace SAML2\Response\Validation;

use SAML2\XML\samlp\Response;

interface ConstraintValidator
{
    /**
     * @param \SAML2\XML\samlp\Response $response
     * @param Result $result
     * @return void
     */
    public function validate(Response $response, Result $result): void;
}
