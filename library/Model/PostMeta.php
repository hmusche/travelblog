<?php

namespace TravelBlog\Model;

use Solsken\Model;
use Solsken\Util;

use Medoo\Medoo;

class PostMeta extends Model {
    protected $_name = 'post_meta';

    static protected $_metaKeys = ['tag'];

    static public function getMetaKeys() {
        return self::$_metaKeys;
    }

    public function getCountries($states = ['active']) {
        $countries = $this->select([
            '[>]post' => [
                'post_id' => 'id'
            ]
        ], [
            'value(country_code)',
            'count'   => Medoo::raw('COUNT(*)'),
            'updated' => Medoo::raw('MAX(<post.updated>)')
        ], [
            'type' => 'country',
            'GROUP' => 'value',
            'ORDER' => 'post_id',
            'post.status' => $states
        ]);

        return $countries;
    }

    public function getTags($states = ['active']) {
        $tags = $this->select([
            '[>]post' => [
                'post_id' => 'id'
            ]
        ], [
            'value',
            'count'   => Medoo::raw('COUNT(*)'),
            'updated' => Medoo::raw('MAX(<post.updated>)')
        ], [
            'type' => 'tag',
            'GROUP' => 'value',
            'ORDER' => 'value',
            'post.status' => $states
        ]);

        return $tags;
    }

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
            return isset($return[$type]) ? $return[$type] : [];
        }

        return $return;
    }

    public function setPostMetaType($postId, $type, $values) {
        if (!is_array($values)) {
            $values = explode(',', $values);
        }

        $values = array_map('trim', $values);
        $values = array_map('strtolower', $values);
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
