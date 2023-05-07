<?php

declare(strict_types=1);

namespace SAML2\Assertion;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use SAML2\Assertion;
use SAML2\Assertion\Exception\InvalidAssertionException;
use SAML2\Assertion\Transformer\Transformer;
use SAML2\Assertion\Validation\AssertionValidator;
use SAML2\Assertion\Validation\SubjectConfirmationValidator;
use SAML2\Configuration\IdentityProvider;
use SAML2\EncryptedAssertion;
use SAML2\Signature\Validator;
use SAML2\Utilities\ArrayCollection;
use StdClass;

/**
 * @runTestsInSeparateProcesses
 */
class ProcessorTest extends MockeryTestCase
{
    /**
     * @var Processor
     */
    private Processor $processor;

    /**
     * @var \Mockery\MockInterface
     */
    private MockInterface $decrypter;

    protected function setUp(): void
    {
        $this->decrypter = Mockery::mock(Decrypter::class);
        $validator = Mockery::mock(Validator::class);
        $assertionValidator = Mockery::mock(AssertionValidator::class);
        $subjectConfirmationValidator = Mockery::mock(SubjectConfirmationValidator::class);
        $transformer = Mockery::mock(Transformer::class);
        $identityProvider = new IdentityProvider([]);
        $logger = Mockery::mock(LoggerInterface::class);

        $this->processor = new Processor(
            $this->decrypter,
            $validator,
            $assertionValidator,
            $subjectConfirmationValidator,
            $transformer,
            $identityProvider,
            $logger
        );
    }

    /**
     * @test
     */
    public function processor_correctly_encrypts_assertions(): void
    {
        $testData = [
            [new Assertion()],
            [new EncryptedAssertion()],
            [new Assertion(), new EncryptedAssertion(), new Assertion()],
            [new EncryptedAssertion(), new EncryptedAssertion(), new EncryptedAssertion()],
        ];

        foreach ($testData as $assertions) {
            $this->decrypter
                ->shouldReceive('decrypt')
                ->andReturn(new Assertion());

            $collection = new ArrayCollection($assertions);
            $result = $this->processor->decryptAssertions($collection);
            self::assertInstanceOf(ArrayCollection::class, $result);
            foreach ($result as $assertion) {
                self::assertInstanceOf(Assertion::class, $assertion);
            }
        }
    }

    /**
     * @test
     */
    public function unsuported_assertions_are_rejected(): void
    {
        $this->expectException(InvalidAssertionException::class);
        $this->expectExceptionMessage('The assertion must be of type: EncryptedAssertion or Assertion');
        $this->processor->decryptAssertions(new ArrayCollection([new stdClass()]));
    }
}
