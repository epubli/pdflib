<?php

namespace Epubli\Pdf\PdfLib;

use Epubli\Pdf\PdfLib\PdfImport\Document as PdiDocument;
use Psr\Log\LoggerInterface;

class PdfLibWrapperTest extends \PHPUnit_Framework_TestCase
{
    const PATH_TO_MINIMAL_PDF = 'data/minimal.pdf';
    const PATH_TO_CORRUPT_PDF = 'data/corrupt.pdf';
    const PATH_TO_IMAGE = 'data/image.png';

    /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $logger;

    public function setup()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testSetInvalidLicenseKeyWillLogWarning()
    {
        $wrapper = new PdfLibWrapper($this->logger);
        $this->logger->expects($this->once())->method('warning')->with('Skipping invalid license key!');
        $success = $wrapper->setLicenseKey('unit_test_license');
        $this->assertFalse($success);
    }

    public function testSetEmptyLicenseKeyWillNotThrow()
    {
        $wrapper = new PdfLibWrapper($this->logger);
        $this->logger->expects($this->once())->method('notice')->with('Skipping empty license key.');
        $success = $wrapper->setLicenseKey('');
        $this->assertFalse($success);
    }

    /**
     * @expectedException Epubli\Pdf\PdfLib\Exception
     * @expectedExceptionMessage Cannot create empty virtual file!
     * @throws Exception
     * @throws \PDFlibException
     */
    public function testCreateVirtualFileWithoutDataWillThrow()
    {
        $wrapper = new PdfLibWrapper($this->logger);
        $wrapper->createVirtualFile('testfile', '');
    }

    /**
     * @throws Exception
     * @throws \PDFlibException
     */
    public function testCreateVirtualFileWithoutPrefixWillDefault()
    {
        $wrapper = new PdfLibWrapper($this->logger);
        $file = $wrapper->createVirtualFile(null, 'lorem ipsum');
        $this->assertEquals('pvf', (string)$file);
    }

    /**
     * @throws Exception
     * @throws \PDFlibException
     */
    public function testCreateVirtualFileWithExistingName()
    {
        $wrapper = new PdfLibWrapper($this->logger);
        $file1 = $wrapper->createVirtualFile('testfile', 'lorem ipsum');
        $file2 = $wrapper->createVirtualFile('testfile.1', 'dolor');
        $file3 = $wrapper->createVirtualFile('testfile', 'sit');
        $file4 = $wrapper->createVirtualFile('testfile.2', 'amet');
        $file5 = $wrapper->createVirtualFile('testfile.3', 'consectuer');
        $file6 = $wrapper->createVirtualFile('testfile', 'est fumpum');

        $this->assertEquals('testfile', (string)$file1);
        $this->assertEquals('testfile.1', (string)$file2);
        $this->assertEquals('testfile.2', (string)$file3);
        $this->assertEquals('testfile.2.1', (string)$file4);
        $this->assertEquals('testfile.3', (string)$file5);
        $this->assertEquals('testfile.4', (string)$file6);
    }

    /**
     * @throws Exception
     */
    public function testOpenPdiDocument()
    {
        $wrapper = new PdfLibWrapper($this->logger);
        $document = $wrapper->openPdiDocument(__DIR__ . DIRECTORY_SEPARATOR . self::PATH_TO_MINIMAL_PDF);
        $this->assertInstanceOf(PdiDocument::class, $document);
    }

    /**
     * @expectedException Epubli\Pdf\PdfLib\Exception
     * @expectedExceptionMessage Cannot open PDI document
     * @throws Exception
     */
    public function testOpenPdiDocumentWithInvalidInputThrows()
    {
        $wrapper = new PdfLibWrapper($this->logger);
        $wrapper->openPdiDocument(__DIR__ . DIRECTORY_SEPARATOR . self::PATH_TO_CORRUPT_PDF);
    }

    /**
     * @throws Exception
     * @throws \PDFlibException
     */
    public function testOpenPdiDocumentFromVirtualFile()
    {
        $wrapper = new PdfLibWrapper($this->logger);
        $contents = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . self::PATH_TO_MINIMAL_PDF);
        $file = $wrapper->createVirtualFile(null, $contents);
        $pdiDoc = $wrapper->openPdiDocument($file);

        $this->assertInstanceOf(PdiDocument::class, $pdiDoc);
    }

    /**
     * @throws Exception
     * @throws \PDFlibException
     */
    public function testOpenPdiDocumentWithVirtualFile()
    {
        $contents = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . self::PATH_TO_MINIMAL_PDF);
        $wrapper = new PdfLibWrapper($this->logger);
        $document = $wrapper->openPdiDocumentWithVirtualFile($contents, 'my_pvf');
        $this->assertInstanceOf(PdiDocument::class, $document);
    }

    /**
     * @expectedException Epubli\Pdf\PdfLib\Exception
     * @expectedExceptionMessage Cannot load image
     * @throws Exception
     */
    public function testLoadImageWithInvalidInputThrows()
    {
        $wrapper = new PdfLibWrapper($this->logger);
        $wrapper->loadImage(__DIR__ . DIRECTORY_SEPARATOR . self::PATH_TO_CORRUPT_PDF);
    }

    /**
     * @expectedException Epubli\Pdf\PdfLib\Exception
     * @expectedExceptionMessageRegExp /Cannot load image.+Function must not be called in 'object' scope/
     * @throws Exception
     */
    public function testLoadImageOutOfDocumentScopeThrows()
    {
        $wrapper = new PdfLibWrapper($this->logger);
        $image = $wrapper->loadImage(__DIR__ . DIRECTORY_SEPARATOR . self::PATH_TO_IMAGE);
        $this->assertInstanceOf(Image::class, $image);
    }

    /**
     * @throws Exception
     */
    public function testLoadImage()
    {
        $wrapper = new PdfLibWrapper($this->logger);
        $wrapper->beginDocument();
        $image = $wrapper->loadImage(__DIR__ . DIRECTORY_SEPARATOR . self::PATH_TO_IMAGE);
        $this->assertInstanceOf(Image::class, $image);
    }

    public function testBeginDocument()
    {

    }

    public function testBeginDocumentWithVersion()
    {

    }

    public function testEndDocument()
    {

    }

    public function testGetBuffer()
    {

    }

    public function testBeginPageExt()
    {

    }

    public function testEndPageExt()
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

    public function testGetLastErrorMessage()
    {

    }
}
