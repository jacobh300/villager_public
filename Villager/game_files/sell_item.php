<?php
    require_once('helpers/database.php');
    require_once('helpers/JsonHelper.php');

    //Parameters
    //user 
    $user_id = 1;
  
    //Test Parameters
    //item to sell
    $item_id = 0;
    //amount to sell
    $item_amount = 1;

    if (!is_numeric($item_id) || !is_numeric($item_amount)) {
        die("Invalid input format");
    }   
    
    
    $itemArray = array(
        array(
            'table_name' => 'user_items',
            'row_name' => 'item_id',
            'id' => $item_id,
            'amount' => $item_amount
        )
    );
    

    $db = new Database();

    $db->begin_transaction();
    try{
        //Throws exception if remove object failed
        $db->remove_objects_from_user($user_id, $itemArray);
        give_currency_from_item($db, $item_id, $user_id);
        $db->commit();
    }catch(Exception $e){
        $db->rollback();
        die($e->getMessage());
    }



   //Gives user currency of the amount associated with a item id
   function give_currency_from_item($db, $item_id, $user_id){
    $sql = "SELECT currency_id, currency_amount FROM items WHERE item_id = $item_id";
    $result = $db->executeQuery($sql);

    $row = mysqli_fetch_assoc($result);
    $currency_id = $row["currency_id"]; 
    $currency_amount = $row["currency_amount"];

    $itemArray = array(
      array(
          'table_name' => 'user_currency',
          'row_name' => 'currency_id',
          'id' => $currency_id,
          'amount' => $currency_amount
      )
    );

    $db->upsertObjectsToUser($user_id, $itemArray);
    echo("Gave user currency");
}





?>
