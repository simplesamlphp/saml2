<?php

namespace SAML2\Signature;

use SAML2\Configuration\IdentityProvider;
use SAML2\Response;

class ValidatorChainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \SAML2\Signature\ValidatorChain
     */
    private $chain;

    public function setUp()
    {
        $this->chain = new ValidatorChain(new \Psr\Log\NullLogger(), array());
    }

    /**
     * @group signature
     *
     * @test
     * @expectedException \SAML2\Signature\MissingConfigurationException
     */
    public function if_no_validators_can_validate_an_exception_is_thrown()
    {
        $this->chain->appendValidator(new MockChainedValidator(false, true));
        $this->chain->appendValidator(new MockChainedValidator(false, true));

        $this->chain->hasValidSignature(new Response(), new IdentityProvider(array()));
    }

    /**
     * @group signature
     *
     * @test
     */
    public function all_registered_validators_should_be_tried()
    {
        $this->chain->appendValidator(new MockChainedValidator(false, true));
        $this->chain->appendValidator(new MockChainedValidator(false, true));
        $this->chain->appendValidator(new MockChainedValidator(true, false));

        $validationResult = $this->chain->hasValidSignature(
            new Response(),
            new IdentityProvider(array())
        );
        $this->assertFalse($validationResult, 'The validation result is not what is expected');
    }

    /**
     * @group signature
     *
     * @test
     */
    public function it_uses_the_result_of_the_first_validator_that_can_validate()
    {
        $this->chain->appendValidator(new MockChainedValidator(false, true));
        $this->chain->appendValidator(new MockChainedValidator(true, false));
        $this->chain->appendValidator(new MockChainedValidator(false, true));

        $validationResult = $this->chain->hasValidSignature(
            new Response(),
            new IdentityProvider(array())
        );
        $this->assertFalse($validationResult, 'The validation result is not what is expected');
    }
}
