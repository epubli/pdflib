<?php

namespace Epubli\Pdf\PdfLib;

use Epubli\Pdf\PdfLib\PdfImport\Document as PdiDocument;
use PDFlib;

/**
 * Class PdfLibWrapper A wrapper for a PDFLib object.
 * @package Epubli\Pdf\PdfLib
 */
class PdfLibWrapper
{
    const BOX_TYPE_CROP = 'crop';
    const PDF_MIN_VERSION = 14;

    const META_TITLE = 'Title';
    const META_CREATOR = 'Creator';
    const META_AUTHOR = 'Author';

    /**
     * @deprecated Poschi used these options for open_pdi_document everywhere but did not explain why.
     * @TODO: Figure out if this is really needed.
     */
    const POSCHIS_UNDOCUMENTED_OPTIONS = 'infomode=false requiredmode=minimum';

    /** @var PDFlib The wrapped PDFLib object. */
    private $pdfLib;

    /** @var array A list of the names of all virtual files is use. */
    private $virtualFileNames = [];

    public function __construct()
    {
        $this->pdfLib = new PDFlib();
    }

    /**
     * @param string $licenseKey The license key.
     * @throws Exception if key is invalid.
     */
    public function setLicenseKey($licenseKey)
    {
        if (!$licenseKey) {
            return;
        }
        try {
            $this->pdfLib->set_option('license=' . $licenseKey);
        } catch (\PDFlibException $ex) {
            throw new Exception($ex->getMessage(), 0, $ex);
        }
    }

    /**
     * Create a named virtual read-only file from data provided in memory.
     *
     * @param string $prefix
     * @param string $data
     * @return VirtualFile
     * @throws Exception If the given data is empty.
     * @throws \PDFlibException
     */
    public function createVirtualFile($prefix, $data)
    {
        if (empty($data)) {
            throw new Exception('Cannot create empty virtual file!');
        }

        // Get the next free filename.
        // We handle this here rather than letting PDFLib throw an exception.
        // (PDFLib seems to only be able to throw one single exception after which it enters some invalid state.
        // Therefore PDFLibExceptions should be avoided. FTR: Option errorpolicy=return does not work with create_pvf.)
        $counter = 0;
        $filename = $prefix;
        while (in_array($filename, $this->virtualFileNames)) {
            $filename = $prefix . '.' . ++$counter;
        }

        $file = VirtualFile::create($this->pdfLib, $filename, $data);
        $this->virtualFileNames[] = $filename;

        return $file;
    }

    /**
     * Open a disk-based or virtual PDF document and prepare it for later use.
     *
     * @param string|VirtualFile|\SplFileInfo $fileInfo An object convertible to a string that identifies a file (or PDFlib virtual file).
     * @param string $options PDFlib options. See PDFLib API Reference, Options for PDF_open_pdi_document( ).
     * @return PdiDocument The PDI document.
     * @throws Exception if the PDI document could not be opened.
     */
    public function openPdiDocument($fileInfo, $options = '')
    {
        /** @var int $handle */
        $handle = $this->pdfLib->open_pdi_document((string)$fileInfo, $options);

        if (!$handle) {
            $this->throwLastError();
        }

        return new PdiDocument($this->pdfLib, $handle);
    }

    /**
     * Open a disk-based or virtual image file subject to various options.
     *
     * @param string $imagetype
     * @param string $filename
     * @param string $options
     * @return Image
     * @throws Exception
     */
    public function loadImage($imagetype, $filename, $options)
    {
        /** @var int $handle */
        $handle = $this->pdfLib->load_image($imagetype, $filename, $options);

        if (!$handle) {
            $this->throwLastError();
        }

        return new Image($this->pdfLib, $handle);
    }

