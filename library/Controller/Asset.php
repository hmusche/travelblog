<?php

namespace TravelBlog\Controller;

use Solsken\Http;
use Solsken\Request;
use Solsken\Controller;

use Leafo\ScssPhp\Compiler;

class Asset extends Controller {

    public function preDispatch() {
        Http::setCacheHeader();
    }

    public function postDispatch() {}

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
        ];

        foreach ($parts as $part) {
            $assetPath .= "$part/";
            $jsFiles[] = $assetPath . '/script.js';
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
