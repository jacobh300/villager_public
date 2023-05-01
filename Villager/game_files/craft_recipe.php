<?php

    require_once('helpers/Database.php');
    require_once('helpers/JsonHelper.php');

    //Parameters
    //user 
    $user_id = 1;
    //recipe to craft
    $recipe_id = 1;

    $db = new Database();
    $sql = "SELECT name, recipe_components, recipe_output FROM recipes WHERE id = ?";
    $params = [$recipe_id];

    $result = $db->executePreparedQuery($sql, $params);
    $row = mysqli_fetch_assoc($result);

    //Remove Recipe Components from users items
    $item_stack_array = JsonHelper::decode($row["recipe_components"]);  
    $recipe_output = JsonHelper::decode($row["recipe_output"]);  
    JsonHelper::printDecodedJson($row["recipe_output"]);


    $db->begin_transaction();
    try{

        $items_updated = $db->remove_objects_from_user($user_id, $item_stack_array);
        if($items_updated) $db->upsertObjectsToUser($user_id, $recipe_output);
        $db->commit();   
    }catch(Exception $e){
        $db->rollback();
        die("Query Failed: " . $e->getMessage());
    }



?>
