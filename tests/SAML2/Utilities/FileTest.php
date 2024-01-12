<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Utilities;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\RuntimeException;
use SimpleSAML\SAML2\Utilities\File;

/**
 * @covers \SimpleSAML\SAML2\Utilities\File
 * @package simplesamlphp/saml2
 */
final class FileTest extends TestCase
{
    /**
     * @group utilities
     * @test
     */
    public function whenLoadingANonExistantFileAnExceptionIsThrown(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File "/foo/bar/baz/quux" does not exist or is not readable');
        File::getFileContents('/foo/bar/baz/quux');
    }

    /**
     * @group utilities
     * @test
     */
    public function anExistingReadableFileCanBeLoaded(): void
    {
        $contents = File::getFileContents(__DIR__ . '/File/can_be_loaded.txt');

        $this->assertEquals(
            "Yes we can!\n",
            $contents,
            'The contents of the loaded file differ from what was expected',
        );
    }
}
