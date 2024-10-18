<?php


use Hooshid\ImdbScraper\Chart;

require __DIR__ . "/../vendor/autoload.php";

$type = $_GET["type"];
$chart = new Chart();
$list = $chart->getList($type);
if (isset($_GET["output"])) {
    header("Content-Type: application/json");
    echo json_encode($list);
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
    <title>Charts</title>
    <link rel="stylesheet" href="/example/style.css">
</head>
<body>

<a href="/example" class="back-page">Go back</a>
<a href="/example/chart-list.php?type=<?php echo $type; ?>&output=json" class="output-json-link">JSON Format</a>

<div class="container">
    <div class="boxed">
        <!-- Title -->
        <h2 class="text-center pb-30"><?php echo $type; ?></h2>

        <div class="flex-container">
            <table class="table">
                <tr>
                    <th>Rank</th>
                    <th>Poster</th>
                    <th>Title</th>
                    <th>Year</th>
                    <th>Rating</th>
                    <th>Votes</th>
                </tr>
                <?php foreach ($list as $row) { ?>
                <tr>
                    <td><?php echo $row['rank']; ?></td>
                    <td>
                        <?php if (!empty($row['imageUrl']['140'])) { ?>
                            <img class="small-image" src="<?php echo $row['imageUrl']['140']; ?>"
                                 alt="<?php echo $row['title']; ?>" loading="lazy">
                        <?php } ?>
                    </td>
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