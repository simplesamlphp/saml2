<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Response\Validation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Response\Validation\Result;

/**
 * @package simplesamlphp/saml2
 */
#[CoversClass(Result::class)]
final class ResultTest extends TestCase
{
    /**
     */
    #[Group('response-validation')]
    public function testAddedErrorsCanBeRetrieved(): void
    {
        $error = 'This would be an error message';
        $result = new Result();

        $result->addError($error);
        $errors = $result->getErrors();

        $this->assertCount(1, $errors);
        $this->assertEquals($error, $errors[0]);
    }


    /**
     */
    #[Group('response-validation')]
    public function testTheResultCorrectlyReportsWhetherOrNotItIsValid(): void
    {
        $result = new Result();

        $this->assertTrue($result->isValid());
        $this->assertCount(0, $result->getErrors());

        $result->addError('Oh noooos!');

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }
}
