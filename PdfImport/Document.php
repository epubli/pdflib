<?php

namespace Epubli\Pdf\PdfLib\PdfImport;

use Epubli\Pdf\PdfLib\VirtualFile;

/**
 * Class PdiDocument: A wrapper for a
 * PDI ({@link https://www.pdflib.com/en/produkte/pdflib-9-familie/pdflib-pdi/ PDF Import Library}) document
 * handle retrieved from PDFLib.
 * @package Epubli\Pdf\PdfLib
 * @author Simon Schrape <simon@epubli.com>
 */
class Document
{
    /** @var \PDFlib The PDFLib bridge this object belongs to. */
    private $lib;

    /** @var int The handle retrieved from PDFLib. */
    private $handle;

    /** @var VirtualFile The virtual file this document is read from and that it is responsible to delete when closing. */
    private $heldFile;

    public function __construct(\PDFlib $lib, $handle)
    {
        $this->lib = $lib;
        $this->handle = $handle;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Get the handle of this document.
     * @deprecated We want to keep that handle private. Implement object-oriented methods instead.
     */
    public function getHandle()
    {
        return $this->handle;
    }

    public function close()
    {
        if ($this->handle) {
            $this->lib->close_pdi_document($this->handle);
        }
        if ($this->heldFile) {
            $this->heldFile->delete();
        }

        $this->handle = null;
        $this->heldFile = null;
    }

    public function holdFile(VirtualFile $file)
    {
        $this->heldFile = $file;
    }

    /**
     * The PDF version of this Document as an integer (17 is for PDF 1.7).
     * @return int
     */
    public function getPdfVersion()
    {
        return (int)$this->lib->pcos_get_number($this->handle, 'pdfversion');
    }
}