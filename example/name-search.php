<?php

use Hooshid\ImdbScraper\NameSearch;

require __DIR__ . "/../vendor/autoload.php";

if (count($_GET) > 0) {
    $nameSearch = new NameSearch();
    $results = $nameSearch->search([
        'name' => $_GET['name'] ?? '',
        'birthMonthDay' => $_GET['birth_monthday'] ?? '',
        'gender' => $_GET['gender'] ?? '',
        'adult' => $_GET['adult'] ?? 'EXCLUDE_ADULT',
    ]);

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
                    <option value="MALE" <?php if (@$_GET['gender'] == "MALE") {
                        echo " selected";
                    } ?>>Male
                    </option>
                    <option value="FEMALE" <?php if (@$_GET['gender'] == "FEMALE") {
                        echo " selected";
                    } ?>>Female
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="adult">Adult names:</label>
                <select id="adult" name="adult" class="form-field">
                    <option value="EXCLUDE_ADULT" <?php if (@$_GET['adult'] == "EXCLUDE_ADULT") {
                        echo " selected";
                    } ?>>Exclude
                    </option>
                    <option value="INCLUDE_ADULT" <?php if (@$_GET['adult'] == "INCLUDE_ADULT") {
                        echo " selected";
                    } ?>>Include
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
                    <th>Image</th>
                    <th>Name</th>
                    <th>Id</th>
                    <th>Job</th>
                    <th>Bio</th>
                </tr>
                <?php foreach ($results as $result) { ?>
                    <tr>
                        <td><?php echo $result['index']; ?></td>
                        <td>
                            <?php if (!empty($result['imageUrl']['120x120'])) { ?>
                                <img class="name-image" src="<?php echo $result['imageUrl']['120x120']; ?>"
                                     alt="<?php echo $result['name']; ?>" loading="lazy">
                            <?php } ?>
                        </td>
                        <td><a href="name.php?id=<?php echo $result['id']; ?>"><?php echo $result['name']; ?></a></td>
                        <td><?php echo $result['id']; ?></td>
                        <td><?php echo $result['job']; ?></td>
                        <td><?php echo $result['bio']; ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>

    </div>
</div>

</body>
</html>