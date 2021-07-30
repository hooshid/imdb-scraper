<?php

namespace Hooshid\ImdbScraper\Base\Exception;


class Http extends \Exception
{
    public $HTTPStatusCode = null;
}
