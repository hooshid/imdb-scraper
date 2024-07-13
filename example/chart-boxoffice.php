<?php

use Hooshid\ImdbScraper\Chart;
use Hooshid\ImdbScraper\Base\Config;

require __DIR__ . "/../vendor/autoload.php";

$config = new Config();
$config->language = 'en-US,en';
$chart = new Chart($config);
if (isset($_GET["output"])) {
    header("Content-Type: application/json");
    echo json_encode($chart->getBoxOffice());
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
    <title>Chart - BoxOffice</title>
    <link rel="stylesheet" href="/example/style.css">
</head>
<body>

<a href="/example" class="back-page">Go back</a>
<a href="/example/chart-boxoffice.php?output=json" class="output-json-link">JSON Format</a>

<div class="container">
    <div class="boxed">
        <!-- Title -->
        <h2 class="text-center pb-30">Box office</h2>

        <div class="flex-container">
            <table class="table">
                <tr>
                    <th>Id</th>
                    <th>Title</th>
                    <th>Weekend</th>
                    <th>Gross</th>
                    <th>Weeks</th>
                </tr>
                <?php foreach ($chart->getBoxOffice() as $row) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['title']; ?></td>
                    <td><?php echo $row['weekend']; ?></td>
                    <td><?php echo $row['gross']; ?></td>
                    <td><?php echo $row['weeks']; ?></td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>

</body>
</html>