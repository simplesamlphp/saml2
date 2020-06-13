<?php

declare(strict_types=1);

namespace SAML2\Response\Validation;

use SAML2\XML\samlp\Response;

class Validator
{
    /**
     * @var \SAML2\Response\Validation\ConstraintValidator[]
     */
    protected $constraints = [];


    /**
     * @param \SAML2\Response\Validation\ConstraintValidator $constraint
     * @return void
     */
    public function addConstraintValidator(ConstraintValidator $constraint): void
    {
        $this->constraints[] = $constraint;
    }


    /**
     * @param \SAML2\XML\samlp\Response $response
     * @return \SAML2\Response\Validation\Result
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
