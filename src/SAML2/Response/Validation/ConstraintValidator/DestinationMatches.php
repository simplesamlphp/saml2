<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Response\Validation\ConstraintValidator;

use Exception;
use SimpleSAML\SAML2\Configuration\Destination;
use SimpleSAML\SAML2\Response;
use SimpleSAML\SAML2\Response\Validation\ConstraintValidator;
use SimpleSAML\SAML2\Response\Validation\Result;

use function sprintf;
use function strval;

final class DestinationMatches implements
    ConstraintValidator
{
    /**
     * DestinationMatches constructor.
     *
     * @param \SimpleSAML\SAML2\Configuration\Destination $destination
     */
    public function __construct(
        private Destination $destination
    ) {
    }


    /**
     * @param \SimpleSAML\SAML2\Response $response
     * @param \SimpleSAML\SAML2\Response\Validation\Result $result
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
