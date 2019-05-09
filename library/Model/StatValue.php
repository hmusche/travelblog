<?php

namespace TravelBlog\Model;

use TravelBlog\Model\Translation;

use Solsken\Model;
use Solsken\I18n;
use Solsken\Request;

use Medoo\Medoo;

class StatValue extends Model {
    protected $_name = 'stat_value';

    public function getValues($statId) {
        $stats = $this->select([
            'id',
            'key' => Medoo::raw('CONCAT(\'stat.\', <stat_id>,\'.value.\', <id>)'),
            'value',
            'sort'
        ], [
            'stat_id' => $statId,
            'ORDER' => [
                'sort' => 'ASC'
            ]
        ]);

        return $stats;
    }

    public function getValue($id) {
        $i18n = I18n::getInstance();
        $tModel = new Translation();

        $data = $this->get([
            'id',
            'stat_id',
            'value',
            'sort'
        ], [
            'id' => $id
        ]);

        if ($data) {
            foreach ($i18n->getSupportedLocales() as $locale) {
                $locale = substr($locale, 0, 2);
                $data['translation_' . $locale] = $tModel->translate('stat.' . $data['stat_id'] . '.value.' . $id, $locale);
            }
        }

        return $data;
    }

    public function updateValue($data, $where = []) {
        $tModel = new Translation;
        $statId = Request::getInstance()->getParam('stat-id');

        if (!isset($where['id'])) {
            $maxSort = $this->max('sort', ['stat_id' => $statId]);
            $maxSort++;

            $this->insert([
                'sort' => $maxSort
            ]);

            $id = $this->id();
        } else {
            $id = $where['id'];
        }

        $data['key'] = 'stat.' . $statId . '.value.' . $id;

        $tModel->updateTranslations($data);

        $this->update([
            'stat_id' => $statId,
            'value'   => $data['value']
        ], [
            'id' => $id
        ]);

        return $id;
    }
}
