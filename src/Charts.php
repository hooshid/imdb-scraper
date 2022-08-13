<?php

namespace Hooshid\ImdbScraper;

use Hooshid\ImdbScraper\Base\Base;

class Charts extends Base
{
    protected $data = [
        'boxoffice' => [],
    ];

    /**
     * Build imdb url
     *
     * @param string|null $page
     * @return string
     * @throws \Exception
     */
    protected function buildUrl($page = null): string
    {
        return "https://" . $this->imdbSiteUrl . "/chart/" . $page;
    }

    /***************************************[ Main Methods ]***************************************/

    /**
     * Get the USA Weekend Box-Office Summary, weekend earnings and all time earnings
     *
     * @return array
     */
    public function getChartsBoxOffice(): array
    {
        $dom = $this->getHtmlDomParser("boxoffice");

        // not found boxoffice table
        if ($dom->findOneOrFalse('table.chart') == false) {
            return [];
        }

        $i = 0;
        foreach ($dom->find('table.chart tbody tr') as $row) {
            $id = $this->getImdbId($row->find('.titleColumn a', 0)->getAttribute('href'));
            if ($id) {
                $this->data['boxoffice'][$i]['id'] = $id;
                $this->data['boxoffice'][$i]['title'] = $this->cleanString($row->find('.titleColumn a', 0)->innerText());
                $this->data['boxoffice'][$i]['weeks'] = $this->cleanString($row->find('.weeksColumn', 0)->innerText());

                // Weekend
                $weekend = $this->cleanString($row->find('.ratingColumn', 0)->innerText());
                $moneyPattern = "/[\$£]([\d\.]+)(M|K)/";
                $weekendMatches = null;
                preg_match($moneyPattern, $weekend, $weekendMatches, PREG_OFFSET_CAPTURE);
                $this->data['boxoffice'][$i]['weekend'] = $weekendMatches[2][0] === 'M' ? $weekendMatches[1][0] : $weekendMatches[1][0] / 1000;

                // Gross
                $gross = $this->cleanString($row->find('.ratingColumn .secondaryInfo', 0)->innerText());
                $grossMatches = null;
                preg_match($moneyPattern, $gross, $grossMatches, PREG_OFFSET_CAPTURE);
                $this->data['boxoffice'][$i]['gross'] = $grossMatches[2][0] === 'M' ? $grossMatches[1][0] : $grossMatches[1][0] / 1000;

                $i++;
            }
        }

        return $this->data['boxoffice'];
    }












    // @todo fix this

    /**
     * Get the MOVIEmeter Top 10
     * @return string[] array of IMDb IDs
     */
    public function getChartsTop10()
    {
        $page = $this->getPage('moviemeter');
        $offset = strpos($page, 'Most Popular Movies');
        $end = strpos($page, 'Our Most Popular charts use data');
        $res = array();
        while (count($res) < 10) {
            $matches = null;
            preg_match("#<td class=\"titleColumn\">\s+<a\s+href=\"/title/tt(\d+)#", $page, $matches, PREG_OFFSET_CAPTURE, $offset);
            if (!$matches || $offset > $end) {
                break;
            }

            $res[] = $matches[1][0];
            $offset = $matches[0][1] + 1;
        }
        return $res;
    }

}

