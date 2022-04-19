<?php

use Hooshid\ImdbScraper\Title;
use Hooshid\ImdbScraper\Base\Config;

require __DIR__ . "/../vendor/autoload.php";

if (isset ($_GET["id"]) && preg_match('/^[0-9]+$/', $_GET["id"])) {
    $config = new Config();
    $config->language = 'en-US,en';
    $title = new Title($_GET["id"], $config);
    if (isset($_GET["output"])) {
        header("Content-Type: application/json");
        echo json_encode($title->full());
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
    <title><?php echo $title->title(); ?></title>
    <link rel="stylesheet" href="/example/style.css">
</head>
<body>

<a href="/example" class="back-page">Go back</a>
<a href="/example/title.php?id=<?php echo $_GET["id"]; ?>&output=json" class="output-json-link">JSON Format</a>

<div class="container">
    <div class="boxed">
        <!-- Title -->
        <h2 class="text-center pb-30"><?php echo $title->title(); ?> (<?php echo $title->year(); ?><?php if($title->endYear() != $title->year()) {echo "-".$title->endYear();} ?>)</h2>

        <div class="flex-container">
            <div class="col-25">
                <!-- Photo -->
                <div class="photo">
                    <?php
                    if (($photo_url = $title->photo()) != NULL) {
                        echo '<img src="' . $photo_url['original'] . '" alt="Cover">';
                    } else {
                        echo "No photo available";
                    }
                    ?>
                </div>
            </div>


            <div class="col-75">
                <table class="table">
                    <!-- Main Url -->
                    <tr>
                        <td style="width: 140px;"><b>IMDb Full Url:</b></td>
                        <td>[<a href="<?php echo $title->mainUrl(); ?>">IMDb</a>]</td>
                    </tr>

                    <!-- IMDb id -->
                    <tr>
                        <td><b>IMDb id:</b></td>
                        <td><?php echo $title->imdbId(); ?></td>
                    </tr>

                    <!-- Original Title -->
                    <?php if ($title->originalTitle()) { ?>
                        <tr>
                            <td><b>Original Title:</b></td>
                            <td><?php echo $title->originalTitle(); ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Type -->
                    <?php if ($title->type()) { ?>
                        <tr>
                            <td><b>Type:</b></td>
                            <td><?php echo $title->type(); ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Genres -->
                    <?php if (!empty($title->genres())) { ?>
                        <tr>
                            <td><b>All Genres:</b></td>
                            <td><?php echo implode(', ', $title->genres()) ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Languages -->
                    <?php if (!empty($title->languages())) { ?>
                        <tr>
                            <td><b>Languages:</b></td>
                            <td><?php echo implode(', ', $title->languages()) ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Countries -->
                    <?php if (!empty($title->countries())) { ?>
                        <tr>
                            <td><b>Countries:</b></td>
                            <td><?php echo implode(', ', $title->countries()) ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Keywords -->
                    <?php if (!empty($title->keywords())) { ?>
                        <tr>
                            <td><b>Keywords:</b></td>
                            <td><?php echo implode(', ', $title->keywords()) ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Year(s) -->
                    <?php if ($title->year()) { ?>
                        <tr>
                            <td><b>Year:</b></td>
                            <td><?php echo $title->year(); ?><?php if($title->endYear() != $title->year()) {echo "-".$title->endYear();} ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Runtime -->
                    <?php if ($title->runtime()) { ?>
                        <tr>
                            <td><b>Runtime:</b></td>
                            <td><?php echo $title->runtime(); ?> minutes</td>
                        </tr>
                    <?php } ?>

                    <!-- Rating & Votes -->
                    <?php if ($title->rating() and $title->votes()) { ?>
                        <tr>
                            <td><b>Rating & Votes:</b></td>
                            <td><?php echo $title->rating(); ?>/10 from <?php echo number_format($title->votes()); ?> votes</td>
                        </tr>
                    <?php } ?>

                    <!-- Main Tagline -->
                    <?php
                    if (!empty($title->tagline())) { ?>
                        <tr>
                            <td><b>Main Tagline:</b></td>
                            <td><?php echo $title->tagline(); ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Taglines -->
                    <?php
                    if (!empty($title->taglines())) { ?>
                        <tr>
                            <td><b>Taglines:</b></td>
                            <td>
                                <ul>
                                    <?php foreach ($title->taglines() as $t) { ?>
                                        <li><?php echo $t; ?></li>
                                    <?php } ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Colors -->
                    <?php if (!empty($title->colors())) { ?>
                        <tr>
                            <td><b>Colors:</b></td>
                            <td><?php echo implode(', ', $title->colors()) ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Sound -->
                    <?php
                    if (!empty($title->sounds())) { ?>
                        <tr>
                            <td><b>Sound:</b></td>
                            <td><?php echo implode(', ', $title->sounds()); ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Aspect Ratio -->
                    <?php if ($title->aspectRatio()) { ?>
                        <tr>
                            <td><b>Aspect Ratio:</b></td>
                            <td><?php echo $title->aspectRatio(); ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Locations -->
                    <?php if (!empty($title->locations())) { ?>
                        <tr>
                            <td><b>Filming Locations:</b></td>
                            <td>
                                <ul>
                                    <?php foreach ($title->locations() as $location) { ?>
                                        <li><?php echo $location; ?></li>
                                    <?php } ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Mpaa reason -->
                    <?php if (!empty($title->mpaaReason())) { ?>
                        <tr>
                            <td><b>Mpaa Reason:</b></td>
                            <td>
                                <ul>
                                    <?php echo $title->mpaaReason(); ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Mpaa full list -->
                    <?php if (!empty($title->mpaa())) { ?>
                        <tr>
                            <td><b>mpaa:</b></td>
                            <td>
                                <ul>
                                    <?php foreach ($title->mpaa() as $country => $mpaa) { ?>
                                        <li><?php echo $country; ?> : <?php echo $mpaa; ?></li>
                                    <?php } ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>

                </table>
            </div>

        </div>
    </div>
</div>

</body>
</html>