    /**
     * Create a virtual file with the given contents and open a PDF import document from that file.
     * @param string $fileContents The PDF contents.
     * @param string $virtualFilename The filename for the PDFlib virtual file.
     * @param string $options PDFlib options.
     * @return PdiDocument The PDI document.
     * @throws Exception if the PDI document could not be opened or the virtual file could not be created.
     */
    public function openPdiDocumentWithVirtualFile($fileContents, $virtualFilename, $options = self::POSCHIS_UNDOCUMENTED_OPTIONS)
    {
        $vFile = $this->createVirtualFile($virtualFilename, $fileContents);
        $pdiDocument = $this->openPdiDocument($vFile, $options);
        // Make the Document responsible for deleting the VirtualFile.
        $pdiDocument->holdFile($vFile);

        return $pdiDocument;
    }

    /**
     * Create a new PDF file subject to various options.
     *
     * @param string $filename Absolute or relative name of the PDF output file to be generated. If
     * filename is empty, the PDF document will be generated in memory instead of on file, and the
     * generated PDF data must be fetched by the client with getBuffer()
     * @param string $options
     * @return bool false on error, and true otherwise.
     */
    public function beginDocument($filename = '', $options = '')
    {
        return (bool)$this->pdfLib->begin_document($filename, $options);
    }

    /**
     * Create a new PDF file with a certain PDF version.
     *
     * @param int $version
     * @param string $filename
     * @return bool false on error, and true otherwise.
     */
    public function beginDocumentWithVersion($version, $filename = '')
    {
        // Use at least the minimum supported version.
        $version = max(self::PDF_MIN_VERSION, $version);

        return $this->beginDocument($filename, sprintf('compatibility=%.1f', $version/10));
    }

    /**
     * Close the generated PDF document and apply various options.
     *
     * @param string $options
     */
    public function endDocument($options = '')
    {
        $this->pdfLib->end_document($options);
    }

    /**
     * Get the contents of the PDF output buffer.
     *
     * @return string
     */
    public function getBuffer()
    {
        return $this->pdfLib->get_buffer();
    }

    /**
     * Add a new page to the document and specify various options.
     * @param $pageWidth
     * @param $pageHeight
     * @param string $options
     */
    public function beginPageExt($pageWidth, $pageHeight, $options = "")
    {
        $this->pdfLib->begin_page_ext($pageWidth, $pageHeight, $options);
    }

    /**
     * Finish a page and apply various options.
     * @param string $options
     */
    public function endPageExt($options = "")
    {
        $this->pdfLib->end_page_ext($options);
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->pdfLib->set_info(self::META_TITLE, $title);
    }

    /**
     * @param string $creator
     */
    public function setCreator($creator)
    {
        $this->pdfLib->set_info(self::META_CREATOR, $creator);
    }

    /**
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->pdfLib->set_info(self::META_AUTHOR, $author);
    }

    /**
     * Set the color space and color for the graphics and text state..
     *
     * @param string $fstype
     * @param string $colorspace
     * @param double $c1
     * @param double $c2
     * @param double $c3
     * @param double $c4
     */
    public function setColor($fstype, $colorspace, $c1, $c2, $c3, $c4)
    {
        $this->pdfLib->setcolor($fstype, $colorspace, $c1, $c2, $c3, $c4);
    }

    /**
     * Draw a rectangle.
     *
     * @param double $x
     * @param double $y
     * @param double $width
     * @param double $height
     */
    public function drawRectangle($x, $y, $width, $height)
    {
        $this->pdfLib->rect($x, $y, $width, $height);
    }

    /**
     * Stroke the path with the current color and line width, and clear it.
     */
    public function stroke()
    {
        $this->pdfLib->stroke();
    }

    /**
     * Get the text of the last thrown exception or the reason of a failed function call.
     *
     * @return string Text containing the description of the most recent error condition.
     */
    public function getLastErrorMessage()
    {
        return $this->pdfLib->get_errmsg();
    }

    /**
     * @throws Exception
     */
    private function throwLastError()
    {
        throw new Exception($this->getLastErrorMessage());
    }
}
