<?php

namespace Epubli\Pdf\PdfLib\PdfImport;

use Epubli\Pdf\PdfLib\Closable;
use Epubli\Pdf\PdfLib\Exception;
use Epubli\Pdf\PdfLib\File\VirtualFile;
use Epubli\Pdf\PdfLib\LibObject;
use Epubli\Pdf\PdfLib\Scope\RootObject;

/**
 * Class Document: A wrapper for a
 * PDI ({@link https://www.pdflib.com/en/produkte/pdflib-9-familie/pdflib-pdi/ PDF Import Library}) document
 * handle retrieved from PDFlib.
 * @package Epubli\Pdf\PdfLib
 * @author Simon Schrape <simon@epubli.com>
 */
class Document extends LibObject implements Closable
{
    /** @var RootObject The PDFlib root object that created this Document. */
    private $root;

    /** @var int The handle retrieved from PDFlib. */
    private $handle;

    /** @var VirtualFile The virtual file this document is read from and that it is responsible to delete when closing. */
    private $heldFile;

    /** @var Page[] */
    private $pages = [];

    public function __construct(RootObject $root, $handle)
    {
        parent::__construct($root->getLib());
        $this->root = $root;
        $this->handle = $handle;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close()
    {
        if ($this->handle) {
            foreach ($this->pages as $page) {
                $page->close();
            }
            $this->getLib()->close_pdi_document($this->handle);
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
     * Open a Page of this Document.
     * @param int $pageNumber
     * @param string $options
     * @return Page
     * @throws Exception
     */
    public function openPage($pageNumber, $options = '')
    {
        $handle = $this->getLib()->open_pdi_page($this->handle, $pageNumber, $options);
        if (!$handle) {
            $this->throwLastError("Could not open PDI Page #$pageNumber!");
        }

        $page = new Page($this->getLib(), $handle);
        $this->pages[] = $page;

        return $page;
    }

    /**
     * Get the PDF version of this Document as an integer (17 is for PDF 1.7).
     * @return int
     */
    public function getPdfVersion()
    {
        return (int)$this->getLib()->pcos_get_number($this->handle, 'pdfversion');
    }

    /**
     * Get the page width of a certain page of this Document.
     * @param int $pageNumber
     * @return int|float
     */
    public function getPageWidth($pageNumber = 1)
    {
        /** @var int|float $width */
        $width = $this->getLib()->pcos_get_number($this->handle, 'pages[' . ($pageNumber - 1) . ']/width');

        return $width;
    }

    /**
     * Get the page height of a certain page of this Document.
     * @param int $pageNumber
     * @return int|float
     */
    public function getPageHeight($pageNumber = 1)
    {
        /** @var int|float $height */
        $height = $this->getLib()->pcos_get_number($this->handle, 'pages[' . ($pageNumber - 1) . ']/height');

        return $height;
    }

    /**
     * Get the number of pages of this Document.
     * @return int
     */
    public function getNumberOfPages()
    {
        return (int)$this->getLib()->pcos_get_number($this->handle, 'length:pages');
    }
}