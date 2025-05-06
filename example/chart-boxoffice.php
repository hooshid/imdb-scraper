<?php

use Hooshid\ImdbScraper\Base\Image;
use Hooshid\ImdbScraper\Chart;

require __DIR__ . "/../vendor/autoload.php";

$chart = new Chart();
$boxOffice = $chart->getBoxOffice();
if (isset($_GET["output"])) {
    header("Content-Type: application/json");
    echo json_encode($boxOffice);
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
    <title>Chart - BoxOffice</title>
    <link rel="stylesheet" href="/example/style.css">
</head>
<body>

<a href="/example" class="back-page">Go back</a>
<a href="/example/chart-boxoffice.php?output=json" class="output-json-link">JSON Format</a>

<div class="container">
    <div class="boxed">
        <!-- Title -->
        <h2 class="text-center pb-30">Box office - (<?php echo $boxOffice['weekend_start_date']; ?>-<?php echo $boxOffice['weekend_end_date']; ?>)</h2>

        <div class="flex-container">
            <table class="table">
                <tr>
                    <th>Poster</th>
                    <th>Title</th>
                    <th>Rating/Votes</th>
                    <th>Weekend Gross</th>
                    <th>Lifetime Gross</th>
                    <th>Weeks Released</th>
                </tr>
                <?php foreach ($boxOffice['list'] as $row) { ?>
                <tr>
                    <td>
                        <?php if ($row['image']) { ?>
                            <img class="small-image" src="<?php
                            echo $image->makeThumbnail($row['image']['url'], $row['image']['width'], $row['image']['height'], 140, 207);
                            ?>" alt="<?php echo $row['title']; ?>" loading="lazy">
                        <?php } ?>
                    </td>
                    <td><a href="title.php?id=<?php echo $row['id']; ?>"><?php echo $row['title']; ?></a></td>
                    <td><?php echo $row['rating']; ?> / <?php echo number_format($row['votes']); ?></td>
                    <td><?php echo number_format($row['weekend_gross_amount']); ?> (<?php echo $row['weekend_gross_currency']; ?>)</td>
                    <td><?php echo number_format($row['lifetime_gross_amount']); ?> (<?php echo $row['lifetime_gross_currency']; ?>)</td>
                    <td><?php echo $row['weeks_released']; ?></td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>

</body>
</html>