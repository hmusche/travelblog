<?php

namespace TravelBlog\Controller;

use TravelBlog\Controller;

use TravelBlog\Model\Stat;

class Stats extends Controller {
    public function overviewAction() {
        $statsModel          = new Stat;
        
        $this->_view->header = [
            'title' => 'stats.title'
        ];
    }
}
