<?php

use Hooshid\ImdbScraper\Base\Image;
use Hooshid\ImdbScraper\Title;

require __DIR__ . "/../vendor/autoload.php";

$id = $_GET["id"];
if (isset($id) and preg_match('/^(tt\d+|\d+)$/', $id)) {
    $titleObj = new Title($id);
    $title = $titleObj->full(['keywords', 'locations', 'sounds', 'colors', 'aspect_ratio', 'cameras', 'mpaas', 'videos']);
    if (isset($_GET["output"])) {
        header("Content-Type: application/json");
        echo json_encode($title);
        exit();
    }
} else {
    header("Location: /example");
    exit;
}

$image = new Image();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
    <title><?php echo $title['title']; ?></title>
    <link rel="stylesheet" href="/example/style.css">
</head>
<body>

<a href="/example" class="back-page">Go back</a>
<a href="/example/title.php?id=<?php echo $id; ?>&output=json" class="output-json-link">JSON Format</a>

<div class="container">
    <div class="boxed">
        <!-- Title -->
        <h2 class="text-center pb-30"><?php echo $title['title']; ?>
            (<?php echo $title['year']; ?><?php if ($title['end_year']) {
                echo "-" . $title['end_year'];;
            } ?>)</h2>

        <div class="flex-container">
            <div class="col-25">
                <!-- Photo -->
                <div class="photo">
                    <?php if ($title['image']) { ?>
                        <img src="<?php
                        echo $image->makeThumbnail($title['image']['url'], $title['image']['width'], $title['image']['height'], 190, 281);
                        ?>" alt="<?php echo $title['title']; ?>" loading="lazy">
                    <?php } else { ?>
                        No photo available
                    <?php } ?>
                </div>
            </div>


            <div class="col-75">
                <table class="table">
                    <!-- Main Url -->
                    <tr>
                        <td style="width: 140px;"><b>IMDb Full Url:</b></td>
                        <td>[<a href="<?php echo $title['main_url']; ?>">IMDb</a>]</td>
                    </tr>

                    <!-- IMDb id -->
                    <tr>
                        <td><b>IMDb id:</b></td>
                        <td><?php echo $title['imdb_id']; ?></td>
                    </tr>

                    <!-- Original Title -->
                    <?php if ($title['original_title']) { ?>
                        <tr>
                            <td><b>Original Title:</b></td>
                            <td><?php echo $title['original_title']; ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Type -->
                    <?php if ($title['type']) { ?>
                        <tr>
                            <td><b>Type:</b></td>
                            <td><?php echo $title['type']; ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Genres -->
                    <?php if (!empty($title['genres'])) { ?>
                        <tr>
                            <td><b>All Genres:</b></td>
                            <td><?php echo implode(', ', $title['genres']) ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Languages -->
                    <?php if (!empty($title['languages'])) { ?>
                        <tr>
                            <td><b>Languages:</b></td>
                            <td><?php echo implode(', ', array_column($title['languages'], 'name')) ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Countries -->
                    <?php if (!empty($title['countries'])) { ?>
                        <tr>
                            <td><b>Countries:</b></td>
                            <td><?php echo implode(', ', array_column($title['countries'], 'name')) ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Keywords -->
                    <?php if (!empty($title['keywords'])) { ?>
                        <tr>
                            <td><b>Keywords:</b></td>
                            <td><?php echo implode(', ', $title['keywords']) ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Year(s) -->
                    <?php if ($title['year']) { ?>
                        <tr>
                            <td><b>Year:</b></td>
                            <td><?php echo $title['year']; ?><?php if ($title['end_year']) {
                                    echo "-" . $title['end_year'];
                                } ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Runtime -->
                    <?php if ($title['runtime']) { ?>
                        <tr>
                            <td><b>Runtime:</b></td>
                            <td><?php echo $title['runtime']; ?> minutes</td>
                        </tr>
                    <?php } ?>

                    <!-- Rating & Votes -->
                    <?php if ($title['ratings']['rating'] and $title['ratings']['votes']) { ?>
                        <tr>
                            <td><b>Rating & Votes:</b></td>
                            <td><?php echo $title['ratings']['rating']; ?>/10
                                from <?php echo number_format($title['ratings']['votes']); ?>
                                votes
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Taglines -->
                    <?php
                    if (!empty($title['taglines'])) { ?>
                        <tr>
                            <td><b>Taglines:</b></td>
                            <td>
                                <ul>
                                    <?php foreach ($title['taglines'] as $tagline) { ?>
                                        <li><?php echo $tagline; ?></li>
                                    <?php } ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Colors -->
                    <?php if (!empty($title['colors'])) { ?>
                        <tr>
                            <td><b>Colors:</b></td>
                            <td>
                                <ul>
                                    <?php foreach ($title['colors'] as $sound) { ?>
                                        <li><?php echo $sound['value']; ?></li>
                                    <?php } ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Sounds -->
                    <?php
                    if (!empty($title['sounds'])) { ?>
                        <tr>
                            <td><b>Sounds:</b></td>
                            <td>
                                <ul>
                                    <?php foreach ($title['sounds'] as $sound) { ?>
                                        <li><?php echo $sound['value']; ?></li>
                                    <?php } ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Aspect Ratio -->
                    <?php if ($title['aspect_ratio']) { ?>
                        <tr>
                            <td><b>Aspect Ratio:</b></td>
                            <td>
                                <ul>
                                    <?php foreach ($title['aspect_ratio'] as $ar) { ?>
                                        <li><?php echo $ar['value']; ?> <?php if (!empty($ar['attributes'])) { ?>(<?php echo $ar['attributes'][0]; ?>)<?php } ?></li>
                                    <?php } ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Cameras -->
                    <?php
                    if (!empty($title['cameras'])) { ?>
                        <tr>
                            <td><b>Cameras:</b></td>
                            <td>
                                <ul>
                                    <?php foreach ($title['cameras'] as $camera) { ?>
                                        <li><?php echo $camera['value']; ?></li>
                                    <?php } ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Locations -->
                    <?php if (!empty($title['locations'])) { ?>
                        <tr>
                            <td><b>Filming Locations:</b></td>
                            <td>
                                <ul>
                                    <?php foreach ($title['locations'] as $location) { ?>
                                        <li><?php echo $location['real']; ?></li>
                                    <?php } ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Mpaa full list -->
                    <?php if (!empty($title['mpaas'])) { ?>
                        <tr>
                            <td><b>mpaa:</b></td>
                            <td>
                                <ul>
                                    <?php foreach ($title['mpaas'] as $mpaa) { ?>
                                        <li><?php echo $mpaa['country']; ?> : <?php echo $mpaa['rating']; ?></li>
                                    <?php } ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>


            <!-- Videos -->
            <?php if (!empty($title['videos'])) { ?>
                <div class="head-title">Videos</div>
                <div class="grid-box-4">
                    <?php foreach ($title['videos'] as $key => $item) { ?>
                        <div class="video-box">
                            <div class="thumbnail">
                                <a href="video.php?id=<?php echo $item['id']; ?>">
                                    <?php if ($item['thumbnail']) { ?>
                                        <img src="<?php
                                        echo $image->makeThumbnail($item['thumbnail']['url'], $item['thumbnail']['width'], $item['thumbnail']['height'], 500, 281);
                                        ?>" alt="<?php echo $item['title']; ?>" loading="lazy">
                                    <?php } ?>

                                    <div class="top-label"><?php echo date('Y-m-d H:i', strtotime($item['created_date'])); ?></div>

                                    <div class="bottom-label"><?php echo $item['content_type']; ?>
                                        - <?php echo $item['runtime_formatted']; ?></div>
                                </a>
                            </div>

                            <a href="video.php?id=<?php echo $item['id']; ?>" class="title font-bold">
                                <?php echo $item['title']; ?>
                            </a>

                            <a href="title.php?id=<?php echo $item['primary_title']['id']; ?>" class="title">
                                <?php echo $item['primary_title']['title']; ?>
                            </a>

                            <time><?php echo $item['primary_title']['year']; ?></time>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

        </div>
    </div>
</div>

</body>
</html>