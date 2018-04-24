<?php

namespace Epubli\Pdf\PdfLib;

/**
 * Class BaseObject: The base for all PDFlib wrapping objects.
 * @package Epubli\Pdf\PdfLib
 */
abstract class LibObject
{
    /** @var \PDFlib The wrapped PDFlib object. */
    private $lib;

    protected function __construct(\PDFlib $lib)
    {
        $this->lib = $lib;
    }

    /**
     * @return \PDFlib
     */
    protected function getLib()
    {
        return $this->lib;
    }

    protected function getCurrentScope()
    {
        return $this->lib->get_option('scope', '');
    }

    /**
     * Get the text of the last thrown exception or the reason of a failed function call.
     *
     * @return string Text containing the description of the most recent error condition.
     */
    protected function getLastErrorMessage()
    {
        return $this->lib->get_errmsg();
    }

    /**
     * @param string $callerMessage
     * @throws Exception
     */
    protected function throwLastError($callerMessage)
    {
        throw new Exception($callerMessage . ' ' . $this->getLastErrorMessage());
    }
}
