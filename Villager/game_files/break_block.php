<?php
    require_once('helpers/database.php');
    

    $user_id = 1;

    $db = new Database();
  
    //Get specifc block owned by the user
    $sql = "SELECT user_blocks.block_id, user_blocks.broken, blocks.item_id
    FROM user_blocks 
    JOIN blocks 
    ON user_blocks.block_id = blocks.block_id
    WHERE user_blocks.user_id = ?";


    $params = [$user_id];
    $result = $db->executePreparedQuery($sql, $params);
    
    //if no user_id registered or multiple blocks registered
    if($result->num_rows == 0 || $result->num_rows > 1) die( "User Block not found or user has multiple blocks registered");

    $row = mysqli_fetch_assoc($result);
    $block_id = $row["block_id"];
    $broken = $row["broken"]; 
    $block_item_id = $row["item_id"];


    //If block is broken exit
    if($broken != 0) die("Block has been broken already");


    $db->begin_transaction();
    try{        
        //Set block to broken status
        set_user_block_state($db, $user_id, $block_id, 1);

        //Create item array for block drop
        $itemArray = array(
            array(
                'table_name' => 'user_items',
                'row_name' => 'item_id',
                'id' => $block_item_id,
                'amount' => 1
            )
        ); 

        $db->upsertObjectsToUser($user_id, $itemArray);
        $db->commit();
    }catch(Exception $e){
        $db->rollback();
        die("Failed to break block: " . $e->getMessage());
    }


    // Function to update a user block to broken or unbroken
    function set_user_block_state($db, $user_id, $block_id, $block_status) {
        $sql = "UPDATE user_blocks SET broken = ? WHERE user_id = ? AND block_id = ?";
        $param = [$block_status, $user_id, $block_id];
        $db->executePreparedQuery($sql, $param);
    }






?>
