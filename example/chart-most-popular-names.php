<?php

use Hooshid\ImdbScraper\Base\Image;
use Hooshid\ImdbScraper\Chart;

require __DIR__ . "/../vendor/autoload.php";

$chart = new Chart();
if (isset($_GET["output"])) {
    header("Content-Type: application/json");
    echo json_encode($chart->getMostPopularNames());
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
    <title>Chart - Most Popular Names</title>
    <link rel="stylesheet" href="/example/style.css">
</head>
<body>

<a href="/example" class="back-page">Go back</a>
<a href="/example/chart-most-popular-names.php?output=json" class="output-json-link">JSON Format</a>

<div class="container">
    <div class="boxed">
        <!-- Title -->
        <h2 class="text-center pb-30">Most Popular Names</h2>

        <div class="flex-container">
            <table class="table">
                <tr>
                    <th>Rank</th>
                    <th>Name</th>
                    <th>Image</th>
                    <th>Professions</th>
                    <th>Known for</th>
                </tr>
                <?php foreach ($chart->getMostPopularNames() as $row) { ?>
                    <tr>
                        <td><?php echo $row['rank']; ?></td>
                        <td>
                            <?php if ($row['image']) { ?>
                                <img class="medium-image" src="<?php
                                echo $image->makeThumbnail($row['image']['url'], $row['image']['width'], $row['image']['height'], 140, 207);
                                ?>" alt="<?php echo $row['name']; ?>" loading="lazy">
                            <?php } ?>
                        </td>
                        <td><a href="name.php?id=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></td>
                        <td>
                            <?php if (!empty($professions = $row['professions'])) { ?>
                                <?php echo implode(', ', $professions); ?>
                            <?php } ?>
                        </td>
                        <td>
                            <?php foreach ($row['known_for'] as $known) { ?>
                                <a href="title.php?id=<?php echo $known['id']; ?>">
                                    <?php echo $known['title']; ?> (<?php echo $known['year']; ?>)
                                </a>
                                <br>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>

</body>
</html>