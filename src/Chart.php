<?php

namespace Hooshid\ImdbScraper;

use Hooshid\ImdbScraper\Base\Base;

class Chart extends Base
{
    protected $data = [
        'boxoffice' => [],
    ];

    /**
     * Get the USA Weekend Box-Office Summary, weekend earnings and all time earnings
     *
     * @return array
     */
    public function getBoxOffice(): array
    {
        $dom = $this->getHtmlDomParser("/chart/boxoffice/");

        // if result exist
        if ($this->data['boxoffice']) {
            return $this->data['boxoffice'];
        }

        $i = 0;
        foreach ($dom->find('[data-testid="chart-layout-main-column"] ul li') as $e) {
            $id = $this->getImdbId($e->find('.ipc-title a', 0)->getAttribute('href'));
            if ($id) {
                $this->data['boxoffice'][$i]['id'] = $id;
                $title = $this->cleanString($e->find('.ipc-title h3', 0)->innerText());
                $this->data['boxoffice'][$i]['title'] = preg_replace('/^\d+\.\s/', '', $title);

                $moneyPattern = "/[\$£]([\d\.]+)(M|K)/";
                foreach ($e->find('ul[data-testid="title-metadata-box-office-data-container"] li') as $metadata) {
                    $cellTitle = $metadata->find('span', 0)->innerText();
                    if (strpos($cellTitle, 'Weeks Released') !== false) {
                        $this->data['boxoffice'][$i]['weeks'] = $this->cleanString($metadata->find('span', 1)->innerText());
                    } else if (strpos($cellTitle, 'Weekend Gross') !== false) {
                        // Weekend
                        $weekend = $this->cleanString($metadata->find('span', 1)->innerText());
                        $weekendMatches = null;
                        preg_match($moneyPattern, $weekend, $weekendMatches, PREG_OFFSET_CAPTURE);
                        $this->data['boxoffice'][$i]['weekend'] = $weekendMatches[2][0] === 'M' ? $weekendMatches[1][0] : $weekendMatches[1][0] / 1000;
                    } else if (strpos($cellTitle, 'Total Gross') !== false) {
                        // Gross
                        $gross = $this->cleanString($metadata->find('span', 1)->innerText());
                        $grossMatches = null;
                        preg_match($moneyPattern, $gross, $grossMatches, PREG_OFFSET_CAPTURE);
                        $this->data['boxoffice'][$i]['gross'] = $grossMatches[2][0] === 'M' ? $grossMatches[1][0] : $grossMatches[1][0] / 1000;
                    }
                }
                $i++;
            }
        }

        return $this->data['boxoffice'];
    }

}

