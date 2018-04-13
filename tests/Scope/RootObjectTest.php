<?php

namespace Epubli\Pdf\PdfLib\Scope;

use Epubli\Pdf\PdfLib\Exception;
use Epubli\Pdf\PdfLib\PdfImport\Document as PdiDocument;

class RootObjectTest extends \PHPUnit_Framework_TestCase
{
    const PATH_TO_MINIMAL_PDF = '../data/minimal.pdf';
    const PATH_TO_CORRUPT_PDF = '../data/corrupt.pdf';
    const PATH_TO_IMAGE = '../data/image.png';

    /**
     * @expectedException Epubli\Pdf\PdfLib\Exception
     * @expectedExceptionMessage Cannot create empty virtual file!
     * @throws \PDFlibException
     * @throws Exception
     */
    public function testCreateVirtualFileWithoutDataWillThrow()
    {
        $wrapper = new RootObject();
        $wrapper->createVirtualFile('', 'testfile');
    }

    /**
     * @throws Exception
     * @throws \PDFlibException
     */
    public function testCreateVirtualFileWithoutPrefixWillDefault()
    {
        $wrapper = new RootObject();
        $file = $wrapper->createVirtualFile('lorem ipsum');
        $this->assertEquals('pvf', (string)$file);
    }

    /**
     * @throws Exception
     * @throws \PDFlibException
     */
    public function testCreateVirtualFileWithExistingName()
    {
        $wrapper = new RootObject();
        $file1 = $wrapper->createVirtualFile('l', 'testfile');
        $file2 = $wrapper->createVirtualFile('o', 'testfile.1');
        $file3 = $wrapper->createVirtualFile('r', 'testfile');
        $file4 = $wrapper->createVirtualFile('e', 'testfile.2');
        $file5 = $wrapper->createVirtualFile('m', 'testfile.3');
        $file6 = $wrapper->createVirtualFile('i', 'testfile');

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
        $wrapper = new RootObject();
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
        $wrapper = new RootObject();
        $wrapper->openPdiDocument(__DIR__ . DIRECTORY_SEPARATOR . self::PATH_TO_CORRUPT_PDF);
    }

    /**
     * @throws Exception
     * @throws \PDFlibException
     */
    public function testOpenPdiDocumentFromVirtualFile()
    {
        $wrapper = new RootObject();
        $contents = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . self::PATH_TO_MINIMAL_PDF);
        $file = $wrapper->createVirtualFile($contents);
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
        $wrapper = new RootObject();
        $document = $wrapper->openPdiDocumentWithVirtualFile($contents);
        $this->assertInstanceOf(PdiDocument::class, $document);
    }

    public function testGetDocument()
    {
        $wrapper = new RootObject();
        $document1 = $wrapper->getDocument();
        $this->assertInstanceOf(Document::class, $document1);
        $document2 = $wrapper->getDocument();
        $this->assertSame($document1, $document2);
    }

    public function testCreateDocument()
    {
        $wrapper = new RootObject();
        $document = $wrapper->createDocument();
        $this->assertInstanceOf(Document::class, $document);
    }

    /**
     * @expectedException Epubli\Pdf\PdfLib\Exception
     * @expectedExceptionMessage There can be only one.
     * @throws Exception
     */
    public function testCreateDocumentMoreThanOnceThrows()
    {
        $wrapper = new RootObject();
        $wrapper->createDocument();
        $wrapper->createDocument();
    }

    /**
     * @dataProvider provideDocumentWithVersionData
     * @throws Exception
     */
    public function testCreateDocumentWithVersion($version, $expectedOptlist)
    {
        /** @var RootObject|\PHPUnit_Framework_MockObject_MockObject $wrapper */
        $wrapper = $this->createPartialMock(RootObject::class, ['createDocument']);
        $filename = 'somefile';
        $wrapper->expects($this->once())
            ->method('createDocument')
            ->with($filename, $expectedOptlist);
        $wrapper->createDocumentWithVersion($version, $filename);
    }

    public function provideDocumentWithVersionData()
    {
        return [
            [9, 'compatibility=1.4'],
            [10, 'compatibility=1.4'],
            [11, 'compatibility=1.4'],
            [12, 'compatibility=1.4'],
            [13, 'compatibility=1.4'],
            [14, 'compatibility=1.4'],
            [15, 'compatibility=1.5'],
            [16, 'compatibility=1.6'],
            [17, 'compatibility=1.7'],
            [18, 'compatibility=1.8'],
            [19, 'compatibility=1.9'],
            [20, 'compatibility=2.0'],
        ];
    }
}
