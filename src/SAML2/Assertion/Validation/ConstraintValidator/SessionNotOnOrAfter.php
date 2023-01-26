<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator;

use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\Assertion\Validation\AssertionConstraintValidator;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Utilities\Temporal;

class SessionNotOnOrAfter implements AssertionConstraintValidator
{
    /**
     * @param \SimpleSAML\SAML2\XML\saml\Assertion $assertion
     * @param \SimpleSAML\SAML2\Assertion\Validation\Result $result
     */
    public function validate(Assertion $assertion, Result $result): void
    {
        $sessionNotOnOrAfterTimestamp = $assertion->getAuthnStatements()[0]->getSessionNotOnOrAfter();
        $currentTime = Temporal::getTime();
        if (($sessionNotOnOrAfterTimestamp !== null) && ($sessionNotOnOrAfterTimestamp <= ($currentTime - 60))) {
            $result->addError(
                'Received an assertion with a session that has expired. Check clock synchronization on IdP and SP.',
            );
        }
    }
}
