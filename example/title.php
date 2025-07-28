<?php

use Hooshid\ImdbScraper\Base\Image;
use Hooshid\ImdbScraper\Title;

require __DIR__ . "/../vendor/autoload.php";

$id = $_GET["id"];
if (isset($id) and preg_match('/^(tt\d+|\d+)$/', $id)) {
    $titleObj = new Title($id);
    $titleObj->images(8);
    $titleObj->videos(8);
    $titleObj->news(8);
    $title = $titleObj->full(['keywords', 'locations', 'sounds', 'colors', 'aspect_ratio', 'cameras', 'certificates']);
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
                        echo $image->makeThumbnail($title['image']['url'], $title['image']['width'], $title['image']['height'], 280, 414);
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

                    <!-- Adult -->
                    <?php if ($title['is_adult']) { ?>
                        <tr>
                            <td><b>Adult status:</b></td>
                            <td>Title is adult</td>
                        </tr>
                    <?php } ?>

                    <!-- Production_status -->
                    <?php if ($title['production_status']) { ?>
                        <tr>
                            <td><b>Production status:</b></td>
                            <td><?php echo $title['production_status']; ?>
                                &nbsp; <?php
                                if ($title['is_ongoing'] === true) {
                                    echo "(Series is ongoing!)";
                                } else if ($title['is_ongoing'] === false) {
                                    echo "(Series is finished!)";
                                }
                                ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Type -->
                    <?php if ($title['type']) { ?>
                        <tr>
                            <td><b>Type:</b></td>
                            <td><?php echo $title['type']; ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Plot -->
                    <?php if ($title['plot']) { ?>
                        <tr>
                            <td><b>Plot:</b></td>
                            <td><?php echo $title['plot']; ?></td>
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

                    <!-- Certificates -->
                    <?php if (!empty($title['certificates'])) { ?>
                        <tr>
                            <td><b>Certificates:</b></td>
                            <td>
                                <ul>
                                    <?php foreach ($title['certificates'] as $certificate) { ?>
                                        <li><?php echo $certificate['country']; ?>
                                            : <?php echo $certificate['rating']; ?></li>
                                    <?php } ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <!-- Images -->
            <?php if (!empty($title['images'])) { ?>
                <div class="head-title">Images</div>
                <div class="grid-box-4">
                    <?php foreach ($title['images'] as $key => $item) { ?>
                        <div class="video-box">
                            <div class="thumbnail">
                                <?php if ($item['image']) { ?>
                                    <img src="<?php
                                    echo $image->makeThumbnail($item['image']['url'], $item['image']['width'], $item['image']['height'], 500, 300);
                                    ?>" alt="<?php echo $item['caption']; ?>" loading="lazy">
                                <?php } ?>

                                <?php if ($item['copyright']) { ?>
                                    <div class="bottom-label">© <?php echo $item['copyright']; ?></div>
                                <?php } ?>
                            </div>

                            <div class="title font-bold">
                                <?php echo $item['caption']; ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

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

            <!-- News -->
            <?php if (!empty($title['news'])) { ?>
                <div class="head-title">News</div>
                <div class="grid-box-4">
                    <?php foreach ($title['news'] as $key => $item) { ?>
                        <div class="news-box">
                            <div class="thumbnail">
                                <?php if ($item['image']) { ?>
                                    <img src="<?php
                                    echo $image->makeThumbnail($item['image']['url'], $item['image']['width'], $item['image']['height'], 500, 281);
                                    ?>" alt="<?php echo $item['title']; ?>" loading="lazy">
                                <?php } ?>

                                <div class="date">
                                    <?php echo date('Y-m-d H:i', strtotime($item['date'])); ?>
                                </div>

                                <a href="<?php echo $item['source_home_url']; ?>" target="_blank" class="sourceLabel">
                                    <?php echo $item['source_label']; ?>
                                </a>
                            </div>

                            <a href="<?php echo $item['source_url']; ?>" target="_blank" class="title">
                                <?php echo $item['title']; ?>
                            </a>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

        </div>
    </div>
</div>

</body>
</html>