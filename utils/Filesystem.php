<?php

namespace tn\phpmvc\utils;

use tn\phpmvc\Application;

class Filesystem
{
    public int $maxSize = 0;
    /**
     * @var array
     */
    public array $types = [];
    public static $destination_folder = 'media';

    public function upload($file = [])
    {
        if($this->types)
        if(!in_array($this->mimeType($file['tmp_name']),$this->types)) {
            return false;
        }
        if($this->maxSize)
        if(!$this->size($file['tmp_name'])>($this->maxSize)) {
            return false;
        }
        $file_name = sha1_file($file['tmp_name']);
        $ext = $this->extension($file['name']);

        try {
            move_uploaded_file(
                $file['tmp_name'],
                sprintf('%s/%s/%s.%s',
                    Application::$ROOT_DIR,
                    self::$destination_folder,
                    $file_name ,
                    $ext
                )
            );
        }
        catch (\Exception $e) {
            return false;
        }

        return self::$destination_folder.'/'.$file_name.'.'.$ext;

    }
    /**
     * Delete the file at a given path.
     *
     * @param  string|array  $paths
     * @return bool
     */
    public function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        $success = true;

        foreach ($paths as $path) {
            try {
                if (! @unlink($path)) {
                    $success = false;
                }
            } catch (ErrorException $e) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * @param $size
     */
    public function setMaxSize($size) {
        $this->maxSize = $size;
    }


    /**
     * @param array $types
     */
    public function allowedTypes(array $types = []) {
        $this->types = $types;
    }

    /**
     * @param string $dest
     */
    public function setDestFolder(string $dest){
        $this->destination_folder = $dest;
    }


    /**
     * Copy a file to a new location.
     *
     * @param  string  $path
     * @param  string  $target
     * @return bool
     */
    public function copy($path, $target)
    {
        return copy($path, $target);
    }

    /**
     * Extract the file name from a file path.
     *
     * @param  string  $path
     * @return string
     */
    public function name($path)
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Extract the trailing name component from a file path.
     *
     * @param  string  $path
     * @return string
     */
    public function basename($path)
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    /**
     * Extract the parent directory from a file path.
     *
     * @param  string  $path
     * @return string
     */
    public function dirname($path)
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * Extract the file extension from a file path.
     *
     * @param  string  $path
     * @return string
     */
    public function extension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Get the file type of a given file.
     *
     * @param  string  $path
     * @return string
     */
    public function type($path)
    {
        return filetype($path);
    }

    /**
     * Get the mime-type of a given file.
     *
     * @param  string  $path
     * @return string|false
     */
    public function mimeType($path)
    {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
    }

    /**
     * Get the file size of a given file.
     *
     * @param  string  $path
     * @return int
     */
    public function size($path)
    {
        return filesize($path);
    }

    public function getFile(string $file)
    {
        $file = Application::$ROOT_DIR.'/'.$file;
        $mime_type = $this->mimeType($file);
        header('Content-Type: '.$mime_type);
        readfile($file);
    }

}