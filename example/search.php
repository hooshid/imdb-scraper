<?php

require __DIR__ . "/../vendor/autoload.php";

// check search string is imdb id or searchTerm with specific type
$search = trim(strip_tags($_GET["search"]));

function extractImdbId($url): ?string
{
    $pattern = '/(tt|nm)\d+/';
    if (preg_match($pattern, $url, $matches)) {
        return $matches[0];
    }
    return null;
}

if (extractImdbId($search)) {
    $search = extractImdbId($search);
}

// if search input is empty, go back to example page
if (empty($search)) {
    header("Location: /example");
} // check name id
elseif (preg_match('/^nm\d+$/', $search)) {
    header("Location: name.php?id=" . $search);
} // check title id
elseif (preg_match('/^tt\d+$/', $search)) {
    header("Location: title.php?id=" . $search);
} // check company id
elseif (preg_match('/^co\d+$/', $search)) {
    header("Location: company.php?id=" . $search);
} // check video id
elseif (preg_match('/^vi\d+$/', $search)) {
    header("Location: video.php?id=" . $search);
} // search type is name
elseif ($_GET['type'] === 'name') {
    header("Location: name-search.php?name=" . $search);
} // search type is title
elseif ($_GET['type'] === 'title') {
    header("Location: title-search.php?searchTerm=" . $search);
} // search type is episode
elseif ($_GET['type'] === 'episode') {
    header("Location: title-search.php?searchTerm=" . $search . "&types=tvEpisode");
} // search type is company
elseif ($_GET['type'] === 'company') {
    header("Location: company-search.php?company=" . $search);
}// search type is keyword
elseif ($_GET['type'] === 'keyword') {
    header("Location: keyword-search.php?keyword=" . $search);
}