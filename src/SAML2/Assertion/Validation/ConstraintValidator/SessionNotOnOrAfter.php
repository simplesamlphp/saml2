<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator;

use DateInterval;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\Assertion\Validation\AssertionConstraintValidator;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Utils;

class SessionNotOnOrAfter implements AssertionConstraintValidator
{
    /**
     * @param \SimpleSAML\SAML2\XML\saml\Assertion $assertion
     * @param \SimpleSAML\SAML2\Assertion\Validation\Result $result
     */
    public function validate(Assertion $assertion, Result $result): void
    {
        $sessionNotOnOrAfterTimestamp = $assertion->getAuthnStatements()[0]->getSessionNotOnOrAfter();
        $clock = Utils::getContainer()->getClock();
        if (
            ($sessionNotOnOrAfterTimestamp !== null) &&
            ($sessionNotOnOrAfterTimestamp <= ($clock->now()->sub(new DateInterval('PT60S'))))
        ) {
            $result->addError(
                'Received an assertion with a session that has expired. Check clock synchronization on IdP and SP.',
            );
        }
    }
}
