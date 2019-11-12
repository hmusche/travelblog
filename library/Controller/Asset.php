<?php

namespace TravelBlog\Controller;

use TravelBlog\Model\PostMedia;

use TravelBlog\Video;

use Solsken\Http;
use Solsken\Request;
use Solsken\Controller;
use Solsken\Registry;
use TravelBlog\Image;

use Leafo\ScssPhp\Compiler;

class Asset extends Controller {

    public function preDispatch() {
        Http::setCacheHeader();
    }

    public function postDispatch() {}

    public function postAction() {
        $postId = $this->_request->getParam('id');
        $file   = $this->_request->getParam('f');
        $size   = $this->_request->getParam('s', 'md');

        if ($postId && $file) {
            $path = Registry::get('app.config')['asset_path'] . DIRECTORY_SEPARATOR
                  . $postId . DIRECTORY_SEPARATOR;

            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $filetype = finfo_file($finfo, $path . $file);

            if (strpos($filetype, 'video') === 0) {
                $video = new Video;
                $video->stream($path . $file);
            } else {
                $file = Image::generateImage($postId, $file, $size);

                if (file_exists($file)) {
                    $filemtime = filemtime($file);
                    $headers   = $this->_request->get('headers');
                    $filetype  = finfo_file($finfo, $file);

                    if (isset($headers['HTTP_IF_MODIFIED_SINCE']) && strtotime($headers['HTTP_IF_MODIFIED_SINCE']) > $filemtime) {
                        http_response_code(304);
                    } else {
                        header('Content-Type: ' . $filetype);
                        header('Content-Length: ' . filesize($file));
                        header('Last-modified: ' . gmdate('D, d M Y H:i:s ', $filemtime) . 'GMT');

                        readfile($file);
                    }

                }
            }
        }

        exit;

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

        $assetPath = '';

        $jsFiles = [
            'jquery' => [
                'vendor/components/jquery/jquery.min.js',
                'vendor/twbs/bootstrap/dist/js/bootstrap.min.js',
            ],
            'base' => [
                'js/main.js'
            ]
        ];

        foreach ($parts as $part) {
            $assetPath .= "$part/";
            $jsFiles['base'][] = $assetPath . '/script.js';
        }

        $output = '';

        $lastMTime = 0;

        if (isset($jsFiles[$type])) {
            foreach ($jsFiles[$type] as $file) {
                if ($type == 'base') {
                    if (file_exists('template/' . $file)) {
                        $output .= $this->_view->partial($file);
                    }
                } else {
                    if (file_exists($file)) {
                        $output .= "\n" . file_get_contents($file);

                        if ($lastMTime < filemtime($file)) {
                            $lastMTime = filemtime($file);
                        }
                    }
                }
            }
        }

        header('Last-modified: ' . gmdate('D, d M Y H:i:s ', $lastMTime) . 'GMT');
        header('Content-Length: ' . strlen($output));

        echo $output;
    }

    public function cssAction() {
        header('Content-Type: text/css');

        $output = '';

        $cssFiles = [
            'vendor/twbs/bootstrap/dist/css/bootstrap.min.css',
            'vendor/components/font-awesome/css/fontawesome-all.min.css',
            'vendor/BlackrockDigital/startbootstrap-clean-blog/css/clean-blog.min.css',
            'vendor/noelboss/featherlight/src/featherlight.css',
            'vendor/noelboss/featherlight/src/featherlight.gallery.css'
        ];

        $lastMTime = 0;

        foreach ($cssFiles as $file) {
            $output .= file_get_contents($file);

            if ($lastMTime < filemtime($file)) {
                $lastMTime = filemtime($file);
            }
        }

        $scssPath = 'template/scss/';
        $scss     = new Compiler();
        $scss->setImportPaths($scssPath);

        $output .= $scss->compile('@import "main.scss";');

        header('Last-modified: ' . gmdate('D, d M Y H:i:s ', $lastMTime) . 'GMT');
        header('Content-Length: ' . strlen($output));

        echo $output;
    }
}
