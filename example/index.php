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
            <a class="badge badge-red" href="/example/chart-boxoffice.php">Chart: BoxOffice</a>
            <a class="badge badge-red" href="/example/chart-list.php?type=TOP_250">Chart: Top 250 Movies</a>
            <a class="badge badge-red" href="/example/chart-list.php?type=TOP_250_TV">Chart: Top 250 TV Shows</a>
            <a class="badge badge-red" href="/example/chart-list.php?type=BOTTOM_100">Chart: Bottom 100 Movies</a>

            <a class="badge badge-green" href="/example/calendar.php?type=MOVIE">Calendar: Upcoming Movies</a>
            <a class="badge badge-green" href="/example/calendar.php?type=TV">Calendar: Upcoming TV Series</a>
            <a class="badge badge-green" href="/example/calendar.php?type=TV_EPISODE">Calendar: Upcoming TV Episodes</a>

            <a class="badge badge-blue" href="/example/name.php?id=nm0000134">Name: Robert De Niro</a>
            <a class="badge badge-blue" href="/example/name.php?id=nm1297015">Name: Emma Stone</a>
            <a class="badge badge-blue" href="/example/name.php?id=nm0908094">Name: Paul Walker</a>

            <a class="badge badge-purple" href="/example/name-search.php?name=Emma">Search name: Emma</a>
            <a class="badge badge-purple" href="/example/name-search.php?birth_monthday=<?php echo date("m-d"); ?>">Search name: Born today</a>
            <a class="badge badge-purple" href="/example/name-search.php?death_date_start=<?php echo date("Y-m-d",time()-86400); ?>&death_date_end=<?php echo date("Y-m-d"); ?>">Search name: Died yesterday</a>

            <a class="badge badge-orange" href="/example/title.php?id=tt0133093">Movie: The Matrix (1999)</a>
            <a class="badge badge-orange" href="/example/title.php?id=tt6723592">Movie: Tenet (2020)</a>
            <a class="badge badge-orange" href="/example/title.php?id=tt3228774">Movie: Cruella (2021)</a>
            <a class="badge badge-orange" href="/example/title.php?id=tt16277242">Movie: Society of the Snow (2023)</a>

            <a class="badge badge-purple" href="/example/title-search.php?searchTerm=Harry Potter">Search title: Harry Potter</a>
            <a class="badge badge-purple" href="/example/title-search.php?searchTerm=Saw&types=movie">Search title: Saw</a>

            <a class="badge badge-pink" href="/example/title.php?id=tt0944947">TV Series: Game of Thrones (2011-2019)</a>
            <a class="badge badge-pink" href="/example/title.php?id=tt10048342">TV Mini Series: The Queen's Gambit (2020)</a>

            <a class="badge badge-yellow" href="/example/video.php?id=vi2051194393">Video: Tenet - Final Trailer</a>
            <a class="badge badge-yellow" href="/example/video.php?id=vi1032782617">Video: The Matrix - Theatrical Trailer</a>
        </div>
    </div>

</div>

</body>
</html>
