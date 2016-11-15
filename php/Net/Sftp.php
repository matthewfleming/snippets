<?php

namespace Net;

use Monolog\Logger;
use phpseclib\Net\SFTP;

/**
 * Description of SortPlanParser
 *
 * @author matthewfl
 */
class Sftp
{

    const MAX_RETRIES = 10;
    const CHUNKS = 12;
    const CHUNK_MIN = 1048576;
    const CHUNK_MAX = 10485760;

    protected $retries = 0;
    protected static $initalRetryDelays = [1, 2, 3, 5, 10, 30];

    /**
     *
     * @var \phpseclib\Net\SFTP
     */
    protected $sftp;

    /**
     *
     * @var Logger
     */
    protected $logger;

    /**
     * SFTP host
     * @var string
     */
    protected $host;

    /**
     * SFTP user
     * @var string
     */
    protected $user;

    /**
     * SFTP password
     * @var string
     */
    protected $pass;

    /**
     * Current remote working dir
     * @var string
     */
    protected $cwd;

    public function __construct($host, $user, $pass, $logger)
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->logger = $logger;
        $this->connect($host, $user, $pass);
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    protected function connect()
    {
        do {
            try {
                $sftp = new SFTP($this->host);
                $success = $sftp->login($this->user, $this->pass);
            } catch (Exception $e) {
                $this->logger->error("exception thrown when connecting to SFTP server '{$this->host}'", ['exception' => $e]);
                $success = false;
            }
            if ($success) {
                $this->sftp = $sftp;
                $this->retries = 0;
            } else {
                $delay = $this->getRetryDelay();
                $this->logger->error("failed to connect to SFTP server '{$this->host}' - retrying in $delay seconds");
                sleep($delay);
            }
        } while (!$success);
    }

    protected function reconnect()
    {
        if ($this->sftp) {
            $this->sftp->disconnect();
            $this->sftp = null;
            $this->connect();
            $this->logger->info("sucessful reconnect to SFTP server '{$this->host}'");
            if (!$this->sftp->chdir($this->cwd)) {
                throw new \Exception("unable to change to previous working directory on reconnect to SFTP server '{$this->host}'");
            }
        }
    }

    protected function getRetryDelay()
    {
        $retries = $this->retries++;
        if ($retries > self::MAX_RETRIES) {
            $message = "Exceeded maximum retries for SFTP server '$this->host'";
            throw new \Exception($message);
        }
        if (isset(self::$initalRetryDelays[$retries])) {
            return self::$initalRetryDelays[$retries];
        }
        $initialRetries = count(self::$initalRetryDelays);
        if ($retries === $initialRetries) {
            array_sum(self::$initalRetryDelays);
            $this->logger->alert("Exceeded initial $initialRetries retries for SFTP server '$this->host', backing off further");
        }
        return ($retries - $initialRetries + 1) * 60;
    }

    protected function getLastError()
    {
        return 'SSH: "' . $this->sftp->getLastError() . '" SFTP: "' . $this->sftp->getLastSFTPError() . '"';
    }

    /**
     * Upload a file to current working directory on remote
     * Returns true if file uploaded or false if skipped i.e. if the file exists & ($overwrite = false)
     * @param string $localPath
     * @param string $overWrite
     * @return boolean
     * @throws \Exception
     */
    public function uploadFileToCwd($localPath, $overWrite = false)
    {
        if (!file_exists($localPath)) {
            throw new \Exception("cannot upload file $localPath as it does not exist");
        }
        do {
            try {
                $baseName = basename($localPath);
                if ($this->sftp->file_exists($baseName)) {
                    if ($overWrite) {
                        $success = $this->sftp->delete($baseName);
                        if (!$success) {
                            throw new \Exception("unable to delete remote file $baseName to overwrite " . $this->getLastError());
                        }
                    } else {
                        $this->logger->notice("skipping local file $localPath upload as it already exists on remote");
                        return false;
                    }
                }
                $tempName = "$baseName.part";
                $success = $this->sftp->put($tempName, $localPath, SFTP::SOURCE_LOCAL_FILE);
                if ($success) {
                    $success = $this->sftp->rename($tempName, $baseName);
                    if (!$success) {
                        $this->logger->error("failed renaming temp file $tempName to $baseName on SFTP server", ['error' => $this->getLastError()]);
                    }
                } else {
                    $this->logger->error("failed uploading file $localPath to SFTP server", ['error' => $this->getLastError()]);
                }
            } catch (\Exception $e) {
                $this->logger->error("unexpected exception during upload to SFTP server", ['exception' => $e]);
                $success = false;
            }
            if ($success) {
                $this->retries = 0;
            } else {
                $delay = $this->getRetryDelay();
                $this->logger->error("failed uploading file to SFTP server - retrying in $delay seconds");
                sleep($delay);
                $this->logger->info("reconnecting to sftp server after failure");
                $this->reconnect();
            }
        } while (!$success);

        return true;
    }

    public function chdir($dir)
    {
        $result = $this->sftp->chdir($dir);
        if ($result) {
            $this->cwd = $dir;
        }
        return $result;
    }

    public function disconnect()
    {
        return $this->sftp->disconnect();
    }

    public function fileExists($file)
    {
        return $this->sftp->file_exists($file);
    }

    protected function getChunkSize($fileSize)
    {
        $chunk = intval($fileSize / self::CHUNKS);
        $chunk += $chunk % 4096;
        if ($chunk > self::CHUNK_MAX) {
            $chunk = self::CHUNK_MAX;
        }
        if ($chunk < self::CHUNK_MIN) {
            $chunk = self::CHUNK_MIN;
        }
        if ($chunk > $fileSize) {
            return $fileSize;
        }
        return $chunk;
    }

    public function get($remoteFile, $localFile)
    {
        $size = $this->sftp->filesize($remoteFile);
        $length = $this->getChunkSize($size);
        $offset = 0;
        $incomplete = true;
        $fp = fopen($localFile, 'w');
        if (!$fp) {
            throw new \Exception("unable to open $localFile for writing");
        }
        do {
            $buffer = $this->sftp->get($remoteFile, false, $offset, $length);
            if (empty($buffer)) {
                fclose($fp);
                return false;
            }

            $bytes = fwrite($fp, $buffer);
            if ($bytes === false) {
                fclose($fp);
                throw new \Exception("error writing file $localFile");
            }
            fflush($fp);

            $offset += $length;
            if ($offset > $size) {
                $this->logger->info("getting $remoteFile 100% complete");
                $incomplete = false;
            } else {
                $complete = sprintf('%04.1f', floor(1000 * $offset / $size) / 10);
                $this->logger->info("getting $remoteFile $complete% complete");
            }
        } while ($incomplete);

        fclose($fp);
        return true;
    }

    public function mkdir(
    $dir, $mode, $recursive)
    {
        return $this->sftp->mkdir($dir, $mode, $recursive);
    }

    public function read(
    $expect, $mode)
    {
        return $this->sftp->read($expect, $mode);
    }

}
