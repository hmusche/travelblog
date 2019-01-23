<?php

namespace TravelBlog;

use Solsken\Registry;
use Solsken\Curl;

class TimeZoneDb {
    protected $_apiUrl = 'http://api.timezonedb.com/v2.1/get-time-zone?';
    protected $_apiKey;
    protected $_curl;

    protected $_data = [
        'format' => 'json',
        'by'     => 'position'
    ];

    public function __construct() {
        $this->_curl   = new Curl(['format' => 'json']);
        $this->_apiKey = Registry::get('app.config')['timezonedb_token'];
    }

    public function getTimeZoneData($lat, $lng, $ts = null) {
        $this->_data['lat'] = $lat;
        $this->_data['lng'] = $lng;

        if ($ts) {
            $this->_data['ts'] = $ts;
        }

        $data = $this->_curl->call($this->_getUrl());

        if (!isset($data['status']) || $data['status'] !== 'OK') {
            return false;
        }

        return [
            'offset'  => $data['gmtOffset'],
            'cc'      => $data['countryCode'],
        ];
    }

    protected function _getUrl() {
        $url = $this->_apiUrl;

        $data = array_merge(['key' => $this->_apiKey], $this->_data);

        return $url . http_build_query($data);
    }
}
