<?php

namespace File;

/**
 * Various File system related helper functions
 *
 * @author matthewfl
 */
class FileSystemHelper
{

    /**
     *
     * @var \Monolog\Logger
     */
    protected $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Create a dir if it does exist and all required parent directories
     * @param string $dir
     * @param string $perms permissions
     * @throws \Exception if unable to create dir
     */
    public function createDir($dir, $perms = 0777)
    {
        if (!file_exists($dir)) {
            $success = mkdir($dir, $perms, true);
            if (!$success) {
                throw new \Exception("unable to create directory '$dir'");
            }
        }
    }

    /**
     * Create all required parent dirs of a path if they don't exist
     * @param string $path a file path
     * @param string $perms permissions
     * @throws \Exception if unable to create dir
     */
    public function createParentDirs($path, $perms = 0777)
    {
        return $this->createDir(dirname($path), $perms);
    }

    /**
     * Returns true if directory is empty, false otherwise
     * @param string $dir directory path
     * @return bool
     * @throws \Exception if $dir does not exist, is unreadable or not a directory
     */
    public function isDirEmpty($dir)
    {
        $this->ensureDirValidAndReadable($dir);
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                return false;
            }
        }
        return true;
    }

    /**
     * Return the number of files in a directory
     *
     * @param string $dir
     * @param bools $includeDirs count directories if true
     * @return int
     * @throws \Exception if $dir does not exist, is unreadable or not a directory
     */
    public function fileCount($dir, $includeDirs = false)
    {
        $this->ensureDirValidAndReadable($dir);
        $fsi = new \FilesystemIterator($dir, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
        if ($includeDirs) {
            return iterator_count($fsi);
        }
        $count = 0;
        foreach ($fsi as $file) {
            if (is_file($file)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Returns true if a directory is unmodified for $minUnmodified seconds - does not check recursively
     * @param string $dir directory path
     * @param int $minUnmodified
     * @return bool
     * @throws \Exception if $dir does not exist
     */
    public function isDirStable($dir, $minUnmodified)
    {
        $this->ensureDirValidAndReadable($dir);
        clearstatcache(false, $dir);
        $stats = stat($dir);
        $secondsSinceMod = time() - $stats['mtime'];
        $this->logger->debug("seconds since modfied: $secondsSinceMod");
        return ($secondsSinceMod >= $minUnmodified);
    }

    public function isOlderThan($path, \DateTime $date)
    {
        $this->ensureExistsAndReadable($path);
        clearstatcache(false, $path);
        $modified = new \DateTime();
        $modified->setTimestamp(filemtime($path));
        $formatted = $modified->format('Y-m-d h:i:s');
        $this->logger->debug("$path last modified $formatted");
        return ($modified < $date);
    }

    /**
     * Moves the contents of one directory to another, optionally overwriting existing files.
     * If the parent dirs of $to do not exist they will be created.
     * @param string $fromDir dir
     * @param string $toDir dir
     * @param int $perms octal file permission for new directories
     * @throws \Exception if $dir does not exist, is unreadable or not a directory
     */
    public function moveContents($fromDir, $toDir, $recurse = true, $overWrite = false, $perms = 0777)
    {
        $this->logger->info("start moving contents of $fromDir to $toDir");
        $this->ensureDirValidAndReadable($fromDir);
        $this->createDir($toDir, $perms);
        $this->ensureDirValidAndWriteable($toDir);

        $fsi = new \FilesystemIterator($fromDir, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);

        foreach ($fsi as $fromPath) {
            $toPath = "$toDir/" . basename($fromPath);
            if (is_dir($fromPath)) {
                if ($recurse) {
                    $this->moveContents($fromPath, $toPath, $recurse, $overWrite, $perms);
                    rmdir($fromPath);
                } else {
                    $this->logger->warning("$fromPath is a directory - skipping move");
                }
                continue;
            }

            if (file_exists($toPath)) {
                if ($overWrite) {
                    $this->ensureFileValidAndWriteable($toPath);
                } else {
                    $this->logger->warning("$toPath exists - skipping move");
                }
            }

            $success = rename($fromPath, $toPath);
            if (!$success) {
                throw new \Exception("unable to move file '$fromPath' to dir '$toDir'");
            }
        }
        $this->logger->info("finish moving contents of $fromDir to $toDir");
    }

    /**
     * Move a directory from one path to another, and optionally recreate the $from dir.
     * If the parent dirs of $to do not exist they will be created.
     * @param string $from
     * @param string $to
     * @param bool $recreateFrom
     * @param int $perms octal file permission for new directories
     * @throws \Exception if $dir does not exist, is unreadable or not a directory
     */
    public function moveDir($from, $to, $recreateFrom = false, $perms = 0777)
    {
        $this->ensureDirValidAndReadable($from);
        $this->createParentDirs($to, $perms);
        $this->ensureDirValidAndWriteable(dirname($to));

        $success = rename($from, $to);
        if (!$success) {
            throw new \Exception("unable to move dir '$from' to '$to'");
        }
        if ($recreateFrom) {
            mkdir($from, $perms, true);
        }
    }

    /**
     * Move a file to a new directory. If the parent dirs of $to do not exist they will be created.
     * @param string $from path to file
     * @param string $toDir directory to move to
     * @param bool $overWrite overwrite existing file
     * @param int $perms octal file permission for new directories
     * @throws \Exception if $dir does not exist, is unreadable or not a directory
     */
    public function moveFile($from, $toDir, $overWrite = false, $perms = 0777)
    {
        $this->ensureFileValidAndReadable($from);
        $this->createDir($toDir, $perms);
        $this->ensureDirValidAndWriteable($toDir);

        $toPath = "$toDir/" . basename($from);
        if (file_exists($toPath)) {
            if ($overWrite) {
                $this->ensureFileValidAndWriteable($toPath);
            } else {
                throw new \Exception("$toDir already exists");
            }
        }

        $success = rename($from, $toPath);
        if (!$success) {
            throw new \Exception("unable to move file '$from' to dir '$toDir'");
        }
    }

    /**
     * Removes a dir if it is empty and recursively remove empty child dirs if $recursive is true
     * Returns true if $dir is removed
     * @param string $dir
     * @param bool $recursive
     * @return bool
     * @throws \Exception if $dir does not exist, is not writeable or not a dir
     */
    public function removeEmptyDir($dir, $recursive = true)
    {
        $this->ensureDirValidAndWriteable($dir);
        if ($recursive) {
            $fsi = new \FilesystemIterator($dir, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
            foreach ($fsi as $file) {
                if (is_dir($file)) {
                    $this->removeEmptyDir($file, true);
                }
            }
        }
        $this->logger->debug("removing dir $dir");
        return @rmdir($dir);
    }

    /**
     * Recursivley removes a dir and all contents
     * Returns true if $dir is removed
     * @param string $dir must be canonical absolute path (realpath())
     * @return bool
     * @throws \Exception if $dir does not exist, is not readable or not a dir, or an empty string
     */
    public function removeDir($dir)
    {
        if (!$dir || ($dir !== realpath($dir))) {
            throw new \Exception("\$dir '$dir' must be canonical absolute path (realpath())");
        }
        $this->ensureDirValidAndWriteable($dir);

        $fsi = new \FilesystemIterator($dir, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
        foreach ($fsi as $path) {
            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                $this->logger->debug("removing file '$path'");
                unlink($path);
            }
        }
        $this->logger->debug("removing dir '$dir'");
        return @rmdir($dir);
    }

    /**
     * Throws an exception if $path does not exist, or is not readable
     * @param string $path
     * @throws \Exception
     */
    protected function ensureExistsAndReadable($path)
    {
        if (!file_exists($path)) {
            throw new \Exception("'$path' does not exist");
        }
        if (!is_readable($path)) {
            throw new \Exception("'$path' is not readable");
        }
    }

    /**
     * Throws an exception if $path does not exist, or is not writeable
     * @param string $path
     * @throws \Exception
     */
    protected function ensureExistsAndWriteable($path)
    {
        if (!file_exists($path)) {
            throw new \Exception("'$path' does not exist");
        }
        if (!is_writeable($path)) {
            throw new \Exception("'$path' is not writeable");
        }
    }

    /**
     * Throws an exception if $dir does not exist, is not readable or not a dir
     * @param string $dir
     * @throws \Exception
     */
    protected function ensureDirValidAndReadable($dir)
    {
        $this->ensureExistsAndReadable($dir);
        if (!is_dir($dir)) {
            throw new \Exception("'$dir' is not a directory");
        }
    }

    /**
     * Throws an exception if $dir does not exist, is not writeable or not a dir
     * @param string $dir
     * @throws \Exception
     */
    protected function ensureDirValidAndWriteable($dir)
    {
        $this->ensureExistsAndWriteable($dir);
        if (!is_dir($dir)) {
            throw new \Exception("'$dir' is not a directory");
        }
    }

    /**
     * Throws an exception if $file does not exist, is not readable or not a file
     * @param string $file
     * @throws \Exception
     */
    protected function ensureFileValidAndReadable($file)
    {
        $this->ensureExistsAndReadable($file);
        if (!is_file($file)) {
            throw new \Exception("'$file' is not a file");
        }
    }

    /**
     * Throws an exception if $file does not exist, is not writeable or not a file
     * @param string $file
     * @throws \Exception
     */
    protected function ensureFileValidAndWriteable($file)
    {
        $this->ensureExistsAndWriteable($file);
        if (!is_file($file)) {
            throw new \Exception("'$file' is not a file");
        }
    }

    /**
     * Returns true if a zipfile is valid, false otherwise
     * @param string $path
     * @return bool
     */
    public function validateZipFile($path)
    {
        $zip = new \ZipArchive();
        return ($zip->open($path, \ZipArchive::CHECKCONS) === TRUE);
    }

}
