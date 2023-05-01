<?php
    require_once('helpers/database.php');


    $user_id = 1;

    $db = new Database();
    $sql = "SELECT broken FROM user_blocks WHERE user_id = $user_id";
    $result = $db->executeQuery($sql); 

    //If user already has a user_block entry
    if (mysqli_num_rows($result) > 0) {
        //If block not broken, tell user to break block first
        $row = mysqli_fetch_assoc($result);
        $broken = $row["broken"]; 
        if($broken != 1) die("user already has a block available");

        //Give new block thats not broken
        $rand_block_id = get_random_block_id($db);

        echo("Updating with new block id " . $rand_block_id);
        $sql = "UPDATE user_blocks SET broken = 0, block_id = $rand_block_id  WHERE user_id = $user_id";
        $db->executeQuery($sql);

    //If user does not have a user_block entry yet
    }else{
            // User does not have the item, so add a new row to the inventory table
            $rand_block_id = get_random_block_id($db);
            $sql = "INSERT INTO user_blocks (user_id, block_id, broken) VALUES ($user_id, $rand_block_id, 0)";
            $db->executeQuery($sql);
    }



    function get_random_block_id($db){
        $sql = "SELECT * FROM blocks";
        $result = $db->executeQuery($sql); 
        $number_of_blocks = mysqli_num_rows($result);
        return rand(0, $number_of_blocks-1);
    }







?>
