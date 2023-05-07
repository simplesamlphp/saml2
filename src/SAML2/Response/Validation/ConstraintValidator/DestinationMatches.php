<?php

declare(strict_types=1);

namespace SAML2\Response\Validation\ConstraintValidator;

use Exception;
use SAML2\Configuration\Destination;
use SAML2\Response;
use SAML2\Response\Validation\ConstraintValidator;
use SAML2\Response\Validation\Result;

use function sprintf;
use function strval;

final class DestinationMatches implements
    ConstraintValidator
{
    /**
     * DestinationMatches constructor.
     *
     * @param Destination $destination
     */
    public function __construct(
        private Destination $destination
    ) {
    }


    /**
     * @param Response $response
     * @param Result $result
     * @return void
     */
    public function validate(Response $response, Result $result): void
    {
        $destination = $response->getDestination();
        if ($destination === null) {
            throw new Exception('No destination set in the response.');
        }
        if (!$this->destination->equals(new Destination($destination))) {
            $result->addError(sprintf(
                'Destination in response "%s" does not match the expected destination "%s"',
                $destination,
                strval($this->destination)
            ));
        }
    }
}
