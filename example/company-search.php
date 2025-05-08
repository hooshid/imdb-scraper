<?php

use Hooshid\ImdbScraper\CompanySearch;

require __DIR__ . "/../vendor/autoload.php";

if (!empty($_GET['company'])) {
    $companySearch = new CompanySearch();
    $results = $companySearch->search($_GET['company']);

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
    <title>Company search</title>
    <link rel="stylesheet" href="/example/style.css">
</head>
<body>

<a href="/example" class="back-page">Go back</a>
<a href="/example/company-search.php?<?php echo http_build_query($_GET); ?>&output=json" class="output-json-link">JSON
    Format</a>

<div class="container">
    <div class="boxed">
        <h2 class="text-center pb-30">Result (<?php echo ucwords($_GET['company'], ' '); ?>)</h2>

        <div class="flex-container">
            <table class="table">
                <tr>
                    <th>Id</th>
                    <th>Rank</th>
                    <th>Company name</th>
                    <th>country</th>
                    <th>Types</th>
                </tr>
                <?php foreach ($results as $result) { ?>
                    <tr>
                        <td><?php echo $result['id']; ?></td>
                        <td><?php echo $result['rank']['current_rank']; ?></td>
                        <td>
                            <a href="title-search.php?companies=<?php echo $result['id']; ?>"><?php echo $result['name']; ?></a>
                        </td>
                        <td><?php echo $result['country']; ?></td>
                        <td><?php echo implode(", ", $result['types']); ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>