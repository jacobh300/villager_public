<?php

    require_once('helpers/database.php');


    //parameters
    $user_id = 1;


    $db = new Database();
    
    //Query
    $sql = "SELECT items.item_id, items.name, user_items.amount
    FROM user_items
    INNER JOIN items
    ON user_items.item_id = items.item_id
    WHERE user_items.user_id = $user_id";

    $result = $db->executeQuery($sql);

    while($row = $result->fetch_assoc()){
        echo "Name: ". $row["name"] . " | Amount : " . $row["amount"] . " |\n";       
    }




?>
