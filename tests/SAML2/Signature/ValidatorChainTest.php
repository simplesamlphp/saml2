<?php

declare(strict_types=1);

namespace SAML2\Signature;

use PHPUnit\Framework\TestCase;
use SAML2\Configuration\IdentityProvider;
use SAML2\Signature\ValidatorChain;
use SAML2\XML\samlp\Response;
use SAML2\XML\samlp\Status;
use SAML2\XML\samlp\StatusCode;
use SAML2\Signature\MissingConfigurationException;

/**
 * @covers \SAML2\Signature\ValidatorChain
 * @package simplesamlphp/saml2
 */
final class ValidatorChainTest extends TestCase
{
    /**
     * @var \SAML2\Signature\ValidatorChain
     */
    private $chain;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->chain = new ValidatorChain(new \Psr\Log\NullLogger(), []);
    }


    /**
     * @group signature
     * @test
     * @return void
     */
    public function if_no_validators_can_validate_an_exception_is_thrown(): void
    {
        $this->chain->appendValidator(new MockChainedValidator(false, true));
        $this->chain->appendValidator(new MockChainedValidator(false, true));

        $this->expectException(MissingConfigurationException::class);
        $this->chain->hasValidSignature(new Response(new Status(new StatusCode())), new IdentityProvider([]));
    }


    /**
     * @group signature
     * @test
     * @return void
     */
    public function all_registered_validators_should_be_tried(): void
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
     * @return void
     */
    public function it_uses_the_result_of_the_first_validator_that_can_validate(): void
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
