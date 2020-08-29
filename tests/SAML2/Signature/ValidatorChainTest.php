<?php

declare(strict_types=1);

namespace SAML2\Signature;

use PHPUnit\Framework\TestCase;
use SimpleSAMLSAML2\Configuration\IdentityProvider;
use SimpleSAMLSAML2\Signature\ValidatorChain;
use SimpleSAMLSAML2\XML\samlp\Response;
use SimpleSAMLSAML2\XML\samlp\Status;
use SimpleSAMLSAML2\XML\samlp\StatusCode;
use SimpleSAMLSAML2\Signature\MissingConfigurationException;

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
     * @return void
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
     * @return void
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
