<?php

namespace Hooshid\ImdbScraper;

use Hooshid\ImdbScraper\Base\Base;
use Hooshid\ImdbScraper\Base\Config;

class NameSearch extends Base
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
     * Search name
     *
     * @param $params
     * @return array
     */
    public function search($params): array
    {
        // if params is empty or null
        if (empty($params)) {
            return [];
        }

        // if already result exist
        if ($this->data['result']) {
            return $this->data['result'];
        }

        $dom = $this->getHtmlDomParser("/search/name/?" . http_build_query($params));

        // check name list exist
        if (!$dom->findOneOrFalse('.ipc-metadata-list')) {
            return [];
        }

        $list = $dom->find('.ipc-metadata-list', 0);
        foreach ($list->find('.ipc-metadata-list-summary-item') as $e) {
            $title = $e->find(".ipc-title__text", 0)->text();

            $index = 0;
            if (preg_match('/^(\d+)\.\s*/', $title, $matches)) {
                $index = $this->getNumbers($matches[1]);
            }

            $url = $this->cleanString($e->find(".ipc-title a", 0)->getAttribute('href'));
            $url = str_replace($this->baseUrl, '', $url);
            $url = preg_replace('/\?.*/', '', $url);

            $name = $this->cleanString(preg_replace('/^\d+\.\s*/', '', $title));

            $job = null;
            if ($e->findOneOrFalse('[data-testid="nlib-professions"]')) {
                $job = implode(", ", $e->find('[data-testid="nlib-professions"] li')->innerText());
                $job = $this->cleanString($job);
            }

            $bio = null;
            if ($e->find(".ipc-html-content-inner-div", 0)->innerText()) {
                $bio = $this->cleanString($e->find(".ipc-html-content-inner-div", 0)->innerText());
            }

            $this->data['result'][] = [
                'index' => $index,
                'id' => $this->getImdbId($url),
                'url' => $this->baseUrl . $url,
                'name' => $name,
                'photo' => $this->photoUrl($this->cleanString($e->find(".ipc-media img", 0)->getAttribute('src'))),
                'job' => $job,
                'bio' => $bio,
            ];
        }

        return $this->data['result'];
    }
}

