<?php

namespace Epubli\Pdf\PdfLib\File;

use Epubli\Pdf\PdfLib\Closable;
use Epubli\Pdf\PdfLib\LibObject;

/**
 * Class Image: A wrapper for an image handle retrieved from PDFlib.
 * @package Epubli\Pdf\PdfLib
 * @author Simon Schrape <simon@epubli.com>
 */
class Image extends LibObject implements Closable
{
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
        if ($this->handle) {
            $this->getLib()->close_image($this->handle);
        }

        $this->handle = null;
    }

    /**
     * Place this Image on the current page, subject to various options.
     *
     * @param int $x See below.
     * @param int $y The coordinates of the reference point in the user coordinate system where
     *               the image will be located, subject to various options.
     * @param string $options
     */
    public function fitOnPage($x = 0, $y = 0, $options = self::OPTION_ADJUST_PAGE)
    {
        $this->getLib()->fit_image($this->handle, $x, $y, $options);
    }
}