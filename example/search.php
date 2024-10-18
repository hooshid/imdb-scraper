<?php

require __DIR__ . "/../vendor/autoload.php";

// check search string is imdb id of Title or Person
$search = trim(strip_tags($_GET["search"]));

// if search input is empty, go back to example page
if (empty($search)) {
    header("Location: /example");
    exit;
} // check name id
elseif (preg_match('/^nm\d+$/', $search)) {
    header("Location: name.php?id=" . $search);
    return;
} // check title id
elseif (preg_match('/^tt\d+$/', $search)) {
    header("Location: title.php?id=" . $search);
    return;
} // search type is name
elseif ($_GET['type'] === 'name') {
    header("Location: name-search.php?name=" . $search);
    exit;
} // search type is title
elseif ($_GET['type'] === 'title') {
    header("Location: title-search.php?searchTerm=" . $search);
    exit;
}// search type is episode
elseif ($_GET['type'] === 'episode') {
    header("Location: title-search.php?searchTerm=" . $search ."&types=tvEpisode");
    exit;
}