<?php

namespace TravelBlog\Controller;

use TravelBlog\Model\PostMedia;

use Solsken\Http;
use Solsken\Request;
use Solsken\Controller;
use Solsken\Registry;
use Solsken\Image;

use Leafo\ScssPhp\Compiler;

class Asset extends Controller {

    public function preDispatch() {
        Http::setCacheHeader();
    }

    public function postDispatch() {}

    public function postAction() {
        $postId = $this->_request->getParam('id');
        $file   = $this->_request->getParam('f');
        $size   = $this->_request->getParam('s', 'p');

        $allowedSizes = [
            'p' => 800, // preview
            't' => 300,  // thumbnail
            'o' => 1920     // original
        ];

        if (!isset($allowedSizes[$size])) {
            http_response_code(404);
            exit;
        }

        if ($postId && $file) {
            $path = Registry::get('app.config')['asset_path'] . DIRECTORY_SEPARATOR
                  . $postId . DIRECTORY_SEPARATOR;

            $subPath = $path;
            $subPath .= $size . DIRECTORY_SEPARATOR;

            if (!file_exists($subPath . $file)) {
                if (!file_exists($path . $file)) {
                    http_response_code(404);
                    return;
                }

                $resized = Image::resize($path . $file, $subPath . $file, $allowedSizes[$size]);
            } else {
                $resized = true;
            }

            if ($resized) {
                $path = $subPath;
            }

            if (file_exists($path . $file)) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                header('Content-Type: ' . finfo_file($finfo, $path . $file));

                readfile($path . $file);
                exit;
            }
        }

        //http_response_code(404);
    }

    public function jsAction() {
        header('Content-Type: application/javascript');

        $req   = Request::getInstance();
        $type  = $req->getParam('t', 'base');
        $parts = [
            $req->getParam('c'),
            $req->getParam('a')
        ];

        $assetPath = 'template/';

        $jsFiles = [
            'jquery' => [
                'vendor/components/jquery/jquery.slim.min.js',
                'vendor/twbs/bootstrap/dist/js/bootstrap.min.js'
            ],
            'base' => []
        ];

        foreach ($parts as $part) {
            $assetPath .= "$part/";
            $jsFiles['base'][] = $assetPath . '/script.js';
        }

        $output = '';

        if (isset($jsFiles[$type])) {
            foreach ($jsFiles[$type] as $file) {
                if (file_exists($file)) {
                    $output .= "\n".file_get_contents($file);
                }
            }
        }

        echo $output;
    }

    public function cssAction() {
        header('Content-Type: text/css');

        $output = '';

        $cssFiles = [
            'vendor/twbs/bootstrap/dist/css/bootstrap.min.css',
            'vendor/components/font-awesome/css/fontawesome-all.min.css',
            'vendor/BlackrockDigital/startbootstrap-clean-blog/css/clean-blog.min.css'
        ];

        foreach ($cssFiles as $file) {
            $output .= file_get_contents($file);
        }

        $scssPath = 'template/scss/';
        $scss     = new Compiler();
        $scss->setImportPaths($scssPath);

        $output .= $scss->compile('@import "main.scss";');

        echo $output;
    }
}
