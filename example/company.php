<?php

use Hooshid\ImdbScraper\Company;

require __DIR__ . "/../vendor/autoload.php";

if (!empty($_GET['id'])) {
    $companySearch = new Company();
    $result = $companySearch->getInfo($_GET['id']);

    if (isset($_GET["output"])) {
        header("Content-Type: application/json");
        echo json_encode($result);
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
    <title>Company profile</title>
    <link rel="stylesheet" href="/example/style.css">
</head>
<body>

<a href="/example" class="back-page">Go back</a>
<a href="/example/company.php?<?php echo http_build_query($_GET); ?>&output=json" class="output-json-link">JSON
    Format</a>

<div class="container">
    <div class="boxed">
        <?php if (empty($result)) { ?>
            <div class="alert">No results!</div>
        <?php } else { ?>
            <h2 class="text-center pb-30">Company profile (<?php echo $result['name']; ?>)</h2>

            <div class="flex-container">
                <table class="table">
                    <!-- ID -->
                    <tr>
                        <td style="width: 160px;"><b>IMDb Company id:</b></td>
                        <td><?php echo $result['id']; ?> (<a
                                    href="title-search.php?companies=<?php echo $result['id']; ?>">Title
                                Search</a>)
                        </td>
                    </tr>

                    <!-- Name -->
                    <tr>
                        <td style="width: 160px;"><b>Name:</b></td>
                        <td><?php echo $result['name']; ?></td>
                    </tr>

                    <!-- Rank -->
                    <tr>
                        <td style="width: 160px;"><b>Rank:</b></td>
                        <td>
                            <?php if ($result['rank']["current_rank"]) { ?>
                                Current rank: <?php echo $result['rank']["current_rank"]; ?><br>
                                <?php if ($result['rank']["change_direction"]) { ?>
                                    Change Direction: <?php echo $result['rank']["change_direction"]; ?><br>
                                <?php } ?>
                                <?php if ($result['rank']["difference"]) { ?>
                                    Difference: <?php echo $result['rank']["difference"]; ?><br>
                                <?php } ?>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>
                    </tr>

                    <!-- Country -->
                    <tr>
                        <td style="width: 160px;"><b>Country:</b></td>
                        <td><?php echo $result['country']; ?></td>
                    </tr>

                    <!-- Types -->
                    <tr>
                        <td style="width: 160px;"><b>Types:</b></td>
                        <td><?php echo implode(", ", $result['types']); ?></td>
                    </tr>

                    <!-- Affiliations -->
                    <tr>
                        <td style="width: 160px;"><b>Affiliations:</b></td>
                        <td>
                            <ul>
                                <?php foreach ($result['affiliations'] as $aff) { ?>
                                    <li>
                                        <a href="company.php?id=<?php echo $aff['id']; ?>"><?php echo $aff['name']; ?></a>
                                        (<?php echo $aff['description']; ?>)
                                    </li>
                                <?php } ?>
                            </ul>
                        </td>
                    </tr>

                    <!-- Staff -->
                    <tr>
                        <td style="width: 160px;"><b>Staff:</b></td>
                        <td>
                            <ul>
                                <?php foreach ($result['staff'] as $staff) { ?>
                                    <li>
                                        <a href="name.php?id=<?php echo $staff['id']; ?>"><?php echo $staff['name']; ?></a>
                                        <?php if ($staff['employments'] && $staff['employments'][0]['employmentTitle']) { ?>
                                            (<?php echo $staff['employments'][0]['employmentTitle']; ?>)
                                        <?php } ?>
                                    </li>
                                <?php } ?>
                            </ul>
                        </td>
                    </tr>
                </table>
            </div>
        <?php } ?>
    </div>
</div>
</body>
</html>