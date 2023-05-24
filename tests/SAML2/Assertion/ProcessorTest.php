<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion;

use Mockery;
use Mockery\MockInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use SimpleSAML\SAML2\Assertion\Decrypter;
use SimpleSAML\SAML2\Assertion\Exception\InvalidAssertionException;
use SimpleSAML\SAML2\Assertion\Processor;
use SimpleSAML\SAML2\Assertion\Transformer\TransformerInterface;
use SimpleSAML\SAML2\Assertion\Validation\AssertionValidator;
use SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationValidator;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Signature\Validator;
use SimpleSAML\SAML2\Utilities\ArrayCollection;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\EncryptedAssertion;
use SimpleSAML\XML\DOMDocumentFactory;
use stdClass;

/**
 * @covers \SimpleSAML\SAML2\Assertion\Processor
 * @package simplesamlphp/saml2
 * @runTestsInSeparateProcesses
 */
final class ProcessorTest extends MockeryTestCase
{
    /**
     * @var \SimpleSAML\SAML2\Assertion\Processor
     */
    private static Processor $processor;

    /**
     * @var m\MockInterface&Decrypter
     */
    private static MockInterface $decrypter;


    public static function setUpBeforeClass(): void
    {
        self::$decrypter = Mockery::mock(Decrypter::class);
        $validator = Mockery::mock(Validator::class);
        $assertionValidator = Mockery::mock(AssertionValidator::class);
        $subjectConfirmationValidator = Mockery::mock(SubjectConfirmationValidator::class);
        $transformer = Mockery::mock(TransformerInterface::class);
        $identityProvider = new IdentityProvider([]);
        $logger = Mockery::mock(LoggerInterface::class);

        self::$processor = new Processor(
            self::$decrypter,
            $validator,
            $assertionValidator,
            $subjectConfirmationValidator,
            $transformer,
            $identityProvider,
            $logger,
        );
    }


    /**
     * @test
     */
    public function processorCorrectlyEncryptsAssertions(): void
    {
        $encryptedAssertion = EncryptedAssertion::fromXML(
            DOMDocumentFactory::fromFile(
                dirname(__FILE__, 3) . '/resources/xml/saml_EncryptedAssertion.xml'
            )->documentElement
        );
        $assertion = Assertion::fromXML(
            DOMDocumentFactory::fromFile(
                dirname(__FILE__, 3) . '/resources/xml/saml_Assertion.xml'
            )->documentElement
        );

        $testData = [
            [$assertion],
            [$encryptedAssertion],
            [$assertion, $encryptedAssertion, $assertion],
            [$encryptedAssertion, $encryptedAssertion, $encryptedAssertion],
        ];

        foreach ($testData as $assertions) {
            self::$decrypter
                ->shouldReceive('decrypt')
                ->andReturn($assertion);

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
    public function unsuportedAssertionsAreRejected(): void
    {
        $this->expectException(InvalidAssertionException::class);
        $this->expectExceptionMessage('The assertion must be of type: EncryptedAssertion or Assertion');
        self::$processor->decryptAssertions(new ArrayCollection([new stdClass()]));
    }
}
