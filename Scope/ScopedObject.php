<?php

namespace Epubli\Pdf\PdfLib\Scope;

use Epubli\Pdf\PdfLib\LibObject;

/**
 * Class BaseObject: The base for all PDFlib wrapping objects.
 * @package Epubli\Pdf\PdfLib
 */
abstract class ScopedObject extends LibObject
{
    abstract protected function childScopeClosed();
}
