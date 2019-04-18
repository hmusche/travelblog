<?php

namespace TravelBlog\Model;

use Solsken\Model;
use Solsken\I18n;

use Medoo\Medoo;

class Translation extends Model {
    protected $_name = 'translation';

    protected $_translationCache = [];

    public function getKey($key) {
        $data = [
            'key' => $key
        ];

        foreach (I18n::getInstance()->getSupportedLocales() as $locale) {
            $locale = substr($locale, 0, 2);
            $entry = $this->get(['translation'], ['key' => $key, 'locale' => $locale]);

            $data['translation_' . $locale] = isset($entry['translation']) ? $entry['translation'] : '';
        }

        return $data;
    }

    public function updateTranslations($data, $where = []) {
        foreach ($data as $key => $value) {
            if (strpos($key, 'translation_') === 0) {
                $locale = explode('_', $key)[1];

                /**
                 * insert into DB if not existant
                 */
                $this->translate($data['key'], $locale);

                $this->update([
                    'key'         => $data['key'],
                    'translation' => $value,
                ], [
                    'key'         => $data['key'],
                    'locale'      => $locale
                ]);
            }
        }

        return $key;
    }

    public function translate($string, $locale) {
        if (!isset($this->_translationCache[$locale])) {
            $this->cacheLocaleStrings($locale);
        }

        if (!array_key_exists($string, $this->_translationCache[$locale])) {
            foreach (I18n::getInstance()->getSupportedLocales() as $loc) {
                $this->insert([
                    'key'    => $string,
                    'locale' => substr($loc, 0, 2)
                ]);
            }

            $this->_translationCache[$locale][$string] = null;
        }

        return isset($this->_translationCache[$locale][$string]) && trim($this->_translationCache[$locale][$string]) !== '' ? $this->_translationCache[$locale][$string] : $string;
    }

    public function getMissing() {
        $languages = [];

        foreach (I18n::getInstance()->getSupportedLocales() as $locale) {
            $languages[] = substr($locale, 0, 2);
        }

        sort($languages);

        $data = $this->select([
            'key',
            'locales' => Medoo::raw('GROUP_CONCAT(<locale>)')
        ], [
            'translation' => '',
            'ORDER' => [
                'locale' => 'DESC'
            ],
            'GROUP' => [
                'key'
            ]
        ]);

        return $data;
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
