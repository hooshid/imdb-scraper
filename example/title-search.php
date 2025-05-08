<?php

use Hooshid\ImdbScraper\Base\Image;
use Hooshid\ImdbScraper\TitleSearch;

require __DIR__ . "/../vendor/autoload.php";

$limit = $_GET['limit'] ?? '50';
$selectedStartYear = @$_GET['startYear'];
$selectedEndYear = @$_GET['endYear'];
if (count($_GET) > 0) {
    $titleSearch = new TitleSearch();
    $results = $titleSearch->search([
        'searchTerm' => $_GET['searchTerm'] ?? '',
        'types' => $_GET['types'] ?? '',
        'genres' => $_GET['genres'] ?? '',
        'startDate' => $selectedStartYear ? $selectedStartYear . '-01-01' : '',
        'endDate' => $selectedEndYear ? $selectedEndYear . '-12-29' : '',
        'keywords' => $_GET['keywords'] ?? '',
        'companies' => $_GET['companies'] ?? '',
        'adult' => $_GET['adult'] ?? 'EXCLUDE_ADULT',
        'limit' => $limit,
    ]);

    if (isset($_GET["output"])) {
        header("Content-Type: application/json");
        echo json_encode($results);
        exit();
    }
} else {
    header("Location: /example");
    exit;
}

$titleTypes = [
    "movie" => "Movie",
    "tvSeries" => "TV Series",
    "short" => "Short",
    "tvEpisode" => "TV Episode",
    "tvMiniSeries" => "TV Mini Series",
    "tvMovie" => "TV Movie",
    "tvSpecial" => "TV Special",
    "tvShort" => "TV Short",
    "videoGame" => "Video Game",
    "video" => "Video",
    "musicVideo" => "Music Video",
    "podcastSeries" => "Podcast Series",
    "podcastEpisode" => "Podcast Episode"
];
$selectedType = @$_GET['types'];

$genreIDs = [
    "Action",
    "Adult",
    "Adventure",
    "Animation",
    "Biography",
    "Comedy",
    "Crime",
    "Documentary",
    "Drama",
    "Family",
    "Fantasy",
    "Film-Noir",
    "Game-Show",
    "History",
    "Horror",
    "Music",
    "Musical",
    "Mystery",
    "News",
    "Reality-TV",
    "Romance",
    "Sci-Fi",
    "Short",
    "Sport",
    "Talk-Show",
    "Thriller",
    "War",
    "Western"
];
$selectedGenre = @$_GET['genres'];

$image = new Image();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
    <title>Title search</title>
    <link rel="stylesheet" href="/example/style.css">
</head>
<body>

<a href="/example" class="back-page">Go back</a>
<a href="/example/title-search.php?<?php echo http_build_query($_GET); ?>&output=json" class="output-json-link">JSON
    Format</a>

<div class="container">
    <div class="boxed" style="max-width: 700px;">
        <h2 class="text-center pb-30">Title Search</h2>

        <form action="/example/title-search.php" method="get">
            <div class="form-group">
                <label for="searchTerm">Title:</label>
                <input class="form-field" type="text" id="searchTerm" name="searchTerm" maxlength="50"
                       placeholder="Search name" value="<?php echo @strip_tags($_GET['searchTerm']); ?>">
            </div>

            <div class="form-group">
                <label for="keywords">Keywords:</label>
                <input class="form-field" type="text" id="keywords" name="keywords" maxlength="50"
                       placeholder="Keywords" value="<?php echo @strip_tags($_GET['keywords']); ?>">
            </div>

            <div class="form-group">
                <label for="types">Type:</label>
                <select id="types" name="types" class="form-field">
                    <option value="">All</option>
                    <?php foreach ($titleTypes as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= $selectedType == $value ? "selected" : "" ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="startYear">Start Date:</label>
                <select id="startYear" name="startYear" class="form-field">
                    <option value="">All</option>
                    <?php for ($startYear = 2030; $startYear >= 1900; $startYear--) { ?>
                        <option value="<?php echo $startYear; ?>" <?php echo $selectedStartYear == $startYear ? "selected" : "" ?>>
                            <?php echo $startYear; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="endYear">End Date:</label>
                <select id="endYear" name="endYear" class="form-field">
                    <option value="">All</option>
                    <?php for ($endYear = 2030; $endYear >= 1900; $endYear--) { ?>
                        <option value="<?php echo $endYear; ?>" <?php echo $selectedEndYear == $endYear ? "selected" : "" ?>>
                            <?php echo $endYear; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="genres">Genre:</label>
                <select id="genres" name="genres" class="form-field">
                    <option value="" <?= empty($selectedGenre) ? "selected" : "" ?>>All</option>
                    <?php foreach ($genreIDs as $label): ?>
                        <option value="<?= htmlspecialchars($label) ?>" <?= $selectedGenre == $label ? "selected" : "" ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="adult">Adult titles:</label>
                <select id="adult" name="adult" class="form-field">
                    <option value="EXCLUDE_ADULT" <?php if (@$_GET['adult'] == "EXCLUDE_ADULT") {
                        echo " selected";
                    } ?>>Exclude
                    </option>
                    <option value="INCLUDE_ADULT" <?php if (@$_GET['adult'] == "INCLUDE_ADULT") {
                        echo " selected";
                    } ?>>Include
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="limit">Limit:</label>
                <input class="form-field" type="text" id="limit" name="limit" maxlength="2"
                       placeholder="Limit" value="<?php echo $limit ?>">
            </div>

            <div class="row">
                <input type="submit" value="Search">
            </div>
        </form>
    </div>

    <div class="boxed">
        <?php if (empty($results)) { ?>
            <div class="alert">No results!</div>
        <?php } else { ?>
            <h2 class="text-center pb-30">Result (<?php echo number_format($results['total']); ?>)</h2>

            <div class="flex-container">
                <table class="table">
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Original Title</th>
                        <th>Year</th>
                        <th>Type</th>
                    </tr>
                    <?php foreach ($results['results'] as $result) { ?>
                        <tr>
                            <td>
                                <?php if ($result['image']) { ?>
                                    <img class="medium-image" src="<?php
                                    echo $image->makeThumbnail($result['image']['url'], $result['image']['width'], $result['image']['height'], 140, 207);
                                    ?>" alt="<?php echo $result['title']; ?>" loading="lazy">
                                <?php } ?>
                            </td>
                            <td><a href="title.php?id=<?php echo $result['id']; ?>"><?php echo $result['title']; ?></a>
                            </td>
                            <td><?php echo $result['originalTitle']; ?></td>
                            <td><?php echo $result['year']; ?></td>
                            <td><?php echo $result['type']; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        <?php } ?>
    </div>
</div>

</body>
</html>