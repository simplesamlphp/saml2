<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Response\Validation\ConstraintValidator;

use Exception;
use SimpleSAML\SAML2\Configuration\Destination;
use SimpleSAML\SAML2\Response\Validation\ConstraintValidator;
use SimpleSAML\SAML2\Response\Validation\Result;
use SimpleSAML\SAML2\XML\samlp\Response;

use function sprintf;
use function strval;

final class DestinationMatches implements ConstraintValidator
{
    /**
     * DestinationMatches constructor.
     *
     * @param \SimpleSAML\SAML2\Configuration\Destination $expectedDestination
     */
    public function __construct(
        private Destination $expectedDestination,
    ) {
    }


    /**
     * @param \SimpleSAML\SAML2\XML\samlp\Response $response
     * @param \SimpleSAML\SAML2\Response\Validation\Result $result
     */
    public function validate(Response $response, Result $result): void
    {
        $destination = $response->getDestination();
        if ($destination === null) {
            throw new Exception('No destination set in the response.');
        }
        if (!$this->expectedDestination->equals(new Destination($destination))) {
            $result->addError(sprintf(
                'Destination in response "%s" does not match the expected destination "%s"',
                $destination,
                strval($this->expectedDestination),
            ));
        }
    }
}
