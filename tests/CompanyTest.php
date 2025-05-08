<?php

use Hooshid\ImdbScraper\CompanySearch;
use PHPUnit\Framework\TestCase;

class CompanyTest extends TestCase
{
    public function testCompanySearch()
    {
        $companySearch = new CompanySearch();
        $results = $companySearch->search('Netflix', 15);

        $this->assertIsArray($results);
        $this->assertCount(15, $results);

        $this->assertEquals('co0144901', $results[0]['id']);
        $this->assertEquals('Netflix', $results[0]['name']);
        $this->assertIsArray($results[0]['rank']);
        $this->assertIsInt($results[0]['rank']['current_rank']);
        $this->assertEquals('United States', $results[0]['country']);
        $this->assertEquals('Distributor, Production', implode(", ", $results[0]['types']));
    }
}
