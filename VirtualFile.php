<?php

namespace Epubli\Pdf\PdfLib;

use Epubli\Common\Tools\StringTools;

/**
 * Class VirtualFile: A wrapper for a virtual file managed by PDFLib.
 * @package Epubli\Pdf\PdfLib
 * @author Simon Schrape <simon@epubli.com>
 */
class VirtualFile
{
    /** @var \PDFlib The PDFLib bridge this object belongs to. */
    private $lib;

    /** @var string The virtual filename */
    private $name;

    /**
     * VirtualFile constructor.
     * This is kept private since instances may only be created by self::create which might throw exceptions.
     * @param \PDFlib $lib
     * @param string $name
     */
    private function __construct(\PDFlib $lib, $name)
    {
        $this->lib = $lib;
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
     * @param string $namePrefix The actual filename will start with
     * @param string $data
     * @param string $options
     * @return VirtualFile
     * @throws Exception
     */
    public static function create(\PDFlib $lib, $namePrefix, $data, $options)
    {
        $name = $namePrefix;
        $counter = 0;
        do {
            $alreadyExists = false;
            try {
                $lib->create_pvf($name, $data, $options);
            } catch (\Exception $ex) {
                $msg = $ex->getMessage();
                if (StringTools::contains($msg, 'already exists')) {
                    // Handle "Couldn't create virtual file '…' (name already exists)":
                    $alreadyExists = true;
                    $name = $namePrefix . '.' . ++$counter;
                } else {
                    // Cannot handle different exceptions here.
                    throw new Exception("Could not create virtual file $name ($msg)", 0, $ex);
                }
            }
        } while ($alreadyExists);

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
            $success = (bool)$this->lib->delete_pvf($this->name);
            // Prevent double deletion.
            $this->name = null;
        }

        return $success;
    }
}