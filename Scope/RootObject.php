<?php

namespace Epubli\Pdf\PdfLib\Scope;

use Epubli\Pdf\PdfLib\Exception;
use Epubli\Pdf\PdfLib\File\VirtualFile;
use Epubli\Pdf\PdfLib\PdfImport\Document as PdiDocument;
use PDFlibException;

/**
 * Class RootObject: A wrapper for a PDFlib Object scope.
 * @package Epubli\Pdf\PdfLib
 * @author Simon Schrape <simon@epubli.com>
 */
class RootObject extends ScopedObject
{
    const SCOPE = 0;
    const VIRTUAL_FILE_DEFAULT_PREFIX = 'pvf';
    const PDF_MIN_VERSION = 14;

    /** @var array A list of the names of all virtual files in use. */
    private $virtualFileNames = [];

    /** @var Document The PDF document currently being edited. */
    private $document;

    /**
     * PdfLibObject constructor. Create a new PDFlib object, and optionally set the license key.
     * @param null $licenseKey
     */
    public function __construct($licenseKey = null)
    {
        $lib = new \PDFlib();
        // See PDFlib API Reference, 2.1 Exception Handling.
        $lib->set_option('errorpolicy=return');
        if ($licenseKey) {
            $lib->set_option("license=$licenseKey");
        }
        parent::__construct($lib);
    }

    public function __destruct()
    {
        if ($this->document) {
            $this->document->close();
        }
    }

    /**
     * Create a named virtual read-only file from data provided in memory.
     *
     * @param string $data
     * @param string $prefix
     * @return VirtualFile
     * @throws Exception If the given data is empty.
     * @throws \PDFlibException If something unexpected happened. Option errorpolicy has no effect on creat_pvf.
     *      Since this might render our PDFlib object unusable we do not catch the exception.
     */
    public function createVirtualFile($data, $prefix = null)
    {
        if (empty($data)) {
            throw new Exception('Cannot create empty virtual file!');
        }

        $prefix = (string)$prefix ?: self::VIRTUAL_FILE_DEFAULT_PREFIX;

        // Get the next free filename.
        // We handle this here rather than letting PDFlib throw an exception.
        // (PDFlib seems to only be able to throw one single exception after which it enters some invalid state.
        // Therefore PDFLibExceptions should be avoided. FTR: Option errorpolicy=return does not work with create_pvf.)
        $counter = 0;
        $filename = $prefix;
        while (in_array($filename, $this->virtualFileNames)) {
            $filename = $prefix . '.' . ++$counter;
        }

        $file = VirtualFile::create($this->getLib(), $filename, $data);
        $this->virtualFileNames[] = $filename;

        return $file;
    }

    /**
     * Open a disk-based or virtual PDF document and prepare it for later use.
     *
     * @param string|VirtualFile|\SplFileInfo $fileInfo An object convertible to a string that identifies a file (or PDFlib virtual file).
     * @param string $options PDFlib options. See PDFlib API Reference, Options for PDF_open_pdi_document( ).
     * @return PdiDocument The PDI document.
     * @throws Exception if the PDI document could not be opened.
     */
    public function openPdiDocument($fileInfo, $options = '')
    {
        /** @var int $handle */
        $handle = $this->getLib()->open_pdi_document((string)$fileInfo, $options);

        if (!$handle) {
            $this->throwLastError("Cannot open PDI document $fileInfo!");
        }

        return new PdiDocument($this, $handle);
    }

    /**
     * Create a virtual file with the given contents and open a PDF import document from that file.
     * @param string $fileContents The PDF contents.
     * @param string $virtualFilename The filename for the PDFlib virtual file.
     * @param string $options PDFlib options.
     * @return PdiDocument The PDI document.
     * @throws Exception if the PDI document could not be opened or the virtual file could not be created.
     * @throws PDFlibException
     */
    public function openPdiDocumentWithVirtualFile(
        $fileContents,
        $virtualFilename = null,
        $options = ''
    ) {
        $vFile = $this->createVirtualFile($fileContents, $virtualFilename);
        $pdiDocument = $this->openPdiDocument($vFile, $options);
        // Make the Document responsible for deleting the VirtualFile.
        $pdiDocument->holdFile($vFile);

        return $pdiDocument;
    }

    /**
     * Create a new PDF Document OR get the one currently being edited.
     * In contrast to createDocument this does not accept PDFlib options. The benefit is that it will not throw
     * an exception but properly handles the creation or retrieval of the one and only Document (Singleton).
     *
     * @return Document|null null on error.
     */
    public function getDocument()
    {
        if (!$this->document) {
            try {
                $this->createDocument();
            } catch (Exception $e) {
                // Nothing to do since exception condition is checked above.
            }
        }

        return $this->document;
    }

    /**
     * Create a new PDF file subject to various options.
     *
     * @param string $filename Absolute or relative name of the PDF output file to be generated. If
     * filename is empty, the PDF document will be generated in memory instead of on file, and the
     * generated PDF data must be fetched by the client with getBuffer()
     * @param string $options PDFlib optlist
     * @return Document|null null on error.
     * @throws Exception If a document is already being edited.
     */
    public function createDocument($filename = '', $options = '')
    {
        if ($this->document) {
            throw new Exception(
                'A document is already being edited! There can be only one. Use another PDFlib RootObject.'
            );
        }

        return $this->document = Document::create($this, $filename, $options);
    }

    /**
     * Create a new PDF file with a certain PDF version.
     *
     * @param int $version
     * @param string $filename
     * @return Document|null null on error.
     * @throws Exception If a document is already being edited.
     */
    public function createDocumentWithVersion($version, $filename = '')
    {
        // Use at least the minimum supported version.
        $version = max(self::PDF_MIN_VERSION, $version);

        return $this->createDocument($filename, sprintf('compatibility=%.1f', $version/10));
    }

    protected function childScopeClosed()
    {
        $this->document = null;
    }
}