<?php

require __DIR__ . "/../vendor/autoload.php";

// check search string is imdb id of Title or Person
$search = trim(strip_tags($_GET["search"]));
if (!empty($search) && preg_match('/^(tt|nm|)([0-9]+)$/', $search, $matches)) {
    $type = !empty($matches[1]) ? $matches[1] : $_GET["type"];
    switch ($type) {
        case "nm":
            header("Location: person.php?id=" . $matches[2]);
            break;
        default:
            header("Location: title.php?id=" . $matches[2]);
            break;
    }
    return;
}

// if we have no search, go back to search page
if (empty($_GET["search"])) {
    header("Location: /example");
    exit;
}
