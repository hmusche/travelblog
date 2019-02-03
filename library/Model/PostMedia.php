<?php


namespace TravelBlog\Model;

use Solsken\Model;
use Solsken\Util;
use Solsken\Registry;

use Medoo\Medoo;

class PostMedia extends Model {
    protected $_name = 'post_media';
    protected $_savePath;

    public function __construct() {
        parent::__construct();
        $this->_savePath = Registry::get('app.config')['asset_path'];
    }

    public function getMedia($postId) {
        return $this->select([
                'filename',
                'name',
                'post_id',
                'sort',
                'full_path' => Medoo::raw("CONCAT('asset/post/s/{size}/id/', <post_id>, '/f/', <filename>)")
            ], [
            'post_id' => $postId,
            'ORDER' => [
                'sort' => 'ASC'
            ]
        ]);
    }

    public function deleteMedia($postId, $filename) {
        $path = $this->_savePath . $postId . DIRECTORY_SEPARATOR;

        $paths = [
            $path
        ];

        foreach (['o', 'p', 't'] as $size) {
            $paths[] = $path . $size . DIRECTORY_SEPARATOR;
        }

        foreach ($paths as $path) {
            if (file_exists($path . $filename)) {
                unlink($path . $filename);
            }
        }

        return $this->delete([
            'post_id' => $postId,
            'filename' => $filename
        ]);

    }

    public function handleUpload($postId, $uploads) {
        $files = [];

        if (isset($uploads['name'])) {
            foreach ($uploads['name'] as $index => $name) {
                $suffix   = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $filename = Util::getUniqueId() . '.' . $suffix;
                $path     = $this->_savePath . $postId . DIRECTORY_SEPARATOR;



                if (isset($uploads['error'][$index]) && $uploads['error'][$index]) {
                    throw new \Exception('Error ' . $uploads['error'][$index] . ' while uploading');
                }

                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }

                $result = move_uploaded_file($uploads['tmp_name'][$index], $path . $filename);

                if (!$result) {
                    return false;
                } else {
                    $res = $this->insert([
                        'post_id' => $postId,
                        'filename' => $filename,
                        'name' => $name,
                        'type' => mime_content_type($path . $filename),
                        'sort' => 0
                    ]);
                }
            }
        }
    }
}
