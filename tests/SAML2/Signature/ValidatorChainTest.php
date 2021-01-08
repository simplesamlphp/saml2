<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Signature;

use PHPUnit\Framework\TestCase;
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
    private ValidatorChain $chain;


    /**
     */
    public function setUp(): void
    {
        $this->chain = new ValidatorChain(new \Psr\Log\NullLogger(), []);
    }


    /**
     * @group signature
     * @test
     */
    public function ifNoValidatorsCanValidateAnExceptionIsThrown(): void
    {
        $this->chain->appendValidator(new MockChainedValidator(false, true));
        $this->chain->appendValidator(new MockChainedValidator(false, true));

        $this->expectException(MissingConfigurationException::class);
        $this->chain->hasValidSignature(new Response(new Status(new StatusCode())), new IdentityProvider([]));
    }


    /**
     * @group signature
     * @test
     */
    public function allRegisteredValidatorsShouldBeTried(): void
    {
        $this->chain->appendValidator(new MockChainedValidator(false, true));
        $this->chain->appendValidator(new MockChainedValidator(false, true));
        $this->chain->appendValidator(new MockChainedValidator(true, false));

        $validationResult = $this->chain->hasValidSignature(
            new Response(new Status(new StatusCode())),
            new IdentityProvider([])
        );
        $this->assertFalse($validationResult, 'The validation result is not what is expected');
    }


    /**
     * @group signature
     * @test
     */
    public function itUsesTheResultOfTheFirstValidatorThatCanValidate(): void
    {
        $this->chain->appendValidator(new MockChainedValidator(false, true));
        $this->chain->appendValidator(new MockChainedValidator(true, false));
        $this->chain->appendValidator(new MockChainedValidator(false, true));

        $validationResult = $this->chain->hasValidSignature(
            new Response(new Status(new StatusCode())),
            new IdentityProvider([])
        );
        $this->assertFalse($validationResult, 'The validation result is not what is expected');
    }
}
