<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use SimpleSAML\SAML2\Assertion;
use SimpleSAML\SAML2\Assertion\Exception\InvalidAssertionException;
use SimpleSAML\SAML2\Assertion\Processor;
use SimpleSAML\SAML2\Assertion\Transformer\Transformer;
use SimpleSAML\SAML2\Assertion\Validation\AssertionValidator;
use SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationValidator;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\EncryptedAssertion;
use SimpleSAML\SAML2\Signature\Validator;
use SimpleSAML\SAML2\Utilities\ArrayCollection;
use StdClass;

/**
 * @runTestsInSeparateProcesses
 */
class ProcessorTest extends MockeryTestCase
{
    /** @var \SimpleSAML\SAML2\Assertion\Processor */
    private static Processor $processor;

    /** @var \Mockery\MockInterface */
    private static MockInterface $decrypter;

    public static function setUpBeforeClass(): void
    {
        self::$decrypter = Mockery::mock(Decrypter::class);
        $validator = Mockery::mock(Validator::class);
        $assertionValidator = Mockery::mock(AssertionValidator::class);
        $subjectConfirmationValidator = Mockery::mock(SubjectConfirmationValidator::class);
        $transformer = Mockery::mock(Transformer::class);
        $identityProvider = new IdentityProvider([]);
        $logger = Mockery::mock(LoggerInterface::class);

        self::$processor = new Processor(
            self::$decrypter,
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
            self::$decrypter
                ->shouldReceive('decrypt')
                ->andReturn(new Assertion());

            $collection = new ArrayCollection($assertions);
            $result = self::$processor->decryptAssertions($collection);
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
        self::$processor->decryptAssertions(new ArrayCollection([new stdClass()]));
    }
}
