<?php

namespace Epubli\Pdf\PdfLib;

use Epubli\Exception\Exception;
use PDFlib;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class PdfLibWrapper
{
    const NUMBER_OF_PAGES = 'length:pages';
    const ADJUST_PAGE = 'adjustpage';
    const BOX_TYPE_CROP = 'crop';
    const PDF_MIN_VERSION = 14;

    const META_TITLE = 'Title';
    const META_CREATOR = 'Creator';
    const META_AUTHOR = 'Author';

    /**
     * @var PDFlib
     */
    protected $pdfLib;

    /**
     * @param PDFlib $PdfLib
     * @param string $licenseKey
     */
    public function __construct(PDFlib $PdfLib, $licenseKey = null)
    {
        $this->pdfLib = $PdfLib;
        if ($licenseKey) {
            $this->pdfLib->set_option('license=' . $licenseKey);
        }
    }

    /**
     * @param string $inputPdf The PDF contents.
     * @param string $virtualFilename The filename for the PDFlib virtual file.
     * @return int The handle to the PDI document.
     * @throws Exception if the PDI document could not be opened.
     */
    public function getPdiDocumentFromRawPdf($inputPdf, $virtualFilename)
    {
        $this->pdfLib->create_pvf($virtualFilename, $inputPdf, "");

        return $this->openPdiDocument($virtualFilename, 'infomode=false requiredmode=minimum');
    }

    /**
     * @param $pdiDocument
     * @param int $pageNumber
     * @return int|float
     */
    public function getPageWidth($pdiDocument, $pageNumber = 1)
    {
        /** @var int|float $width */
        $width = $this->pdfLib->pcos_get_number($pdiDocument, 'pages[' . ($pageNumber - 1) . ']/width');

        return $width;
    }

    /**
     * @param $pdiDocument
     * @param int $pageNumber
     * @return int|float
     */
    public function getPageHeight($pdiDocument, $pageNumber = 1)
    {
        /** @var int|float $height */
        $height = $this->pdfLib->pcos_get_number($pdiDocument, 'pages[' . ($pageNumber - 1) . ']/height');

        return $height;
    }

    /**
     * @return string
     */
    public function output()
    {
        return $this->pdfLib->get_buffer();
    }

    /**
     * @param $pdiDocument
     */
    public function beginDocument($pdiDocument)
    {
        $this->pdfLib->begin_document(
            '',
            sprintf('compatibility=%s', $this->getPdfVersion($pdiDocument))
        );
    }

    public function getPdfVersion($pdiDocument)
    {
        $pdiVersion = $this->pdfLib->pcos_get_number($pdiDocument, 'pdfversion');
        $version = max(self::PDF_MIN_VERSION, $pdiVersion);
        return $version/10;
    }

    /**
     * @param $pdiDocument
     * @param string $virtualFilename
     * @param bool $endDocument
     */
    public function closeDocument($pdiDocument, $virtualFilename, $endDocument = true)
    {
        $this->pdfLib->close_pdi_document($pdiDocument);

        $this->pdfLib->delete_pvf($virtualFilename);

        if ($endDocument) {
            $this->pdfLib->end_document('');
        }
    }

    /**
     * @param $pdiDocument
     * @return int
     */
    public function getLastPageNumber($pdiDocument)
    {
        $lastPageNumber = (int)$this->pdfLib->pcos_get_number($pdiDocument, self::NUMBER_OF_PAGES);
        return $lastPageNumber;
    }

    /**
     * @param $pdiDocument
     * @param int $pageNo
     * @param string $options
     * @return mixed
     */
    public function openPdiPage($pdiDocument, $pageNo, $options = "")
    {
        return $this->pdfLib->open_pdi_page($pdiDocument, $pageNo, $options);
    }

    /**
     * @param $pdiPage
     */
    public function closePdiPage($pdiPage)
    {
        $this->pdfLib->close_pdi_page($pdiPage);
    }

    /**
     * @param $pageWidth
     * @param $pageHeight
     * @param string $options
     */
    public function beginPageExt($pageWidth, $pageHeight, $options = "")
    {
        $this->pdfLib->begin_page_ext($pageWidth, $pageHeight, $options);
    }

    /**
     * @param string $options
     */
    public function endPageExt($options = "")
    {
        $this->pdfLib->end_page_ext($options);
    }

    /**
     * Place an image or template on the page, subject to various options.
     *
     * @param int $image
     * @param double $x
     * @param double $y
     * @param string $optlist
     */
    public function fitImage($image, $x, $y, $optlist)
    {
        $this->pdfLib->fit_image($image, $x, $y, $optlist);
    }

    /**
     * @param $page
     * @param int $xPos
     * @param int $yPos
     * @param string $options
     */
    public function fitPdiPage($page, $xPos = 0, $yPos = 0, $options = self::ADJUST_PAGE)
    {
        $this->pdfLib->fit_pdi_page($page, $xPos, $yPos, $options);
    }

    /**
     * Open a disk-based or virtual PDF document and prepare it for later use.
     *
     * @param string $filename The filename (real file or PDFlib virtual file).
     * @param string $options PDFlib options
     * @return int The handle to the PDI document.
     * @throws Exception if the PDI document could not be opened.
     */
    public function openPdiDocument($filename, $options = '')
    {
        /** @var int $pdiHandle */
        $pdiHandle = $this->pdfLib->open_pdi_document($filename, $options);

        if (!$pdiHandle) {
            throw new Exception($this->pdfLib->get_errmsg());
        }

        return $pdiHandle;
    }

    /**
     * @param $pdiDocument
     */
    public function closePdiDocument($pdiDocument)
    {
        $this->pdfLib->close_pdi_document($pdiDocument);
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
     * Open a disk-based or virtual image file subject to various options.
     *
     * @param string $imagetype
     * @param string $filename
     * @param string $optlist
     * @return int An image handle, or -1 (in PHP: 0) on error.
     */
    public function loadImage($imagetype, $filename, $optlist)
    {
        /** @var int $handle */
        $handle = $this->pdfLib->load_image($imagetype, $filename, $optlist);

        return $handle;
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
}
