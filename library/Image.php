<?php

namespace TravelBlog;

use Solsken\Image as I;
use Solsken\Registry;

class Image {
    const ALLOWED_WIDTHS = [
        'share' => 400,
        'xs' => 300,
        'sm' => 600,
        'md' => 900,
        'lg' => 1200,
        'xl' => 1920,
        'o' => null     // original
    ];

    const ALLOWED_HEIGHTS = [
        'share' => 400
    ];

    static public function getFilesToResize($postId, $files) {
        $toResize = [];

        foreach ($files as $file) {
            foreach (self::ALLOWED_WIDTHS as $size => $width) {
                if ($width) {
                    $path = Registry::get('app.config')['asset_path']
                          . $postId . DIRECTORY_SEPARATOR
                          . $size . DIRECTORY_SEPARATOR
                          . $file;

                    if (!file_exists($path)) {
                        if (!isset($toResize[$file])) {
                            $toResize[$file] = [];
                        }

                        $toResize[$file][] = $size;
                    }
                }
            }
        }

        return $toResize;
    }

    static public function generateImage($postId, $file, $size) {
        $path = Registry::get('app.config')['asset_path']
              . $postId . DIRECTORY_SEPARATOR;

        $subPath  = $path;

        if (self::ALLOWED_WIDTHS[$size]) {
            $subPath  .= $size . DIRECTORY_SEPARATOR;
        }

        if (!file_exists($subPath . $file)) {
            if (self::resize($path . $file, $subPath . $file, $size) === false) {
                throw new \Exception('Image could not be resized', 500);
            }
        }

        return $subPath . $file;
    }

    static public function resize($oldFile, $newFile, $size) {
        if (!isset(self::ALLOWED_WIDTHS[$size])) {
            return false;
        }

        $finfo   = finfo_open(FILEINFO_MIME_TYPE);
        $resized = false;

        if (!file_exists($newFile)) {
            if (!file_exists($oldFile)) {
                return false;
            }

            $filetype = finfo_file($finfo, $oldFile);

            if (strpos($filetype, 'video') === 0) {
                //This should be checked before handing the file to this class
                return null;
            } else {
                $tmpFile = '/tmp/solsken_resizer_' . uniqid();
                $resized = I::resize(
                    $oldFile,
                    $tmpFile,
                    self::ALLOWED_WIDTHS[$size],
                    isset(self::ALLOWED_HEIGHTS[$size]) ? self::ALLOWED_HEIGHTS[$size] : null
                );

                if ($resized) {
                    if (!file_exists(dirname($newFile))) {
                        mkdir(dirname($newFile), 0755, true);
                    }

                    rename($tmpFile, $newFile);
                }
            }
        } else {
            $resized = true;
        }

        return $resized;
    }
}
