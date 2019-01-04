<?php


namespace TravelBlog\Model;

use Solsken\Model;
use Solsken\Util;
use Solsken\Registry;

class PostMedia extends Model {
    protected $_name = 'post_media';
    protected $_savePath;

    public function __construct() {
        parent::__construct();
        $this->_savePath = Registry::get('app.config')['asset_path'];
    }

    public function getMedia($postId) {
        return $this->select(['filename', 'name'], [
            'post_id' => $postId,
            'ORDER' => [
                'sort' => 'DESC'
            ]
        ]);
    }

    public function handleUpload($postId, $uploads) {
        $files = [];

        if (isset($uploads['name'])) {
            foreach ($uploads['name'] as $index => $name) {
                $suffix   = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $filename = Util::getUniqueId() . '.' . $suffix;
                $path     = $this->_savePath . $postId . DIRECTORY_SEPARATOR;

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
