<?php

namespace TravelBlog\Model;

use Solsken\Model;

class Translation extends Model {
    protected $_name = 'translation';

    protected $_translationCache = [];

    public function translate($string, $locale) {
        if (!isset($this->_translationCache[$locale])) {
            $this->cacheLocaleStrings($locale);
        }

        return isset($this->_translationCache[$locale][$string]) ? $this->_translationCache[$locale][$string] : '';
    }

    public function cacheLocaleStrings($locale) {
        $data = $this->select(['key', 'translation'], ['locale' => $locale]);

        if (!isset($this->_translationCache[$locale])) {
            $this->_translationCache[$locale] = [];
        }

        foreach ($data as $row) {
            $this->_translationCache[$locale][$row['key']] = $row['translation'];
        }
    }
}
