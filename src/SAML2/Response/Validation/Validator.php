<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Response\Validation;

use SimpleSAML\SAML2\XML\samlp\Response;

class Validator
{
    /** @var \SimpleSAML\SAML2\Response\Validation\ConstraintValidator[] */
    protected array $constraints = [];


    /**
     * @param \SimpleSAML\SAML2\Response\Validation\ConstraintValidator $constraint
     */
    public function addConstraintValidator(ConstraintValidator $constraint): void
    {
        $this->constraints[] = $constraint;
    }


    /**
     * @param \SimpleSAML\SAML2\XML\samlp\Response $response
     * @return \SimpleSAML\SAML2\Response\Validation\Result
     */
    public function validate(Response $response): Result
    {
        $result = new Result();
        foreach ($this->constraints as $validator) {
            $validator->validate($response, $result);
        }

        return $result;
    }
}
