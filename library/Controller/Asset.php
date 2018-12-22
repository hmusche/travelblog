<?php

namespace TravelBlog\Controller;

use TravelBlog\Http;
use TravelBlog\Request;
use TravelBlog\Controller;

class Asset extends Controller {

    public function preDispatch() {
        Http::setCacheHeader();
    }

    public function postDispatch() {}

    public function jsAction() {
        header('Content-Type: application/javascript');
        $req   = Request::getInstance();
        $parts = [
            $req->getParam('c'),
            $req->getParam('a')
        ];

        $assetPath = 'template/';

        $jsFiles = [
            'vendor/components/jquery/jquery.slim.min.js',
            'vendor/twbs/bootstrap/dist/js/bootstrap.min.js'
        ];

        foreach ($parts as $part) {
            $assetPath .= "$part/";
            $jsFiles[] = $assetPath . '/script.js';
        }

        $output = '';

        foreach ($jsFiles as $file) {
            if (file_exists($file)) {
                $output .= file_get_contents($file);
            }
        }

        echo $output;
    }

    public function cssAction() {
        header('Content-Type: text/css');

        $cssFiles = [
            'vendor/twbs/bootstrap/dist/css/bootstrap.min.css'
        ];

        $output = '';

        foreach ($cssFiles as $file) {
            $output .= file_get_contents($file);
        }

        echo $output;
    }
}
