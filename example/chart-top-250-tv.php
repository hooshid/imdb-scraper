<?php

use Hooshid\ImdbScraper\Chart;
use Hooshid\ImdbScraper\Base\Config;

require __DIR__ . "/../vendor/autoload.php";

$config = new Config();
$config->language = 'en-US,en';
$chart = new Chart($config);
if (isset($_GET["output"])) {
    header("Content-Type: application/json");
    echo json_encode($chart->getTop250TV());
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
    <title>Chart - Top 250 TV Shows</title>
    <link rel="stylesheet" href="/example/style.css">
</head>
<body>

<a href="/example" class="back-page">Go back</a>
<a href="/example/chart-top-250-tv.php?output=json" class="output-json-link">JSON Format</a>

<div class="container">
    <div class="boxed">
        <!-- Title -->
        <h2 class="text-center pb-30">Top 250 TV Shows</h2>

        <div class="flex-container">
            <table class="table">
                <tr>
                    <th>Rank</th>
                    <th>Title</th>
                    <th>Year</th>
                    <th>Rating</th>
                    <th>Votes</th>
                </tr>
                <?php foreach ($chart->getTop250TV() as $row) { ?>
                <tr>
                    <td><?php echo $row['rank']; ?></td>
                    <td><a href="title.php?id=<?php echo $row['id']; ?>"><?php echo $row['title']; ?></a></td>
                    <td><?php echo $row['year']; ?></td>
                    <td><?php echo $row['rating']; ?></td>
                    <td><?php echo $row['votes']; ?></td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>

</body>
</html>