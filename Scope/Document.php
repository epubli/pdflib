<?php

namespace Epubli\Pdf\PdfLib\Scope;

use Epubli\Pdf\PdfLib\Closable;
use Epubli\Pdf\PdfLib\Exception;
use Epubli\Pdf\PdfLib\File\Image;
use Epubli\Pdf\PdfLib\File\VirtualFile;
use Epubli\Pdf\PdfLib\PdfImport\Document as PdiDocument;
use Epubli\Pdf\PdfLib\PdfImport\Page as PdiPage;

/**
 * Class Document: A wrapper for a PDFlib Document scope.
 * @package Epubli\Pdf\PdfLib
 * @author Simon Schrape <simon@epubli.com>
 */
class Document extends ScopedObject implements Closable
{
    const SCOPE = 1;
    const META_TITLE = 'Title';
    const META_CREATOR = 'Creator';
    const META_AUTHOR = 'Author';

    /** @var RootObject The owning RootObject of this Document. */
    private $parent;

    /** @var ScopedObject The child object currently in scope. */
    private $currentChild;

    /** @var Closable[] */
    private $loadedObjects = [];

    /** @var bool Whether this object is already closing. */
    private $closing = false;

    protected function __construct(RootObject $parent, $filename = '', $options = '')
    {
        parent::__construct($parent->getLib());
        $this->parent = $parent;
    }

    public function __destruct()
    {
        if ($this->closing) {
            return;
        }
        $this->closing = true;

        $this->close();
    }

    /**
     * @param RootObject $parent
     * @param string $filename
     * @param string $options
     * @return Document|null
     */
    public static function create(RootObject $parent, $filename = '', $options = '')
    {
        return $parent->getLib()->begin_document($filename, $options) ? new self($parent) : null;
    }

    public function close()
    {
        $this->finish();
    }

    /**
     * Close the generated PDF document and return its contents if requested.
     * @param bool $getBuffer Whether to read out the buffered contents.
     * @param string $options PDFlib optlist
     * @return string The contents of the PDF document.
     */
    public function finish($getBuffer = false, $options = '')
    {
        if ($this->closing) {
            return '';
        }
        $this->closing = true;

        $this->parent->childScopeClosed();

        foreach ($this->loadedObjects as $closable) {
            $closable->close();
        }

        try {
            $this->getLib()->end_document($options);
        } catch (\PDFlibException $ex) {
            // Ignore “Generated document doesn't contain any pages”.
            return '';
        }

        return $getBuffer ? $this->getLib()->get_buffer() : '';
    }

    /**
     * Open a disk-based or virtual image file subject to various options.
     *
     * @param string|VirtualFile|\SplFileInfo $fileInfo
     * @param string $type
     * @param string $options
     * @return Image
     * @throws Exception
     */
    public function loadImage($fileInfo, $type = 'auto', $options = '')
    {
        /** @var int $handle */
        $handle = $this->getLib()->load_image($type, (string)$fileInfo, $options);

        if (!$handle) {
            $this->throwLastError("Cannot load image $fileInfo!");
        }

        $image = new Image($this->getLib(), $handle);
        $this->loadedObjects[] = $image;

        return $image;
    }

    /**
     * Open a PDI page for later fitting it on the current page.
     *
     * @param PdiDocument $pdiDocument
     * @param int $pageNumber
     * @param string $options PDFlib optlist
     * @return PdiPage
     * @throws Exception
     */
    public function loadPdiPage(PdiDocument $pdiDocument, $pageNumber, $options = '')
    {
        $page = $pdiDocument->openPage($pageNumber, $options);
        $this->loadedObjects[] = $page;

        return $page;
    }

    /**
     * Add a new page to the document.
     *
     * @param $pageWidth
     * @param $pageHeight
     * @param string $options PDFlib optlist
     * @return Page|null null on error.
     * @throws Exception If a page is already being edited.
     */
    public function createPage($pageWidth, $pageHeight, $options = '')
    {
        if ($this->currentChild) {
            throw new Exception(
                'A page is already being edited! There can be only one. Use another PDFlib RootObject.'
            );
        }

        return $this->currentChild = new Page($this, $pageWidth, $pageHeight, $options);
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->getLib()->set_info(self::META_TITLE, $title);
    }

    /**
     * @param string $creator
     */
    public function setCreator($creator)
    {
        $this->getLib()->set_info(self::META_CREATOR, $creator);
    }

    /**
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->getLib()->set_info(self::META_AUTHOR, $author);
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
        $this->getLib()->setcolor($fstype, $colorspace, $c1, $c2, $c3, $c4);
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
        $this->getLib()->rect($x, $y, $width, $height);
    }

    /**
     * Stroke the path with the current color and line width, and clear it.
     */
    public function stroke()
    {
        $this->getLib()->stroke();
    }

    protected function childScopeClosed()
    {
        $this->currentChild = null;
    }
}