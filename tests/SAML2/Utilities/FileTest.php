<?php

declare(strict_types=1);

namespace SAML2\Utilities;

use PHPUnit\Framework\Attributes\Test;
use SAML2\Utilities\File;
use SAML2\Exception\RuntimeException;

class FileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group utilities
     * @return void
     */
    #[Test]
    public function whenLoadingNonexistantFileAnExceptionIsThrown(): void
    {
        $this->expectException(RuntimeException::class, 'File "/foo/bar/baz/quux" does not exist or is not readable');
        File::getFileContents('/foo/bar/baz/quux');
    }

    /**
     * @group utilities
     * @return void
     */
    #[Test]
    public function anExistingReadableFileCanBeLoaded(): void
    {
        $contents = File::getFileContents(__DIR__ . '/File/can_be_loaded.txt');

        $this->assertEquals("Yes we can!\n", $contents, 'The contents of the loaded file differ from what was expected');
    }
}
