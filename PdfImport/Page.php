<?php

namespace Epubli\Pdf\PdfLib\PdfImport;

/**
 * Class Page: A wrapper for a handle to a page of a
 * PDI ({@link https://www.pdflib.com/en/produkte/pdflib-9-familie/pdflib-pdi/ PDF Import Library}) document
 * retrieved from PDFLib.
 * @package Epubli\Pdf\PdfLib
 * @author Simon Schrape <simon@epubli.com>
 */
class Page
{
    const OPTION_ADJUST_PAGE = 'adjustpage';

    /** @var \PDFlib The PDFLib bridge this object belongs to. */
    private $lib;

    /** @var int The handle retrieved from PDFLib. */
    private $handle;

    public function __construct(\PDFlib $lib, $handle)
    {
        $this->lib = $lib;
        $this->handle = $handle;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close()
    {
        if ($this->handle) {
            $this->lib->close_pdi_page($this->handle);
        }

        $this->handle = null;
    }

    /**
     * Place this imported PDF page on the current page, subject to various options.
     *
     * @param int $x See below.
     * @param int $y The coordinates of the reference point in the user coordinate system where
     *               the page will be located, subject to various options.
     * @param string $options
     */
    public function fitOnPage($x = 0, $y = 0, $options = self::OPTION_ADJUST_PAGE)
    {
        $this->lib->fit_pdi_page($this->handle, $x, $y, $options);
    }
}