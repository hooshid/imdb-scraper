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
    protected function buildUrl(string $page = null): string
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

        if (!$dom->findOneOrFalse('.ipc-metadata-list')) {
            return [];
        }

        $list = $dom->find('.ipc-metadata-list', 0);
        foreach ($list->find('.ipc-metadata-list-summary-item') as $loop) {
            $job = null;
            if($loop->findOneOrFalse('[data-testid="nlib-professions"]')){
                $job = $this->cleanString($loop->find('[data-testid="nlib-professions"] li',0)->innerText());
                $job = trim($job);
            }

            $bio = null;
            if($loop->find(".ipc-html-content-inner-div", 0)->innerText()){
                $bio = $this->cleanString($loop->find(".ipc-html-content-inner-div", 0)->innerText());
            }

            $name = $this->cleanString(preg_replace('/^\d+\.\s*/', '', $loop->find(".ipc-title__text", 0)->text()));

            $index = 0;
            if (preg_match('/^(\d+)\.\s*/', $loop->find(".ipc-title__text", 0)->text(), $matches)) {
                $index = $this->getNumbers($matches[1]);
            }

            $this->data['result'][] = [
                'id' => $this->getImdbId($loop->find(".ipc-title a", 0)->getAttribute('href')),
                'url' => "https://" . $this->imdbSiteUrl . $this->cleanString($loop->find(".ipc-title a", 0)->getAttribute('href')),
                'photo' => $this->photoUrl($this->cleanString($loop->find(".ipc-media img", 0)->getAttribute('src'))),
                'index' => $index,
                'name' => $name,
                'job' => $job,
                'bio' => $bio,
            ];
        }

        return $this->data['result'];
    }
}

