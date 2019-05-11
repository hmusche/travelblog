<?php

namespace TravelBlog\Model;

use TravelBlog\Model\Translation;
use TravelBlog\Model\StatValue;

use Solsken\Model;
use Solsken\I18n;

use Medoo\Medoo;

class Stat extends Model {
    protected $_name = 'stat';

    public function getStat($id) {
        $i18n = I18n::getInstance();
        $tModel = new Translation();

        $data = $this->get([
            'id',
            'type',
            'sort'
        ], [
            'id' => $id
        ]);

        if ($data) {
            foreach ($i18n->getSupportedLocales() as $locale) {
                $locale = substr($locale, 0, 2);
                $data['translation_' . $locale] = $tModel->translate('stat.' . $id, $locale);
            }
        }

        return $data;
    }

    public function updateStat($data, $where = []) {
        $tModel = new Translation;

        if (!isset($where['id'])) {
            $startSort = $this->max('sort') ?: 0;
            $startSort++;

            $this->insert([
                'sort' => $startSort
            ]);

            $id = $this->id();
        } else {
            $id = $where['id'];
        }

        $data['key'] = 'stat.' . $id;

        $tModel->updateTranslations($data);

        return $id;
    }

    public function getStats() {
        $stats = $this->select([
            'id',
            'key' => Medoo::raw('CONCAT(\'stat.\', <id>)'),
            'type',
            'sort'
        ], [
            'ORDER' => [
                'sort' => 'ASC'
            ]
        ]);

        return $stats;
    }

    public function getFormattedStats() {
        $statValueModel = new StatValue;

        $stats = $this->getStats();

        foreach ($stats as $key => $stat) {
            $values = $statValueModel->getValues($stat['id']);

            switch ($stat['type']) {
                case 'pie':
                    $sum = array_sum(array_column($values, 'value'));
                    $stats[$key]['values'] = $values;

                    foreach ($values as $k => $value) {
                        $stats[$key]['values'][$k]['perc'] = round((int)$value['value'] / $sum * 100);
                    }

                    break;

                default:
                    $stats[$key]['values'] = $values;
                    break;
            }
        }

        return $stats;
    }
}
