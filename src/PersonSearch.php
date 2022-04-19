<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;
use Hooshid\ImdbScraper\Base\Config;

class PersonSearch extends Base
{
    protected $data = [
        'result' => [],
    ];

    /**
     * @param Config|null $config OPTIONAL override default config
     */
    public function __construct(Config $config = null)
    {
        parent::__construct($config);
    }

    /**
     * Build imdb url
     *
     * @param string|null $page
     * @return string
     * @throws Exception
     */
    protected function buildUrl($page = null): string
    {
        return "https://" . $this->imdbSiteUrl . "/search/name/" . $page;
    }

    /***************************************[ Main Methods ]***************************************/

    public function search($params): array
    {
        if(is_null($params)){
            return [];
        }

        $dom = $this->getHtmlDomParser("?" . http_build_query($params));

        if ($dom->findOneOrFalse('.lister-list') == false) {
            return [];
        }

        $list = $dom->find('.lister-list', 0);
        foreach ($list->find('.lister-item.mode-detail') as $loop) {
            $job = null;
            if($loop->find("p.text-muted.text-small", 0)->innerText()){
                $job = explode("|", $this->cleanString($loop->find("p.text-muted.text-small", 0)->innerText()))[0];
                $job = trim($job);
            }

            $bio = null;
            if($loop->find("p", 1)->innerText()){
                $bio = $this->cleanString($loop->find("p", 1)->innerText());
            }

            $this->data['result'][] = [
                'id' => $this->getImdbId($loop->find(".lister-item-header a", 0)->getAttribute('href')),
                'url' => "https://" . $this->imdbSiteUrl . $this->cleanString($loop->find(".lister-item-header a", 0)->getAttribute('href')),
                'photo' => $this->photoUrl($this->cleanString($loop->find(".lister-item-image img", 0)->getAttribute('src'))),
                'index' => $this->getNumbers($loop->find(".lister-item-index", 0)->innerText()),
                'name' => $this->cleanString($loop->find(".lister-item-header a", 0)->innerText()),
                'job' => $job,
                'bio' => $bio,
            ];
        }

        return $this->data['result'];
    }
}

