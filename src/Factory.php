<?php

namespace Epubli\Pdf\PdfLib;

use Epubli\Pdf\PdfLib\Scope\RootObject;
use PDFlib;
use PDFlibException;
use Psr\Log\LoggerInterface;

/**
 * Class Factory A factory for a PDFlib wrapped object.
 * @package Epubli\Pdf\PdfLib
 */
class Factory
{
    /** @var LoggerInterface */
    private $logger;

    /** @var string The license key used with new PDFLib objects. */
    private $licenseKey;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Set the license key.
     * @param string $licenseKey The license key.
     * @return bool Whether the license key was found valid.
     */
    public function setLicenseKey($licenseKey)
    {
        // Reset previously set key.
        $this->licenseKey = null;

        if (!$licenseKey) {
            $this->logger->notice('Skipping empty license key.');

            return false;
        }

        // Try setting the license at a PDFlib object to see if it is valid.
        $test = new PDFlib();
        try {
            $test->set_option("license=$licenseKey");
            $this->licenseKey = $licenseKey;
        } catch (PDFLibException $ex) {
            $this->logger->warning('Skipping invalid license key!', ['exception' => $ex]);

            return false;
        }

        return true;
    }

    /**
     * Create a new PDFlib object with the current license key.
     * @return RootObject
     */
    public function createPdfLibObject()
    {
        return new RootObject($this->licenseKey);
    }
}
