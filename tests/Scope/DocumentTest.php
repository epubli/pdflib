<?php

namespace Epubli\Pdf\PdfLib\Scope;

use Epubli\Pdf\PdfLib\Exception;
use Epubli\Pdf\PdfLib\File\Image;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    const PATH_TO_MINIMAL_PDF = '../data/minimal.pdf';
    const PATH_TO_CORRUPT_PDF = '../data/corrupt.pdf';
    const PATH_TO_IMAGE = '../data/image.png';

    public function test__destruct()
    {

    }

    public function testCreate()
    {

    }

    public function testClose()
    {

    }

    public function testFinish()
    {

    }

    /**
     * @expectedException \Epubli\Pdf\PdfLib\Exception
     * @expectedExceptionMessage Cannot load image
     * @throws Exception
     */
    public function testLoadImageWithInvalidInputThrows()
    {
        $wrapper = new RootObject();
        $doc = $wrapper->getDocument();
        $doc->loadImage(__DIR__ . DIRECTORY_SEPARATOR . self::PATH_TO_CORRUPT_PDF);
    }

    /**
     * @throws Exception
     */
    public function testLoadImage()
    {
        $wrapper = new RootObject();
        $doc = $wrapper->getDocument();
        $image = $doc->loadImage(__DIR__ . DIRECTORY_SEPARATOR . self::PATH_TO_IMAGE);
        $this->assertInstanceOf(Image::class, $image);
    }

    public function testLoadPdiPage()
    {

    }

    public function testCreatePage()
    {

    }

    public function testSetTitle()
    {

    }

    public function testSetCreator()
    {

    }

    public function testSetAuthor()
    {

    }

    public function testSetColor()
    {

    }

    public function testDrawRectangle()
    {

    }

    public function testStroke()
    {

    }
}
