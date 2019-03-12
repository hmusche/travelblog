<?php

namespace TravelBlog;

use TravelBlog\Model\PostMeta;
use Solsken\Cookie;
use Solsken\Registry;
use Solsken\Http;
use Solsken\I18n;

class Controller extends \Solsken\Controller {
    public function preDispatch() {
        parent::preDispatch();

        $this->_languageCheck();

        $metaModel = new PostMeta();

        $this->_view->blogTitle     = 'no fly zone';
        $this->_view->postCountries = $metaModel->getCountries();
        $this->_view->postTags      = $metaModel->getTags();
        $this->_view->locales       = Registry::get('app.config')['translation']['supported_locales'];
        $this->_view->currentLocale = I18n::getInstance()->getLocale();
        $this->_view->acceptCookie  = Cookie::get('accept');
    }

    protected function _languageCheck() {
        $language = $this->_request->getParam('language');

        if ($language) {
            Cookie::set('locale_settings', $language);
            Http::redirect($this->_request->get('path'));
        }
    }

}
