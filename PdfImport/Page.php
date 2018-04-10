<?php

namespace Epubli\Pdf\PdfLib\PdfImport;

/**
 * Class PdiPage: A wrapper for a handle to a page of a
 * PDI ({@link https://www.pdflib.com/en/produkte/pdflib-9-familie/pdflib-pdi/ PDF Import Library}) document
 * retrieved from PDFLib.
 * @package Epubli\Pdf\PdfLib
 * @author Simon Schrape <simon@epubli.com>
 */
class Page
{
    /** @var \PDFlib The PDFLib bridge this object belongs to. */
    private $lib;

    /** @var int The handle retrieved from PDFLib. */
    private $handle;

    public function __construct(\PDFlib $lib, $handle)
    {
        $this->lib = $lib;
        $this->handle = $handle;
    }

    /**
     * Get the handle of this document.
     * @deprecated: We want to keep that handle private. Implement object-oriented methods instead.
     */
    public function getHandle()
    {
        return $this->handle;
    }
}