<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Signature;

use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\NullLogger;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Signature\MissingConfigurationException;
use SimpleSAML\SAML2\Signature\ValidatorChain;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;

/**
 * @covers \SimpleSAML\SAML2\Signature\ValidatorChain
 * @package simplesamlphp/saml2
 */
final class ValidatorChainTest extends TestCase
{
    /** @var \SimpleSAML\SAML2\Signature\ValidatorChain */
    private static ValidatorChain $chain;

    /** @var \Psr\Clock\ClockInterface */
    private static ClockInterface $clock;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$chain = new ValidatorChain(new NullLogger(), []);
        self::$clock = Utils::getContainer()->getClock();
    }


    /**
     * @group signature
     * @test
     */
    public function ifNoValidatorsCanValidateAnExceptionIsThrown(): void
    {
        self::$chain->appendValidator(new MockChainedValidator(false, true));
        self::$chain->appendValidator(new MockChainedValidator(false, true));

        $this->expectException(MissingConfigurationException::class);
        self::$chain->hasValidSignature(
            new Response(new Status(new StatusCode()), self::$clock->now()),
            new IdentityProvider([]),
        );
    }


    /**
     * @group signature
     * @test
     */
    public function allRegisteredValidatorsShouldBeTried(): void
    {
        self::$chain->appendValidator(new MockChainedValidator(false, true));
        self::$chain->appendValidator(new MockChainedValidator(false, true));
        self::$chain->appendValidator(new MockChainedValidator(true, false));

        $validationResult = self::$chain->hasValidSignature(
            new Response(new Status(new StatusCode()), self::$clock->now()),
            new IdentityProvider([]),
        );
        $this->assertFalse($validationResult, 'The validation result is not what is expected');
    }


    /**
     * @group signature
     * @test
     */
    public function itUsesTheResultOfTheFirstValidatorThatCanValidate(): void
    {
        self::$chain->appendValidator(new MockChainedValidator(false, true));
        self::$chain->appendValidator(new MockChainedValidator(true, false));
        self::$chain->appendValidator(new MockChainedValidator(false, true));

        $validationResult = self::$chain->hasValidSignature(
            new Response(new Status(new StatusCode()), self::$clock->now()),
            new IdentityProvider([]),
        );
        $this->assertFalse($validationResult, 'The validation result is not what is expected');
    }
}
