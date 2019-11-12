<?php

namespace TravelBlog;

use Solsken\Image as I;

class Image {
    const ALLOWED_WIDTHS = [
        'share' => 400,
        'xs' => 300,
        'sm' => 600,
        'md' => 900,
        'lg' => 1200,
        'xl' => 1920,
        'o' => 10000     // original
    ];

    const ALLOWED_HEIGHTS = [
        'share' => 400
    ];

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

            if (strpos($filetype, 'video') !== 0) {
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
