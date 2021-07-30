<?php

use Hooshid\ImdbScraper\Person;
use Hooshid\ImdbScraper\Base\Config;

require __DIR__ . "/../vendor/autoload.php";

if (isset ($_GET["id"]) && preg_match('/^[0-9]+$/', $_GET["id"])) {
    $config = new Config();
    $config->language = 'en-US,en';
    $person = new Person($_GET["id"], $config);
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
    <title><?php echo $person->fullName(); ?></title>
    <link rel="stylesheet" href="/example/style.css">
</head>
<body>

<a href="/example" class="back-page">Go back</a>

<div class="container">
    <div class="boxed">
        <!-- Name -->
        <h2 class="text-center pb-30"><?php echo $person->fullName(); ?></h2>

        <div class="flex-container">
            <div class="col-25">
                <!-- Photo -->
                <div class="photo">
                    <?php
                    if (($photo_url = $person->photo()) != NULL) {
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
                        <td>[<a href="<?php echo $person->mainUrl(); ?>">IMDb</a>]</td>
                    </tr>

                    <!-- IMDb id -->
                    <tr>
                        <td><b>IMDb id:</b></td>
                        <td><?php echo $person->imdbId(); ?></td>
                    </tr>

                    <!-- Birth information -->
                    <?php
                    $birth = $person->birth();
                    if (!empty($birth)) {
                        ?>
                        <tr>
                            <td><b>Birth:</b></td>
                            <td>
                                <?php echo $birth["day"] . ' ' . $birth["month"] . ' ' . $birth["year"]; ?> (<?php echo $birth["date"]; ?>)

                                <?php if (!empty($birth["place"])) { ?>
                                    <br>in <?php echo $birth["place"]; ?>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Death information -->
                    <?php
                    $death = $person->death();
                    if (!empty($death)) {
                        ?>
                        <tr>
                            <td><b>Death:</b></td>
                            <td>
                                <?php echo $death["day"] . ' ' . $death["month"] . ' ' . $death["year"]; ?> (<?php echo $death["date"]; ?>)

                                <?php if (!empty($death["place"])) { ?>
                                    <br>in <?php echo $death["place"]; ?>
                                <?php } ?>

                                <?php if (!empty($death["cause"])) { ?>
                                    <br><?php echo $death["cause"]; ?>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Birth name -->
                    <?php
                    $birth_name = $person->birthName();
                    if (!empty($birth_name)) {
                        ?>
                        <tr>
                            <td><b>Birth Name:</b></td>
                            <td><?php echo $birth_name; ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Nick name(s) -->
                    <?php
                    $nick_names = $person->nickNames();
                    if (!empty($nick_names)) {
                        ?>
                        <tr>
                            <td><b>Nicknames:</b></td>
                            <td><?php echo implode(', ', $nick_names); ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Body Height -->
                    <?php
                    $body_height = $person->bodyHeight();
                    if (!empty($body_height)) {
                        ?>
                        <tr>
                            <td><b>Body Height:</b></td>
                            <td><?php echo $body_height["imperial"]; ?> - <?php echo $body_height["metric"]; ?> (<?php echo $body_height["metric_cm"]; ?> cm)</td>
                        </tr>
                    <?php } ?>

                    <!-- Mini Bio -->
                    <?php
                    $bio = $person->bio();
                    if (!empty($bio)) {
                        if (count($bio) < 2) $idx = 0; else $idx = 1;
                        $mini_bio = $bio[$idx]["text"];
                        $mini_bio = preg_replace('/https:\/\/' . str_replace(".", "\.", $person->imdbSiteUrl) . '\/name\/nm(\d{7,8})(\?ref_=nmbio_mbio)?/', 'person.php?id=\\1', $mini_bio);
                        $mini_bio = preg_replace('/https:\/\/' . str_replace(".", "\.", $person->imdbSiteUrl) . '\/title\/tt(\d{7,8})(\?ref_=nmbio_mbio)?/', 'title.php?id=\\1', $mini_bio);
                        ?>
                        <tr>
                            <td><b>Mini Bio:</b></td>
                            <td>
                                <?php echo $mini_bio; ?>
                                <br>(Written by: <?php echo $bio[$idx]['author']['name']; ?>)
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