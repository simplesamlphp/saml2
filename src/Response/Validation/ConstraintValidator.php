<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Response\Validation;

use SimpleSAML\SAML2\XML\samlp\Response;

interface ConstraintValidator
{
    /**
     * @param \SimpleSAML\SAML2\XML\samlp\Response $response
     * @param \SimpleSAML\SAML2\Response\Validation\Result $result
     */
    public function validate(Response $response, Result $result): void;
}
