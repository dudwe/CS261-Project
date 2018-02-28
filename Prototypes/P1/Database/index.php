<!DOCTYPE html>

<head>

</head>

<body>
    <h1>TraderBot</h1>

    <form action="reset.php">
        <input type="submit" value="Initialise/Reset">
    </form>

    <form action="ask_query.php" method="post">
        <input type="text" name="query" placeholder="Full query string">
        <input type="text" name="intent" placeholder="Intent">
        <input type="text" name="entity" placeholder="Entity (stock ticker or sector name)">
        <input type="submit" value="Submit query">
    </form>

    <form action="get_suggestion.php">
        <input type="submit" value="Suggest me a query!">
    </form>

</body>

</html>
