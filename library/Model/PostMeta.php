<?php

namespace TravelBlog\Model;

use Solsken\Model;
use Solsken\Util;

use Medoo\Medoo;

class PostMeta extends Model {
    protected $_name = 'post_meta';

    public function getMeta($postId, $type = null) {
        $values = $this->select(['type', 'value'], ['post_id' => $postId]);
        $return = [];

        foreach ($values as $value) {
            if (!isset($return[$value['type']])) {
                $return[$value['type']] = [];
            }

            $return[$value['type']][] = $value['value'];
        }

        if ($type !== null) {
            return isset($return['type']) ? $return['type'] : [];
        }

        return $return;
    }

    public function setPostMetaType($postId, $type, $values) {
        if (!is_array($values)) {
            $values = [$values];
        }

        $values = array_unique($values);

        $current = $this->select(['id', 'value'], [
            'post_id' => $postId,
            'type'    => $type
        ]);

        foreach ($current as $row) {
            if (!in_array($row['value'], $values)) {
                $this->delete(['id' => $row['id']]);
            } else {
                unset($values[array_search($row['value'], $values)]);
            }
        }

        foreach ($values as $value) {
            $this->insert([
                'post_id' => $postId,
                'type' => $type,
                'value' => $value
            ]);
        }
    }
}
