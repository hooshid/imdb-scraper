<?php

use Hooshid\ImdbScraper\Video;
use Hooshid\ImdbScraper\Base\Config;

require __DIR__ . "/../vendor/autoload.php";

$id = $_GET["id"];
if (isset($id) and preg_match('/^(vi\d+|\d+)$/', $id)) {
    $config = new Config();
    $config->language = 'en-US,en';
    $baseVideo = new Video($config);
    $video = $baseVideo->video($id);
    if (isset($_GET["output"])) {
        header("Content-Type: application/json");
        echo json_encode($video);
        exit();
    }
} else {
    header("Location: /example");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
    <title><?php echo $video['video_title']; ?></title>
    <link rel="stylesheet" href="/example/style.css">
</head>
<body>

<a href="/example" class="back-page">Go back</a>
<a href="/example/video.php?id=<?php echo $id; ?>&output=json" class="output-json-link">JSON Format</a>

<div class="container">
    <div class="boxed">
        <!-- Title -->
        <h2 class="text-center pb-30"><?php echo $video['title']; ?> - <?php echo $video['video_title']; ?></h2>

        <div class="flex-container">
            <div class="col-25" style="padding: 0 5px;">
                <p><b>Video Title: </b><?php echo $video['video_title']; ?></p>
                <br>
                <p><b>Title: </b><a
                            href="title.php?id=<?php echo $video['title_id']; ?>"><?php echo $video['title']; ?></a></p>
                <br>
                <p><b>Description: </b><?php echo $video['description']; ?></p>
                <br>
                <p><b>Caption: </b><?php echo $video['caption']; ?></p>
                <br>
                <p><b>Type: </b><?php echo $video['type']; ?></p>
                <br>
                <p><b>Runtime: </b><?php echo $video['runtime']; ?></p>
                <br>
                <p><b>Aspect Ratio: </b><?php echo $video['aspect_ratio']; ?></p>
                <br>
                <p><b>Created Date: </b><?php
                    $date = new DateTime($video['created_date']);
                    $formattedDate = $date->format('Y-m-d');
                    echo $formattedDate;
                    ?></p>
            </div>

            <div class="col-75">
                <video aria-label="trailer video" controls playsinline poster="<?php echo $video['thumbnail']; ?>"
                       preload="none" class="video">
                    <?php foreach ($video['urls'] as $url) { ?>
                        <source src="<?php echo $url['url']; ?>" type="video/mp4"
                                data-quality="<?php echo $url['quality']; ?>" data-res="<?php echo $url['quality']; ?>">
                    <?php } ?>
                </video>
            </div>
        </div>
    </div>
</div>

</body>
</html>