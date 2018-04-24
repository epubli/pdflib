<?php

namespace Epubli\Pdf\PdfLib\Scope;

use Epubli\Pdf\PdfLib\Closable;

/**
 * Class Page: A wrapper for a PDFlib Page scope.
 * @package Epubli\Pdf\PdfLib
 * @author Simon Schrape <simon@epubli.com>
 */
class Page extends ScopedObject implements Closable
{
    const SCOPE = 2;

    /** @var Document The Document containing this Page. */
    private $parent;

    /**
     * @param Document $parent
     * @param $pageWidth
     * @param $pageHeight
     * @param string $options PDFlib optlist
     */
    public function __construct(Document $parent, $pageWidth, $pageHeight, $options = '')
    {
        $lib = $parent->getLib();
        parent::__construct($lib);
        $this->parent = $parent;
        $lib->begin_page_ext($pageWidth, $pageHeight, $options);
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close()
    {
        $this->finish();
    }

    /**
     * Finish this page.
     * @param string $options PDFlib optlist
     */
    public function finish($options = '')
    {
        if (!$this->parent) {
            return;
        }

        $this->parent->childScopeClosed();
        $this->parent = null;

        $this->getLib()->end_page_ext($options);
    }

    protected function childScopeClosed()
    {
        // TODO: Implement childScopeClosed() method.
    }
}