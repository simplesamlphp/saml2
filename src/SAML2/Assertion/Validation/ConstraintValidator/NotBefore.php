<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator;

use DateInterval;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\Assertion\Validation\AssertionConstraintValidator;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Utils;

class NotBefore implements AssertionConstraintValidator
{
    /**
     * @param \SimpleSAML\SAML2\XML\saml\Assertion $assertion
     * @param \SimpleSAML\SAML2\Assertion\Validation\Result $result
     */
    public function validate(Assertion $assertion, Result $result): void
    {
        $conditions = $assertion->getConditions();
        if ($conditions !== null) {
            $notBeforeTimestamp = $conditions->getNotBefore();
            $clock = Utils::getContainer()->getClock();
            if (
                ($notBeforeTimestamp !== null) &&
                ($notBeforeTimestamp > ($clock->now()->add(new DateInterval('PT60S'))))
            ) {
                $result->addError(
                    'Received an assertion that is valid in the future. Check clock synchronization on IdP and SP.',
                );
            }
        }
    }
}
