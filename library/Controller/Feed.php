<?php

namespace TravelBlog\Controller;

use Solsken\I18n;

use TravelBlog\Controller;
use TravelBlog\Model\Post;
use TravelBlog\Model\PostMedia;

use Solsken\Feed\Rss;
use Solsken\Sitemap;
use Solsken\Registry;

class Feed extends Controller {
    public function sitemapAction() {
        header("Content-Type: application/xml; charset=utf-8");
        $postModel = new Post();
        $sitemap   = new Sitemap();
        $posts     = $postModel->getPosts([], 0);
        $languages = I18n::getInstance()->getSupportedLocales();
        $config    = Registry::get('app.config');
        $webhost   = $config['host'] . $config['path'];

        $pages = [
            'monthly' => [
                'map/route' => 0.4,
                'page/aboutus' => 0.8,
                'page/about-the-site' => 0.4,
                'stats/overview' => 0.4,
                'page/impressum' => 0.3,
                'page/dpr' => 0.3
            ]

        ];

        foreach ($pages as $freq => $urls) {
            foreach ($urls as $url => $prio) {
                $sitemap->addUrl($webhost . $url, time(), $freq, $prio);
            }
        }

        foreach ($this->_view->postTags as $tag) {
            if ($tag['value'] && $tag['count']) {
                $url = $webhost . 'post/by/tag/' . $tag['value'];
                $sitemap->addUrl($url, time(), 'daily', 0.6);
            }
        }

        foreach ($this->_view->postCountries as $country) {
            $url = $webhost . 'post/by/country/' . $country['country_code'];
            $sitemap->addUrl($url, time(), 'daily', 0.6);
        }

        foreach ($posts as $post) {
            $urls = [];

            foreach ($languages as $language) {
                $language = substr($language, 0, 2);

                if ($post['language'] && $post['language'] != 'und' && $language != $post['language']) {
                    $urls[$language] = $webhost . $post['link'] . '/tr/1/lang/' . $language . '/';
                }
            }

            if ($urls) {
                $urls[$post['language']] = $webhost . $post['link'] . '/';
                $sitemap->addMultiUrl($urls, $post['updated'], 'never');
            } else {
                $sitemap->addUrl($webhost . $post['link'] . '/', $post['updated'], 'never');
            }
        }

        echo $sitemap;
        exit;
    }

    public function rssAction() {
        header("Content-Type: application/xml; charset=utf-8");

        $postModel = new Post;
        $postMediaModel = new PostMedia;

        $limit = 10;
        $posts = $postModel->getPosts([], $limit);

        $config = Registry::get('app.config');
        $feed = new Rss([
            'title' => $config['title'],
            'link' => $config['host'] . $config['path'],
            'description' => $config['description']
        ]);

        if (isset($config['category'])) {
            $feed->setCategory($config['category']);
        }

        foreach ($posts as $post) {
            $item = [
                'title'       => $post['title'],
                'link'        => $config['host'] . $config['path'] . $post['link'],
                'description' => $post['text'],
                'guid'        => $config['host'] . $config['path'] . 'post/' . $post['id'],
                'pubDate'     => $post['posted'],
                'dc:creator'  => $post['author']
            ];

            if ($post['files']) {
                $files = $postMediaModel->getMedia($post['id'], 'sm');

                if (file_exists("asset/{$post['id']}/sm/{$files[0]['filename']}")) {
                    $item['enclosure'] = [
                        'url' => $config['host'] . $config['path'] . $files[0]['full_path'],
                        'type' => $files[0]['type'],
                        'length' => filesize("asset/{$post['id']}/sm/{$files[0]['filename']}")
                    ];
                }
            }

            if ($post['tag']) {
                $item['category'] = explode(', ', $post['tag']);
            }

            $feed->addItem($item);
        }

        echo $feed->render();
        exit;
    }
}
