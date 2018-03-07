<!DOCTYPE html>

<head>

</head>

<body>
    <h1>TraderBot</h1>

    <form action="../reset.php">
        <input type="submit" value="Initialise/Reset">
    </form>

    <form action="ask_query.php" method="post">
        <input type="text" name="query" placeholder="Full query string">
        <input type="text" name="intent" placeholder="Intent">
        <input type="text" name="entity" placeholder="Entity (stock ticker or sector name)">
        <input type="submit" value="Submit query">
    </form>

    <form action="wrong_entity.php" method="post">
        <input type="text" name="entity" placeholder="Invalid entity">
        <input type="submit" value="Submit invalid entity">
    </form>

    <form action="get_suggestion.php">
        <input type="submit" value="Suggest me a query!">
    </form>

<?php
$data = array(
    "companyList" => array(
        array(
            "id" => 1,
            "fav" => 1,
            "poll_rate" => "5 Minutes"
        ),
        array(
            "id" => 2,
            "fav" => 1,
            "poll_rate" => "1 Hour"
        )
    ),
    "sectorList" => array(
        array(
            "id" => 1,
            "fav" => 0
        ),
        array(
            "id" => 2,
            "fav" => 1
        )
    )
);

$json = json_encode($data);

echo $json . "<BR>";

include "../interface.php";

$conn = db_connection();

$faves = get_faves($conn);
echo $faves;

//update_fav_tables($conn, $json);

?>

</body>

</html>
