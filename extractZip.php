<?php

/*
 * this script will extract only images from a zip file and skip all other files. 
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * Makes directory and returns BOOL(TRUE) if exists OR made.
 *
 * @param  $path Path name
 * @return bool
 */
function rmkdir($path, $mode = 0755) {
    $path = rtrim(preg_replace(array("/\\\\/", "/\/{2,}/"), "/", $path), "/");
    $e = explode("/", ltrim($path, "/"));
    if (substr($path, 0, 1) == "/") {
        $e[0] = "/" . $e[0];
    }
    $c = count($e);
    $cp = $e[0];
    for ($i = 1; $i < $c; $i++) {
        if (!is_dir($cp) && !@mkdir($cp, $mode)) {
            return false;
        }
        $cp .= "/" . $e[$i];
    }
    return @mkdir($path, $mode);
}

$dir = __DIR__;
$extractedFilesDir = $dir . "/images";
$zipFiles = glob($dir . "/*.zip");
if (is_array($zipFiles) && count($zipFiles) > 0) {
    foreach ($zipFiles as $zipFile) {
        if (file_exists($zipFile)) {
            $zipFolderName = basename($zipFile);
            $zipFileName = basename($zipFile, '.zip');
            $fieDir = $extractedFilesDir . '/' . $zipFileName;
            
            if (class_exists('ZipArchive')) {
                $zip = new ZipArchive;
                $result = $zip->open($zipFile, ZipArchive::CHECKCONS);
                if ($result === TRUE) {
                    $noImageFound = true;
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $onlyFileName = $zip->getNameIndex($i);
                        $fileType = mime_content_type('zip://' . $zipFile . '#' . $onlyFileName);
                        $fileType = strtolower($fileType);
                        if (($fileType == 'image/png' || $fileType == 'image/jpeg' || $fileType == 'image/gif' || $fileType == 'image/svg') && (preg_match('#\.(SVG|svg|jpg|jpeg|JPEG|JPG|gif|GIF|png|PNG)$#i', $onlyFileName))) {
                            copy('zip://' . $zipFile . '#' . $onlyFileName, $fieDir . '/' . $onlyFileName);
                            $noImageFound = false;
                            echo 'extracted the image ' . $onlyFileName . ' from ' . $zipFile . ' to ' . $fieDir . '<br />';
                        }
                    }
                    if ($noImageFound) {
                        echo 'There is no images in zip file ' . $zipFolderName . '<br />';
                    }
                } else {
                    switch ($result) {
                        case ZipArchive::ER_NOZIP:
                            echo 'Not a zip archive ' . basename($zipFolderName);
                        case ZipArchive::ER_INCONS:
                            echo 'Consistency check failed ' . basename($zipFolderName);
                        case ZipArchive::ER_CRC:
                            echo 'checksum failed ' . basename($zipFolderName);
                        default:
                            echo 'Error occured while extracting file ' . basename($zipFolderName);
                    }
                }
                $zip->close();
            } else {
                echo 'Zip Library is not installed in your server, please installed it to process zip files' . '<br />';
            }
        }
    }
}
