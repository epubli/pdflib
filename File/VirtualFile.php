<?php

namespace Epubli\Pdf\PdfLib\File;

use Epubli\Pdf\PdfLib\LibObject;
use PDFlibException;

/**
 * Class VirtualFile: A wrapper for a virtual file managed by PDFlib.
 * @package Epubli\Pdf\PdfLib
 * @author Simon Schrape <simon@epubli.com>
 */
class VirtualFile extends LibObject
{
    /** @var string The virtual filename */
    private $name;

    /**
     * VirtualFile constructor.
     * This is kept private since instances may only be created by self::create which might throw exceptions.
     * @param \PDFlib $lib
     * @param string $name
     */
    protected function __construct(\PDFlib $lib, $name)
    {
        parent::__construct($lib);
        $this->name = $name;
    }

    public function __destruct()
    {
        $this->delete();
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @param \PDFlib $lib
     * @param string $name
     * @param string $data
     * @return VirtualFile
     * @throws PDFlibException
     */
    public static function create(\PDFlib $lib, $name, $data)
    {
        $lib->create_pvf($name, $data, '');

        return new self($lib, $name);
    }

    /**
     * Delete the virtual file and free its data structures (but not the contents).
     * @return bool false if the virtual file exists but is locked, and true otherwise.
     */
    public function delete()
    {
        $success = true;
        if ($this->name) {
            $success = (bool)$this->getLib()->delete_pvf($this->name);
            // Prevent double deletion.
            $this->name = null;
        }

        return $success;
    }
}