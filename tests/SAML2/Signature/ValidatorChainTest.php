<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Signature;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\NullLogger;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Signature\{MissingConfigurationException, ValidatorChain};
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLDateTimeValue};
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\samlp\{Response, Status, StatusCode};
use SimpleSAML\XML\Type\IDValue;

/**
 * @package simplesamlphp/saml2
 */
#[CoversClass(ValidatorChain::class)]
final class ValidatorChainTest extends TestCase
{
    /** @var \SimpleSAML\SAML2\XML\samlp\Response */
    private static Response $response;

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
        self::$response = new Response(
            id: IDValue::fromString('abc123'),
            status: new Status(
                new StatusCode(
                    SAMLAnyURIValue::fromString(C::STATUS_SUCCESS),
                ),
            ),
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
        );
    }


    /**
     */
    #[Group('signature')]
    public function testIfNoValidatorsCanValidateAnExceptionIsThrown(): void
    {
        self::$chain->appendValidator(new MockChainedValidator(false, true));
        self::$chain->appendValidator(new MockChainedValidator(false, true));

        $this->expectException(MissingConfigurationException::class);
        self::$chain->hasValidSignature(
            self::$response,
            new IdentityProvider([]),
        );
    }


    /**
     */
    #[Group('signature')]
    public function testAllRegisteredValidatorsShouldBeTried(): void
    {
        self::$chain->appendValidator(new MockChainedValidator(false, true));
        self::$chain->appendValidator(new MockChainedValidator(false, true));
        self::$chain->appendValidator(new MockChainedValidator(true, false));

        $validationResult = self::$chain->hasValidSignature(
            self::$response,
            new IdentityProvider([]),
        );
        $this->assertFalse($validationResult, 'The validation result is not what is expected');
    }


    /**
     */
    #[Group('signature')]
    public function testItUsesTheResultOfTheFirstValidatorThatCanValidate(): void
    {
        self::$chain->appendValidator(new MockChainedValidator(false, true));
        self::$chain->appendValidator(new MockChainedValidator(true, false));
        self::$chain->appendValidator(new MockChainedValidator(false, true));

        $validationResult = self::$chain->hasValidSignature(
            self::$response,
            new IdentityProvider([]),
        );
        $this->assertFalse($validationResult, 'The validation result is not what is expected');
    }
}
