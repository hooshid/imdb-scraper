<?php

use Hooshid\ImdbScraper\Base\Image;
use Hooshid\ImdbScraper\Trailers;

require __DIR__ . "/../vendor/autoload.php";

if (empty($_GET['type'])) {
    header("Location: /example");
    exit;
}
$type = $_GET['type'];
$trailers = new Trailers();
if ($type == "recent") {
    $list = $trailers->recentVideos();
} else {
    $list = $trailers->trendingVideos();
}
if (isset($_GET["output"])) {
    header("Content-Type: application/json");
    echo json_encode($list);
    exit();
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
    <title><?php echo ucfirst($_GET['type']); ?> Trailers</title>
    <link rel="stylesheet" href="/example/style.css">
</head>
<body>

<a href="/example" class="back-page">Go back</a>
<a href="/example/trailers.php?type=<?php echo $type; ?>&output=json" class="output-json-link">JSON Format</a>

<div class="container">
    <div class="boxed">
        <!-- Title -->
        <h2 class="text-center pb-30"><?php echo ucfirst($_GET['type']); ?> Trailers</h2>

        <?php if (empty($list)) { ?>
            <div class="alert">No results!</div>
        <?php } else { ?>
            <div class="grid-box-4">
                <?php foreach ($list as $key => $item) { ?>
                    <div class="video-box">
                        <div class="thumbnail">
                            <a href="video.php?id=<?php echo $item['id']; ?>">
                                <?php if ($item['thumbnail']) { ?>
                                    <img src="<?php
                                    echo $image->makeThumbnail($item['thumbnail']['url'], $item['thumbnail']['width'], $item['thumbnail']['height'], 500, 281);
                                    ?>" alt="<?php echo $item['title']; ?>" loading="lazy">
                                <?php } ?>

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

                        <time><?php echo $item['primary_title']['release_date']; ?></time>
                    </div>
                    <?php
                    if ($key == 15) {
                        break;
                    }
                    ?>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>

</body>
</html>