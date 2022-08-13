# IMDb Scraper

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

Using this IMDb API, you are able to search, browse and extract data of movies, tv series and musics on imdb.com.

## Install
This library scrapes imdb.com so changes their site can cause parts of this library to fail. You will probably need to update a few times a year. 

### Requirements
* PHP >= 7.3
* PHP cURL extension

### Install via composer
``` bash
$ composer require hooshid/imdb-scraper
```

## Run examples
The example gives you a quick demo to make sure everything's working, some sample code and lets you easily see some available data.

From the example folder in the root of this repository start up php's inbuilt webserver and browse to [http://localhost:8000]()

`php -S localhost:8000`

## Examples
### Set config
this package needs no configuration by default but can change languages of imdb.com if you want.

Configuration is done by the `\Hooshid\ImdbScraper\Base\Config` class.
You can alter the config by creating the object, modifying its properties then passing it to the constructor for imdb.
```php
$config = new \Hooshid\ImdbScraper\Base\Config();
$config->language = 'de-DE,de,en';
$title = new \Hooshid\ImdbScraper\Title(335266, $config);
echo $title->title(); // Lost in Translation - Zwischen den Welten
echo $title->originalTitle(); // Lost in Translation
```

### Get movie/series (title) data
Movie: The Matrix (1999) / URL: https://www.imdb.com/title/tt0133093 / ID: 0133093
``` php
$config = new Config();
$config->language = 'en-US,en';
$title = new Title("0133093", $config);
echo $title->title(); // The Matrix
echo $title->year(); // 1999

// get all available data as json
echo json_encode($title->full());
```
Tv Series: Game of Thrones (2011-2019) / URL: https://www.imdb.com/title/tt0944947 / ID: 0944947
``` php
$title = new Title("0944947"); // without config!
echo $title->title(); // Game of Thrones
echo $title->year(); // 2011
echo $title->endYear(); // 2019 -> just for series
echo $title->genres(); // Array of genres: ["Action", "Adventure", "Drama"]
echo $title->languages(); // Array of languages: ["English"]
echo $title->countries(); // Array of countries: ["United States", "United Kingdom"]
echo $title->rating(); // 9.2
echo $title->votes(); // 2022832

// get all available data as json
echo json_encode($title->full());
```
### Get person (name) data
Person: Christopher Nolan / URL: https://www.imdb.com/name/nm0634240 / ID: 0634240

``` php
$person = new \Hooshid\ImdbScraper\Person("0634240"); // without config!
echo $person->fullName(); // Christopher Nolan
echo $person->birthName(); // Christopher Edward Nolan
echo $person->birth()["date"]; // 1970-07-30
echo $person->birth()["place"]; // London, England, UK

// get all available data as json
echo json_encode($person->full());
```

### Other examples
just open the example folder, we put all examples and methods demo for you in there!

## Todo
* add search title
* add example of search person
* add charts
* add full feature of person
* add full feature of movies
* add series seasons

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.


[ico-version]: https://img.shields.io/packagist/v/hooshid/imdb-scraper.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/hooshid/imdb-scraper.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/hooshid/imdb-scraper
[link-downloads]: https://packagist.org/packages/hooshid/imdb-scraper
