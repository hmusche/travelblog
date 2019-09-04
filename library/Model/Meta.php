<?php

namespace TravelBlog\Model;

use Solsken\I18n;
use Solsken\Model;
use TravelBlog\Content;

class Meta extends Model {
    protected $_name = 'meta';

    public function getMetaById($id) {
        return $this->_format($this->getMeta(['id' => $id]));
    }

    public function getMetaByTypeAndValue($type, $value) {
        $locale = I18n::getInstance()->getLocale(false);
        $meta   = $this->getMeta([
            'type' => $type,
            'value' => $value,
            'locale' => $locale
        ]);

        if (!$meta) {
            $meta = $this->getMeta([
                'type' => $type,
                'value' => $value
            ]);

            if ($meta) {
                unset($meta['id']);

                $meta['locale']         = $locale;
                $meta['text']           = Content::getTranslation($meta['text'], $locale);
                $meta['created']        = time();
                $meta['updated']        = time();

                $this->insert($meta);
            }
        }

        return $this->_format($meta);
    }

    public function getMeta($where) {
        $meta = $this->get([
            'text',
            'created',
            'updated',
            'type',
            'value',
            'status',
            'locale'
        ], $where);

        if (!$meta) {
            return [];
        }

        return $meta;
    }

    protected function _format($meta) {
        if ($meta) {
            $meta['text_formatted']  = Content::parse($meta['text']);

            switch ($meta['type']) {
                case 'country':
                    $meta['value_formatted'] = I18n::getInstance()->formatCountry($meta['value']);
                    break;

                case 'tag':
                    $meta['value_formatted'] = I18n::getInstance()->translate($meta['value']);
                    break;

                default:
                    $meta['value_formatted'] = $meta['value'];
                    break;
            }
        }

        return $meta;
    }

    public function updateMeta($data, $where = []) {
        $data['updated'] = time();
        $data['locale']  = Content::getLanguage($data['text']);

        if ($where === []) {
            $data['created'] = time();

            return $this->insert($data);
        } else {
            return $this->update($data, $where);
        }
    }
}
