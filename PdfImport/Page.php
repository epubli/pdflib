<?php

namespace Epubli\Pdf\PdfLib\PdfImport;

use Epubli\Pdf\PdfLib\Closable;
use Epubli\Pdf\PdfLib\LibObject;
use Epubli\Pdf\PdfLib\Scope\RootObject;

/**
 * Class Page: A wrapper for a handle to a page of a
 * PDI ({@link https://www.pdflib.com/en/produkte/pdflib-9-familie/pdflib-pdi/ PDF Import Library}) document
 * retrieved from PDFlib.
 * @package Epubli\Pdf\PdfLib
 * @author Simon Schrape <simon@epubli.com>
 */
class Page extends LibObject implements Closable
{
    const OPTION_ADJUST_PAGE = 'adjustpage';

    /** @var int The handle retrieved from PDFlib. */
    private $handle;

    public function __construct(\PDFlib $lib, $handle)
    {
        parent::__construct($lib);
        $this->handle = $handle;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close()
    {
        if ($this->handle && $this->getCurrentScope() > RootObject::SCOPE) {
            $this->getLib()->close_pdi_page($this->handle);
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
        $this->getLib()->fit_pdi_page($this->handle, $x, $y, $options);
    }
}