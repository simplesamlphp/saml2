<?php

declare(strict_types=1);

namespace SAML2\Response\Validation;

use PHPUnit\Framework\TestCase;
use SimpleSAMLSAML2\Response\Validation\Result;

/**
 * @covers \SAML2\Respose\Validation\Result
 * @package simplesamlphp/saml2
 */
final class ResultTest extends TestCase
{
    /**
     * @group response-validation
     * @test
     * @return void
     */
    public function addedErrorsCanBeRetrieved(): void
    {
        $error = 'This would be an error message';
        $result = new Result();

        $result->addError($error);
        $errors = $result->getErrors();

        $this->assertCount(1, $errors);
        $this->assertEquals($error, $errors[0]);
    }


    /**
     * @group response-validation
     * @test
     * @return void
     */
    public function theResultCorrectlyReportsWhetherOrNotItIsValid(): void
    {
        $result = new Result();

        $this->assertTrue($result->isValid());
        $this->assertCount(0, $result->getErrors());

        $result->addError('Oh noooos!');

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }
}
