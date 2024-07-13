<?php

use Hooshid\ImdbScraper\Base\Config;
use Hooshid\ImdbScraper\NameSearch;

require __DIR__ . "/../vendor/autoload.php";

if (count($_GET) > 0) {
    $config = new Config();
    $config->language = 'en-US,en';
    $nameSearch = new NameSearch($config);
    $result = $nameSearch->search($_GET);

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
    <title>Name search</title>
    <link rel="stylesheet" href="/example/style.css">
</head>
<body>

<a href="/example" class="back-page">Go back</a>
<a href="/example/name-search.php?<?php echo http_build_query($_GET); ?>&output=json" class="output-json-link">JSON
    Format</a>

<div class="container">
    <div class="boxed" style="max-width: 700px;">
        <h2 class="text-center pb-30">Name Search</h2>

        <form action="/example/name-search.php" method="get">
            <div class="form-group">
                <label for="name">Name:</label>
                <input class="form-field" type="text" id="name" name="name" maxlength="50"
                       placeholder="Search name" value="<?php echo @strip_tags($_GET['name']); ?>">
            </div>

            <div class="form-group">
                <label for="birth_monthday">Birthday:</label>
                <input class="form-field" type="text" id="birth_monthday" name="birth_monthday" maxlength="50"
                       placeholder="Format: MM-DD" value="<?php echo @strip_tags($_GET['birth_monthday']); ?>">
            </div>

            <div class="form-group">
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" class="form-field">
                    <option value="">All</option>
                    <option value="male" <?php if (@$_GET['gender'] == "male") {
                        echo " selected";
                    } ?>>Male
                    </option>
                    <option value="female" <?php if (@$_GET['gender'] == "female") {
                        echo " selected";
                    } ?>>Female
                    </option>
                </select>
            </div>

            <div class="row">
                <input type="submit" value="Search">
            </div>
        </form>
    </div>

    <div class="boxed">
        <h2 class="text-center pb-30">Result</h2>

        <div class="flex-container">
            <table class="table">
                <tr>
                    <th>Index</th>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Id</th>
                    <th>Job</th>
                    <th>Bio</th>
                </tr>
                <?php foreach ($result as $row) { ?>
                    <tr>
                        <td><?php echo $row['index']; ?></td>
                        <td>
                            <?php if ($row['photo']) { ?>
                                <img src="<?php echo $row['photo']['thumbnail']; ?>" alt="<?php echo $row['name']; ?>">
                            <?php } ?>
                        </td>
                        <td><a href="name.php?id=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></td>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['job']; ?></td>
                        <td><?php echo $row['bio']; ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>

    </div>
</div>

</body>
</html>