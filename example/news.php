<?php

use Hooshid\ImdbScraper\Base\Image;
use Hooshid\ImdbScraper\News;

require __DIR__ . "/../vendor/autoload.php";

$type = $_GET["type"];
$news = new News();
$list = $news->newsList($type, 12);
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
    <title>News</title>
    <link rel="stylesheet" href="/example/style.css">
</head>
<body>

<a href="/example" class="back-page">Go back</a>
<a href="/example/news.php?type=<?php echo $type; ?>&output=json" class="output-json-link">JSON Format</a>

<div class="container">
    <div class="boxed">
        <!-- Name -->
        <h2 class="text-center pb-30">News - <?php echo ucfirst(strtolower($type)) ?></h2>

        <?php if (empty($list)) { ?>
            <div class="alert">No results!</div>
        <?php } else { ?>
            <div class="grid-box-4">
                <?php foreach ($list as $item) { ?>
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

                            <div class="sourceLabel">
                                <?php echo $item['sourceLabel']; ?>
                            </div>
                        </div>

                        <a href="<?php echo $item['sourceUrl']; ?>" target="_blank" class="title">
                            <?php echo $item['title']; ?>
                        </a>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>

</body>
</html>