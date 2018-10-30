<?php

namespace Epubli\Pdf\PdfLib;

use Epubli\Pdf\PdfLib\Scope\RootObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FactoryTest extends TestCase
{
    /** @var LoggerInterface|MockObject */
    private $logger;

    public function setup()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testSetInvalidLicenseKeyWillLogWarning()
    {
        $factory = new Factory($this->logger);
        $this->logger->expects($this->once())->method('warning')->with('Skipping invalid license key!');
        $success = $factory->setLicenseKey('unit_test_license');
        $this->assertFalse($success);
    }

    public function testSetEmptyLicenseKeyWillNotThrow()
    {
        $factory = new Factory($this->logger);
        $this->logger->expects($this->once())->method('notice')->with('Skipping empty license key.');
        $success = $factory->setLicenseKey('');
        $this->assertFalse($success);
    }

    public function testCreatePdfLibObject()
    {
        $factory = new Factory($this->logger);
        $lib = $factory->createPdfLibObject();
        $this->assertInstanceOf(RootObject::class, $lib);
    }
}
