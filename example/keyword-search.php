<?php

use Hooshid\ImdbScraper\KeywordSearch;

require __DIR__ . "/../vendor/autoload.php";

if (!empty($_GET['keyword'])) {
    $keywordSearch = new KeywordSearch();
    $results = $keywordSearch->search($_GET['keyword']);

    if (isset($_GET["output"])) {
        header("Content-Type: application/json");
        echo json_encode($results);
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
    <title>Keyword search</title>
    <link rel="stylesheet" href="/example/style.css">
</head>
<body>

<a href="/example" class="back-page">Go back</a>
<a href="/example/keyword-search.php?<?php echo http_build_query($_GET); ?>&output=json" class="output-json-link">JSON
    Format</a>

<div class="container">
    <div class="boxed">
        <h2 class="text-center pb-30">Result (<?php echo ucwords($_GET['keyword'], ' '); ?>)</h2>

        <div class="flex-container">
            <table class="table">
                <tr>
                    <th>Id</th>
                    <th>Keyword</th>
                    <th>Total titles</th>
                </tr>
                <?php foreach ($results as $result) { ?>
                    <tr>
                        <td><?php echo $result['id']; ?></td>
                        <td>
                            <a href="title-search.php?keywords=<?php echo $result['keyword']; ?>"><?php echo $result['keyword']; ?></a>
                        </td>
                        <td><?php echo number_format($result['total_titles']); ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>