<?php

use Hooshid\ImdbScraper\Base\Image;
use Hooshid\ImdbScraper\Calendar;

require __DIR__ . "/../vendor/autoload.php";

$type = trim(strip_tags($_GET["type"]));
if (empty($type)) {
    $type = "MOVIE";
}

$calendar = new Calendar();
$comingSoon = $calendar->comingSoon(['type' => $type]);
if (isset($_GET["output"])) {
    header("Content-Type: application/json");
    echo json_encode($comingSoon);
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
    <title>Upcoming releases</title>
    <link rel="stylesheet" href="/example/style.css">
</head>
<body>

<a href="/example" class="back-page">Go back</a>
<a href="/example/calendar.php?type=<?php echo $type; ?>&output=json" class="output-json-link">JSON Format</a>

<div class="container">
    <div class="boxed">
        <!-- Title -->
        <h2 class="text-center pb-30">Upcoming release - <?php echo ucfirst(strtolower($type)) ?></h2>

        <div class="flex-container">
            <table class="table">
                <tr>
                    <th>Poster</th>
                    <th>Release date</th>
                    <th>Title</th>
                    <th>Genres</th>
                    <th>Cast</th>
                </tr>
                <?php foreach ($comingSoon as $row) { ?>
                    <tr>
                        <td>
                            <?php if ($row['image']) { ?>
                                <img class="small-image" src="<?php
                                echo $image->makeThumbnail($row['image']['url'], $row['image']['width'], $row['image']['height'], 140, 207);
                                ?>" alt="<?php echo $row['title']; ?>" loading="lazy">
                            <?php } ?>
                        </td>
                        <td><?php echo $row['release_date']; ?></td>
                        <td><a href="title.php?id=<?php echo $row['id']; ?>"><?php echo $row['title']; ?></a></td>
                        <td><?php echo join(", ", $row['genres']); ?></td>
                        <td><?php echo join(", ", $row['cast']); ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>

</body>
</html>