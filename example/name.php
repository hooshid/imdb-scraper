<?php

use Hooshid\ImdbScraper\Base\Image;
use Hooshid\ImdbScraper\Name;

require __DIR__ . "/../vendor/autoload.php";

$id = $_GET["id"];
if (isset($id) and preg_match('/^(nm\d+|\d+)$/', $id)) {
    $name = new Name($id);
    $name->spouses();
    $name->children();
    $name->parents();
    $name->relatives();
    $name->trivia();
    $name->quotes();
    $name->trademarks();
    $name->salaries();
    $name->images(8);
    $name->videos(8);
    $name->news(8);
    $name->creditKnownFor();
    $name->credits();
    $name->awards();
    $person = $name->full();
    if (isset($_GET["output"])) {
        header("Content-Type: application/json");
        echo json_encode($person);
        exit();
    }
} else {
    header("Location: /example");
    exit;
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
    <title><?php echo $person['full_name']; ?></title>
    <link rel="stylesheet" href="/example/style.css">
</head>
<body>

<a href="/example" class="back-page">Go back</a>
<a href="/example/name.php?id=<?php echo $id; ?>&output=json" class="output-json-link">JSON Format</a>

<div class="container">
    <div class="boxed">
        <!-- Name -->
        <h2 class="text-center pb-30"><?php echo $person['full_name']; ?></h2>

        <div class="flex-container">
            <div class="col-25">
                <!-- Photo -->
                <div class="photo">
                    <?php if ($person['image']) { ?>
                        <img src="<?php
                        echo $image->makeThumbnail($person['image']['url'], $person['image']['width'], $person['image']['height'], 280, 414);
                        ?>" alt="<?php echo $person['full_name']; ?>" loading="lazy">
                    <?php } else { ?>
                        No photo available
                    <?php } ?>
                </div>
            </div>

            <div class="col-75">
                <table class="table">
                    <!-- Main Url -->
                    <tr>
                        <td style="width: 140px;"><b>IMDb Full Url:</b></td>
                        <td>[<a href="<?php echo $person['main_url']; ?>">IMDb</a>]</td>
                    </tr>

                    <!-- IMDb id -->
                    <tr>
                        <td><b>IMDb id:</b></td>
                        <td><?php echo $person['imdb_id']; ?></td>
                    </tr>

                    <!-- Redirect -->
                    <?php if (!empty($person['canonical_id'])) { ?>
                        <tr>
                            <td><b>New IMDb ID:</b></td>
                            <td>
                                <?php echo $person['canonical_id']; ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Birth information -->
                    <?php if (!empty($person['rank'])) { ?>
                        <tr>
                            <td><b>Rank:</b></td>
                            <td>
                                <?php if ($person['rank']["current_rank"]) { ?>
                                    Current rank: <?php echo $person['rank']["current_rank"]; ?><br>
                                <?php } ?>
                                <?php if ($person['rank']["change_direction"]) { ?>
                                    Change Direction: <?php echo $person['rank']["change_direction"]; ?><br>
                                <?php } ?>
                                <?php if ($person['rank']["difference"]) { ?>
                                    Difference: <?php echo $person['rank']["difference"]; ?><br>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Birth information -->
                    <?php if (!empty($person['birth'])) { ?>
                        <tr>
                            <td><b>Birth:</b></td>
                            <td>
                                <?php if ($person['birth']["date"]) { ?>
                                    <?php echo $person['birth']["date"]; ?>
                                <?php } ?>

                                <?php if (!empty($person['birth']["place"])) { ?>
                                    <br>in <?php echo $person['birth']["place"]; ?>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Death information -->
                    <?php if (!empty($person['death'])) { ?>
                        <tr>
                            <td><b>Death:</b></td>
                            <td>
                                <?php if ($person['death']["date"]) { ?>
                                    <?php echo $person['death']["date"]; ?>
                                <?php } ?>

                                <?php if (!empty($person['death']["place"])) { ?>
                                    <br>in <?php echo $person['death']["place"]; ?>
                                <?php } ?>

                                <?php if (!empty($person['death']["cause"])) { ?>
                                    <br><?php echo $person['death']["cause"]; ?>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Age -->
                    <?php if (!empty($person['age'])) { ?>
                        <tr>
                            <td><b>Age:</b></td>
                            <td>
                                <?php echo $person['age']; ?>
                                years <?php if (!empty($person['death'])) { ?> when died<?php } ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Birth name -->
                    <?php if (!empty($person['birth_name'])) { ?>
                        <tr>
                            <td><b>Birth Name:</b></td>
                            <td><?php echo $person['birth_name']; ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Nick name(s) -->
                    <?php if (!empty($person['nick_names'])) { ?>
                        <tr>
                            <td><b>Nicknames:</b></td>
                            <td><?php echo implode(', ', $person['nick_names']); ?></td>
                        </tr>
                    <?php } ?>

                    <!-- AKA name(s) -->
                    <?php if (!empty($person['aka_names'])) { ?>
                        <tr>
                            <td><b>AKA Names:</b></td>
                            <td><?php echo implode(', ', $person['aka_names']); ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Professions -->
                    <?php if (!empty($person['professions'])) { ?>
                        <tr>
                            <td><b>Professions:</b></td>
                            <td><?php echo implode(', ', $person['professions']); ?></td>
                        </tr>
                    <?php } ?>

                    <!-- Body Height -->
                    <?php if (!empty($person['body_height'])) { ?>
                        <tr>
                            <td><b>Body Height:</b></td>
                            <td><?php echo $person['body_height']["imperial"]; ?>
                                - <?php echo $person['body_height']["metric"]; ?>
                                (<?php echo $person['body_height']["metric_cm"]; ?> cm)
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Mini Bio -->
                    <?php if (!empty($person['bio'])) { ?>
                        <tr>
                            <td><b>Mini Bio:</b></td>
                            <td>
                                <?php echo $person['bio'][0]['text']; ?>
                                <?php if (isset($person['bio'][0]['author'])) { ?>
                                    <br>(Written by: <?php echo $person['bio'][0]['author']; ?>)
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Spouse(s) -->
                    <?php if (!empty($person['spouses'])) { ?>
                        <tr>
                            <td><b>Spouse(s):</b></td>
                            <td>
                                <ul>
                                    <?php foreach ($person['spouses'] as $spouse) { ?>
                                        <li>
                                            <a href="name.php?id=<?php echo $spouse['id']; ?>"><?php echo $spouse['name']; ?></a>
                                            (<?php echo $spouse['date_text']; ?>) -
                                            <?php if (!empty($spouse['children'])) { ?>Children: <?php echo $spouse['children']; ?> -<?php } ?>
                                            <?php if (!empty($spouse['comment'])) { ?><?php echo $spouse['comment'][0]; ?><?php } ?>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Children -->
                    <?php if (!empty($person['children'])) { ?>
                        <tr>
                            <td><b>Children:</b></td>
                            <td>
                                <ul>
                                    <?php foreach ($person['children'] as $child) { ?>
                                        <li>
                                            <?php if (!empty($child['id'])) { ?>
                                                <a href="name.php?id=<?php echo $child['id']; ?>"><?php echo $child['name']; ?></a>
                                            <?php } else { ?>
                                                <?php echo $child['name']; ?>
                                            <?php } ?>
                                            (<?php echo $child['type']; ?>)
                                        </li>
                                    <?php } ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Parents -->
                    <?php if (!empty($person['parents'])) { ?>
                        <tr>
                            <td><b>Parents:</b></td>
                            <td>
                                <ul>
                                    <?php foreach ($person['parents'] as $parent) { ?>
                                        <li>
                                            <?php if (!empty($parent['id'])) { ?>
                                                <a href="name.php?id=<?php echo $parent['id']; ?>"><?php echo $parent['name']; ?></a>
                                            <?php } else { ?>
                                                <?php echo $parent['name']; ?>
                                            <?php } ?>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Relatives -->
                    <?php if (!empty($person['relatives'])) { ?>
                        <tr>
                            <td><b>Relatives:</b></td>
                            <td>
                                <ul>
                                    <?php foreach ($person['relatives'] as $relative) { ?>
                                        <li>
                                            <?php if (!empty($relative['id'])) { ?>
                                                <a href="name.php?id=<?php echo $relative['id']; ?>"><?php echo $relative['name']; ?></a>
                                            <?php } else { ?>
                                                <?php echo $relative['name']; ?>
                                            <?php } ?>
                                            (<?php echo $relative['type']; ?>)
                                        </li>
                                    <?php } ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Trivia -->
                    <?php if (!empty($person['trivia'])) { ?>
                        <tr>
                            <td><b>Trivia:</b></td>
                            <td>
                                <ul>
                                    <?php foreach ($person['trivia'] as $key => $trivia) { ?>
                                        <?php if ($key > 5) {
                                            break;
                                        } ?>
                                        <li>
                                            <?php echo $trivia; ?>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Quotes -->
                    <?php if (!empty($person['quotes'])) { ?>
                        <tr>
                            <td><b>Quotes:</b></td>
                            <td>
                                <ul>
                                    <?php foreach ($person['quotes'] as $key => $quote) { ?>
                                        <?php if ($key > 5) {
                                            break;
                                        } ?>
                                        <li>
                                            <?php echo $quote; ?>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Trademarks -->
                    <?php if (!empty($person['trademarks'])) { ?>
                        <tr>
                            <td><b>Trademarks:</b></td>
                            <td>
                                <ul>
                                    <?php foreach ($person['trademarks'] as $key => $trademark) { ?>
                                        <?php if ($key > 5) {
                                            break;
                                        } ?>
                                        <li>
                                            <?php echo $trademark; ?>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Salaries -->
                    <?php if (!empty($person['salaries'])) { ?>
                        <tr>
                            <td><b>Salaries:</b></td>
                            <td>
                                <ul>
                                    <?php foreach ($person['salaries'] as $salary) { ?>
                                        <li>
                                            <a href="title.php?id=<?php echo $salary['id']; ?>"><?php echo $salary['title']; ?>
                                                (<?php echo $salary['year']; ?>)</a>
                                            <?php echo number_format($salary['amount']); ?> <?php echo $salary['currency']; ?>
                                            <?php if (!empty($salary['comment'])) { ?><?php echo $salary['comment'][0]; ?><?php } ?>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <style>
                details {
                    border: 1px solid #aaa;
                    border-radius: 4px;
                    padding: 0.5em 0.5em 0;
                    margin-bottom: 5px;
                }

                summary {
                    font-weight: bold;
                    margin: -0.5em -0.5em 0;
                    padding: 0.5em;
                    background: #f3f3f3;
                    border-radius: 4px;
                    cursor: pointer;
                }

                details[open] {
                    padding: 0.5em;
                }

                details[open] summary {
                    border-bottom: 1px solid #aaa;
                    margin-bottom: 0.5em;
                }

                details div {
                    padding: 0 0 0 20px;
                }
            </style>

            <!-- Credits -->
            <?php if (!empty($person['credits'])) { ?>
                <div class="head-title">Credits</div>
                <div class="w-full">
                    <?php foreach ($person['credits'] as $key => $items) { ?>
                        <?php if (count($items)) { ?>
                            <details>
                                <summary><?php echo $key; ?></summary>
                                <div>
                                    <ul>
                                        <?php foreach ($items as $item) { ?>
                                            <li>
                                                <a href="title.php?id=<?php echo $item['id']; ?>"><?php echo $item['title']; ?>
                                                    (<?php echo $item['year']; ?>)</a>
                                                <?php if (!empty($item['jobs'])) { ?><?php echo $item['jobs'][0]; ?><?php } ?>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </details>
                        <?php } ?>
                    <?php } ?>
                </div>
            <?php } ?>

            <!-- Credit known for -->
            <?php if (!empty($person['credit_known_for'])) { ?>
                <div class="head-title">Known for</div>
                <div class="grid-box-4">
                    <?php foreach ($person['credit_known_for'] as $key => $item) { ?>
                        <div class="video-box">
                            <a href="title.php?id=<?php echo $item['id']; ?>" class="title font-bold">
                                <div class="thumbnail title-poster">
                                    <?php if ($item['image']) { ?>
                                        <img src="<?php
                                        echo $image->makeThumbnail($item['image']['url'], $item['image']['width'], $item['image']['height'], 280, 414);
                                        ?>" alt="<?php echo $item['title']; ?>" loading="lazy">
                                    <?php } ?>

                                    <?php if ($item['characters']) { ?>
                                        <div class="bottom-label"><?php echo $item['characters'][0]; ?></div>
                                    <?php } ?>
                                </div>

                                <div class="title font-bold">
                                    <?php echo $item['title']; ?> (<?php echo $item['year']; ?>)
                                </div>
                            </a>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <!-- Awards -->
            <?php if (!empty($person['awards'])) { ?>
                <div class="head-title">Awards & Events - <?php echo $person['awards']['stats']['win']; ?> wins & <?php echo $person['awards']['stats']['nom']; ?> nominations</div>
                <div class="w-full">
                    <?php foreach ($person['awards']['events'] as $items) { ?>
                        <?php if (count($items)) { ?>
                            <details>
                                <summary><?php echo $items[0]['event_name']; ?> (<?php echo $items[0]['name']; ?>) (<?php echo $items[0]['id']; ?>)</summary>
                                <div>
                                    <ul>
                                        <?php foreach ($items as $item) { ?>
                                            <li>
                                                <?php echo $item['year']; ?> -

                                                <?php if ($item['category']) { ?>
                                                    <?php echo $item['category']; ?> -
                                                <?php } ?>

                                                <?php if ($item['notes']) { ?>
                                                    <?php echo $item['notes']; ?> -
                                                <?php } ?>

                                                <?php echo $item['conclusion']; ?>

                                                <?php if (!empty($item['titles'])) { ?>
                                                    -
                                                    <?php foreach ($item['titles'] as $title) { ?>
                                                        <a href="title.php?id=<?php echo $title['id']; ?>"><?php echo $title['title']; ?></a>
                                                    <?php } ?>
                                                <?php } ?>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </details>
                        <?php } ?>
                    <?php } ?>
                </div>
            <?php } ?>

            <!-- Images -->
            <?php if (!empty($person['images'])) { ?>
                <div class="head-title">Images</div>
                <div class="grid-box-4">
                    <?php foreach ($person['images'] as $key => $item) { ?>
                        <div class="video-box">
                            <div class="thumbnail">
                                <?php if ($item['image']) { ?>
                                    <img src="<?php
                                    echo $image->makeThumbnail($item['image']['url'], $item['image']['width'], $item['image']['height'], 500, 300);
                                    ?>" alt="<?php echo $item['caption']; ?>" loading="lazy">
                                <?php } ?>

                                <?php if ($item['copyright']) { ?>
                                    <div class="bottom-label">© <?php echo $item['copyright']; ?></div>
                                <?php } ?>
                            </div>

                            <div class="title font-bold">
                                <?php echo $item['caption']; ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <!-- Videos -->
            <?php if (!empty($person['videos'])) { ?>
                <div class="head-title">Videos</div>
                <div class="grid-box-4">
                    <?php foreach ($person['videos'] as $key => $item) { ?>
                        <div class="video-box">
                            <div class="thumbnail">
                                <a href="video.php?id=<?php echo $item['id']; ?>">
                                    <?php if ($item['thumbnail']) { ?>
                                        <img src="<?php
                                        echo $image->makeThumbnail($item['thumbnail']['url'], $item['thumbnail']['width'], $item['thumbnail']['height'], 500, 281);
                                        ?>" alt="<?php echo $item['title']; ?>" loading="lazy">
                                    <?php } ?>

                                    <div class="top-label"><?php echo date('Y-m-d H:i', strtotime($item['created_date'])); ?></div>

                                    <div class="bottom-label"><?php echo $item['content_type']; ?>
                                        - <?php echo $item['runtime_formatted']; ?></div>
                                </a>
                            </div>

                            <a href="video.php?id=<?php echo $item['id']; ?>" class="title font-bold">
                                <?php echo $item['title']; ?>
                            </a>

                            <a href="title.php?id=<?php echo $item['primary_title']['id']; ?>" class="title">
                                <?php echo $item['primary_title']['title']; ?>
                            </a>

                            <time><?php echo $item['primary_title']['year']; ?></time>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <!-- News -->
            <?php if (!empty($person['news'])) { ?>
                <div class="head-title">News</div>
                <div class="grid-box-4">
                    <?php foreach ($person['news'] as $key => $item) { ?>
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

                                <a href="<?php echo $item['source_home_url']; ?>" target="_blank" class="sourceLabel">
                                    <?php echo $item['source_label']; ?>
                                </a>
                            </div>

                            <a href="<?php echo $item['source_url']; ?>" target="_blank" class="title">
                                <?php echo $item['title']; ?>
                            </a>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

        </div>
    </div>
</div>

</body>
</html>