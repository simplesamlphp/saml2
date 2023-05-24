<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Signature;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Signature\ValidatorChain;
use SimpleSAML\SAML2\Response;
use SimpleSAML\SAML2\Signature\MissingConfigurationException;

class ValidatorChainTest extends TestCase
{
    /** @var \SimpleSAML\SAML2\Signature\ValidatorChain */
    private static ValidatorChain $chain;


    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::$chain = new ValidatorChain(new NullLogger(), []);
    }


    /**
     * @group signature
     * @test
     * @return void
     */
    public function ifNoValidatorsCanValidateAnExceptionIsThrown(): void
    {
        self::$chain->appendValidator(new MockChainedValidator(false, true));
        self::$chain->appendValidator(new MockChainedValidator(false, true));

        $this->expectException(MissingConfigurationException::class);
        self::$chain->hasValidSignature(new Response(), new IdentityProvider([]));
    }


    /**
     * @group signature
     * @test
     * @return void
     */
    public function allRegisteredValidatorsShouldBeTried(): void
    {
        self::$chain->appendValidator(new MockChainedValidator(false, true));
        self::$chain->appendValidator(new MockChainedValidator(false, true));
        self::$chain->appendValidator(new MockChainedValidator(true, false));

        $validationResult = self::$chain->hasValidSignature(
            new Response(),
            new IdentityProvider([])
        );
        $this->assertFalse($validationResult, 'The validation result is not what is expected');
    }


    /**
     * @group signature
     * @test
     * @return void
     */
    public function itUsesTheResultOfTheFirstValidatorThatCanValidate(): void
    {
        self::$chain->appendValidator(new MockChainedValidator(false, true));
        self::$chain->appendValidator(new MockChainedValidator(true, false));
        self::$chain->appendValidator(new MockChainedValidator(false, true));

        $validationResult = self::$chain->hasValidSignature(
            new Response(),
            new IdentityProvider([])
        );
        $this->assertFalse($validationResult, 'The validation result is not what is expected');
    }
}
