<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Signature;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Signature\ValidatorChain;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\SAML2\Signature\MissingConfigurationException;

/**
 * @covers \SimpleSAML\SAML2\Signature\ValidatorChain
 * @package simplesamlphp/saml2
 */
final class ValidatorChainTest extends TestCase
{
    /** @var \SimpleSAML\SAML2\Signature\ValidatorChain */
    private static ValidatorChain $chain;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$chain = new ValidatorChain(new NullLogger(), []);
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
        self::$chain->hasValidSignature(new Response(new Status(new StatusCode())), new IdentityProvider([]));
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
            new Response(new Status(new StatusCode())),
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
            new Response(new Status(new StatusCode())),
            new IdentityProvider([]),
        );
        $this->assertFalse($validationResult, 'The validation result is not what is expected');
    }
}
