<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator;

use DateInterval;
use SimpleSAML\SAML2\Assertion\Validation\AssertionConstraintValidator;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\Assertion;

class NotOnOrAfter implements AssertionConstraintValidator
{
    /**
     * @param \SimpleSAML\SAML2\XML\saml\Assertion $assertion
     * @param \SimpleSAML\SAML2\Assertion\Validation\Result $result
     */
    public function validate(Assertion $assertion, Result $result): void
    {
        $notOnOrAfter = $assertion->getConditions()?->getNotOnOrAfter()?->toDateTime();
        $clock = Utils::getContainer()->getClock();

        if (($notOnOrAfter !== null) && ($notOnOrAfter <= ($clock->now()->sub(new DateInterval('PT60S'))))) {
            $result->addError(
                'Received an assertion that has expired. Check clock synchronization on IdP and SP.',
            );
        }
    }
}
