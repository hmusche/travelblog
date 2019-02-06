<?php

namespace TravelBlog;

use Solsken\Util as U;
use Solsken\I18n;

class Util extends U {
    static public function daysDifference($start, $date) {
        $i18n  = I18n::getInstance();
        $start = \DateTime::createFromFormat('Y-m-d', $start);
        $date  = (new \DateTime())->setTimestamp($date);

        $diff = $start->diff($date);

        $days   = $diff->d + 1;
        $string = $diff->invert
                ? $i18n->translate($days > 1 ? 'days.til.journey' : 'day.til.journey')
                : $i18n->translate($days > 1 ? 'days.since.journey' : 'day.since.journey');

        return sprintf($string, $days);
    }
}
