<?php

namespace TravelBlog\Controller;

use TravelBlog\Controller;

use TravelBlog\Model\Stat;
use TravelBlog\Model\StatValue;

class Stats extends Controller {
    public function overviewAction() {
        $statModel      = new Stat;
        $stats          = $statModel->getFormattedStats();


        $this->_view->stats = $stats;
        $this->_view->header = [
            'title' => 'stats.title'
        ];
    }
}
