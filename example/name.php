<?php

use Hooshid\ImdbScraper\Name;

require __DIR__ . "/../vendor/autoload.php";

$id = $_GET["id"];
if (isset($id) and preg_match('/^(nm\d+|\d+)$/', $id)) {
    $name = new Name($id);
    if (isset($_GET["output"])) {
        header("Content-Type: application/json");
        echo json_encode($name->full());
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
    <title><?php echo $name->fullName(); ?></title>
    <link rel="stylesheet" href="/example/style.css">
</head>
<body>

<a href="/example" class="back-page">Go back</a>
<a href="/example/name.php?id=<?php echo $id; ?>&output=json" class="output-json-link">JSON Format</a>

<div class="container">
    <div class="boxed">
        <!-- Name -->
        <h2 class="text-center pb-30"><?php echo $name->fullName(); ?></h2>

        <div class="flex-container">
            <div class="col-25">
                <!-- Photo -->
                <div class="photo">
                    <?php
                    if (($photo_url = $name->photo()) != NULL) {
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
                        <td>[<a href="<?php echo $name->mainUrl(); ?>">IMDb</a>]</td>
                    </tr>

                    <!-- IMDb id -->
                    <tr>
                        <td><b>IMDb id:</b></td>
                        <td><?php echo $name->imdbId(); ?></td>
                    </tr>

                    <!-- Birth information -->
                    <?php
                    $birth = $name->birth();
                    if (!empty($birth)) {
                        ?>
                        <tr>
                            <td><b>Birth:</b></td>
                            <td>
                                <?php echo $birth["day"] . ' ' . $birth["month"] . ' ' . $birth["year"]; ?>
                                <?php if ($birth["date"]) { ?>
                                    (<?php echo $birth["date"]; ?>)
                                <?php } ?>

                                <?php if (!empty($birth["place"])) { ?>
                                    <br>in <?php echo $birth["place"]; ?>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Death information -->
                    <?php
                    $death = $name->death();
                    if (!empty($death)) {
                        ?>
                        <tr>
                            <td><b>Death:</b></td>
                            <td>
                                <?php echo $death["day"] . ' ' . $death["month"] . ' ' . $death["year"]; ?>
                                (<?php echo $death["date"]; ?>)

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
                    $birth_name = $name->birthName();
                    if (!empty($birth_name)) {
                        ?>
                        <tr>
                            <td><b>Birth Name:</b></td>
                            <td><?php echo $birth_name; ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Nick name(s) -->
                    <?php
                    $nick_names = $name->nickNames();
                    if (!empty($nick_names)) {
                        ?>
                        <tr>
                            <td><b>Nicknames:</b></td>
                            <td><?php echo implode(', ', $nick_names); ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Body Height -->
                    <?php
                    $body_height = $name->bodyHeight();
                    if (!empty($body_height)) {
                        ?>
                        <tr>
                            <td><b>Body Height:</b></td>
                            <td><?php echo $body_height["imperial"]; ?> - <?php echo $body_height["metric"]; ?>
                                (<?php echo $body_height["metric_cm"]; ?> cm)
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Mini Bio -->
                    <?php
                    $bio = $name->bio();
                    if (!empty($bio)) {
                        if (count($bio) < 2) $idx = 0; else $idx = 1;
                        $mini_bio = $bio[$idx]["text"];
                        $mini_bio = preg_replace('/https:\/\/' . str_replace(".", "\.", $name->imdbSiteUrl) . '\/name\/nm(\d{7,8})(\?ref_=nmbio_mbio)?/', 'name.php?id=nm\\1', $mini_bio);
                        $mini_bio = preg_replace('/https:\/\/' . str_replace(".", "\.", $name->imdbSiteUrl) . '\/title\/tt(\d{7,8})(\?ref_=nmbio_mbio)?/', 'title.php?id=tt\\1', $mini_bio);
                        ?>
                        <tr>
                            <td><b>Mini Bio:</b></td>
                            <td>
                                <?php echo $mini_bio; ?>
                                <?php if (isset($bio[$idx]['author']) and isset($bio[$idx]['author']['name'])) { ?>
                                    <br>(Written by: <?php echo $bio[$idx]['author']['name']; ?>)
                                <?php } ?>
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