<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator;

use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SessionNotOnOrAfter;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\ControlledTimeTest;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\AuthnContext;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnStatement;
use SimpleSAML\SAML2\XML\saml\Issuer;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 *
 * @covers \SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SessionNotOnOrAfter
 * @package simplesamlphp/saml2
 *
 * @runTestsInSeparateProcesses
 */
final class SessionNotOnOrAfterTest extends ControlledTimeTest
{
    /**
     * @var \SAML2\XML\saml\Issuer
     */
    private $issuer;


    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        // Create an Issuer
        $this->issuer = new Issuer('testIssuer');
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function timestampInThePastBeforeGraceperiodIsNotValid(): void
    {
        // Create the statements
        $authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef('someAuthnContext'),
                null,
                null
            ),
            $this->currentTime,
            $this->currentTime - 60
        );

        // Create an assertion
        $assertion = new Assertion($this->issuer, null, null, null, null, [$authnStatement]);

        $validator = new SessionNotOnOrAfter();
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function timeWithinGraceperiodIsValid(): void
    {
        // Create the statements
        $authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef('someAuthnContext'),
                null,
                null
            ),
            $this->currentTime,
            $this->currentTime - 59
        );

        // Create an assertion
        $assertion = new Assertion($this->issuer, null, null, null, null, [$authnStatement]);

        $validator = new SessionNotOnOrAfter();
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function currentTimeIsValid(): void
    {
        // Create the statements
        $authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef('someAuthnContext'),
                null,
                null
            ),
            $this->currentTime,
            $this->currentTime
        );

        // Create an assertion
        $assertion = new Assertion($this->issuer, null, null, null, null, [$authnStatement]);

        $validator = new SessionNotOnOrAfter();
        $result    = new Result();

        $validator->validate($assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
