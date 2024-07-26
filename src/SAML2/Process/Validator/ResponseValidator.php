<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Process\Validator;

use SimpleSAML\SAML2\{Binding, Metadata};
use SimpleSAML\SAML2\Process\ConstraintValidation\Response\{DestinationMatches, IsSuccessful};

class ResponseValidator implements ValidatorInterface
{
    use ValidatorTrait;

    /**
     * @param \SimpleSAML\SAML2\Metadata\IdentityProvider  The IdP-metadata
     * @param \SimpleSAML\SAML2\Metadata\ServiceProvider  The SP-metadata
     */
    private function __construct(
        protected ?Metadata\IdentityProvider $idpMetadata,
        protected Metadata\ServiceProvider $spMetadata,
    ) {
    }


    /**
     * @param \SimpleSAML\SAML2\Metadata\IdentityProvider|null $idpMetadata
     * @param \SimpleSAML\SAML2\Metadata\ServiceProvider $spMetadata
     * @param string $binding
     * @return \SimpleSAML\SAML2\Validator\ResponseValidator
     */
    public static function createResponseValidator(
        ?Metadata\IdentityProvider $idpMetadata,
        Metadata\ServiceProvider $spMetadata,
        Binding $binding,
    ): ResponseValidator {
        $validator = new self($idpMetadata, $spMetadata);
        $validator->addConstraintValidator(new DestinationMatches($spMetadata, $binding));
//        $validator->addConstraintValidator(new IsSuccesful());

        return $validator;
    }
}
