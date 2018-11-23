<?php

namespace SAML2\Utilities;

<<<<<<< HEAD
class FileTest extends \PHPUnit\Framework\TestCase
=======
use SAML2\Utilities\File;
use SAML2\Exception\RuntimeException;

class FileTest extends \PHPUnit_Framework_TestCase
>>>>>>> Remove PSR-0 autoloader
{
    /**
     * @group utilities
     * @test
     */
    public function when_loading_a_non_existant_file_an_exception_is_thrown()
    {
<<<<<<< HEAD
        $this->expectException(\SAML2\Exception\RuntimeException::class, 'File "/foo/bar/baz/quux" does not exist or is not readable');
=======
        $this->expectException(RuntimeException::class, 'File "/foo/bar/baz/quux" does not exist or is not readable');
>>>>>>> Remove PSR-0 autoloader
        File::getFileContents('/foo/bar/baz/quux');
    }

    /**
     * @group utilities
     * @test
     */
    public function an_existing_readable_file_can_be_loaded()
    {
        $contents = File::getFileContents(__DIR__ . '/File/can_be_loaded.txt');

        $this->assertEquals("Yes we can!\n", $contents, 'The contents of the loaded file differ from what was expected');
    }
}
