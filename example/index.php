<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
    <title>IMDb</title>
    <link rel="stylesheet" href="/example/style.css">
</head>
<body>

<div class="container">
    <div class="boxed" style="max-width: 700px;">
        <h2 class="text-center pb-30">Search</h2>

        <form action="/example/search.php" method="get">
            <div class="form-group">
                <label for="search">Search:</label>
                <input class="form-field" type="text" id="search" name="search" maxlength="50" placeholder="Search or enter title or name id ie:(tt2386490 or nm1706767)">
            </div>

            <div class="form-group">
                <label for="type">Type:</label>
                <select id="type" name="type" class="form-field">
                    <option value="title">Movie</option>
                    <option value="name">Name</option>
                    <option value="episode">Episode</option>
                </select>
            </div>

            <div class="row">
                <input type="submit" value="Search">
            </div>
        </form>
    </div>

    <div class="boxed" style="max-width: 700px;">
        <h2 class="text-center pb-30">Examples</h2>

        <div class="menu-links">
            <a href="/example/chart-boxoffice.php">Chart: BoxOffice</a>
            <a href="/example/chart-top-250-movies.php">Chart: Top 250 Movies</a>

            <a href="/example/name.php?id=nm0000134">Name: Robert De Niro</a>
            <a href="/example/name.php?id=nm1297015">Name: Emma Stone</a>
            <a href="/example/name.php?id=nm0908094">Name: Paul Walker</a>

            <a href="/example/name-search.php?name=Emma">Search name: Emma</a>
            <a href="/example/name-search.php?birth_monthday=<?php echo date("m-d"); ?>">Search name: Born today</a>

            <a href="/example/title.php?id=tt0133093">Movie: The Matrix (1999)</a>
            <a href="/example/title.php?id=tt3228774">Movie: Cruella (2021)</a>
            <a href="/example/title.php?id=tt0944947">TV Series: Game of Thrones (2011-2019)</a>
            <a href="/example/title.php?id=tt10048342">TV Mini Series: The Queen's Gambit (2020)</a>
        </div>
    </div>

</div>

</body>
</html>
