<?php

class SAML2_Response_Validation_ConstraintValidator_DestinationMatches implements
    SAML2_Response_Validation_ConstraintValidator
{
    private $expectedDestination;

    public function __construct($destination)
    {
        $this->expectedDestination = $destination;
    }

    public function validate(SAML2_Response $response, SAML2_Response_Validation_Result $result)
    {
        $destination = $response->getDestination();
        if ($destination !== $this->expectedDestination) {
            $result->addError(sprintf(
                'Destination in response "%s" does not match the expected destination "%s"',
                $destination,
                $this->expectedDestination
            ));
        }
    }
}
