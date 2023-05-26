<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator;

use Beste\Clock;
use DateInterval;
use SimpleSAML\SAML2\Assertion\Validation\AssertionConstraintValidator;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\Assertion;

class NotOnOrAfter implements AssertionConstraintValidator
{
    /** @var \Beste\Clock */
    private static Clock $clock;


    /**
     */
    public function __construct()
    {
        self::$clock = Utils::getContainer()->getClock();
    }


    /**
     * @param \SimpleSAML\SAML2\XML\saml\Assertion $assertion
     * @param \SimpleSAML\SAML2\Assertion\Validation\Result $result
     */
    public function validate(Assertion $assertion, Result $result): void
    {
        $conditions = $assertion->getConditions();
        if ($conditions !== null) {
            $notValidOnOrAfterTimestamp = $conditions->getNotOnOrAfter();
            $currentTime = self::$clock->now();
            if (
                ($notValidOnOrAfterTimestamp !== null) &&
                ($notValidOnOrAfterTimestamp <= ($currentTime->sub(new DateInterval('PT60S'))))
            ) {
                $result->addError(
                    'Received an assertion that has expired. Check clock synchronization on IdP and SP.',
                );
            }
        }
    }
}
