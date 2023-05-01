<?php

    require_once('helpers/Database.php');
    require_once('helpers/JsonHelper.php');


class UserBuilding{
    private $db; 
    private $user_id;
    private $building_id;


    public function __construct($user_id, $building_id){
        if (!is_numeric($user_id) || !is_numeric($building_id)) die("Invalid input format");
 
        $this->db = new Database();
        $this->user_id = $user_id;
        $this->building_id = $building_id;
    }


    /**
     * Returns the building information of the current users building associated with building id
     */
    public function get_user_building_info(){
        //Check if user has building via unique building id
        $sql = "SELECT * FROM user_buildings WHERE id = ? AND user_id = ?";
        $params = [$this->building_id, $this->user_id];
        $result = $this->db->executePreparedQuery($sql, $params);
        $row = mysqli_fetch_assoc($result);
        return $row;
    }


    /**
     * Retrieves recipe information specified by recipe_id
     * 
     * @param int $recipe_id The recipe id to retrieve information from
     * 
     * @throws Exception If no recipe with the given id exists
     * 
     * @return array|bool|null Returns sqli_result
     * 
     */
    function get_recipe_info_from_id($recipe_id){
        $sql = "SELECT * FROM recipes WHERE id = ?";
        $params = [$recipe_id];
        $result = $this->db->executePreparedQuery($sql, $params);
        $row = mysqli_fetch_assoc($result);
        if(empty($row))  throw new Exception("No recipe found with associated id '" .$recipe_id . "'");
        return $row;
    }


    /**
     * This function updates the collection time and the current crafting recipe id 
     * for a users building.
     * 
     * Null Recipe ID can be used to reset the recipe id to nothing, usually used with 0 collection time
     * to reset the building
     * 
     * @param int $collection_time The unix timestamp for when the recipe will be available to collect
     * @param int|null $recipe_id The recipe ID that the building is currently crafting
     * 
     * @throws Exception If the building does not have the given recipe id (Unless recipe_id is null)
     * 
     * @return bool Returns true if the update to the building was succesful
     */
    function update_building_collection_info($collection_time, $recipe_id){
        if($recipe_id != null){
            //Check if the building recipe is contained in the junction table
            $sql = "SELECT building_type_id
            FROM user_buildings
            INNER JOIN building_recipes
            ON building_recipes.building_id = user_buildings.building_type_id
            WHERE user_buildings.id = ? AND recipe_id = ?";

            $params = [$this->building_id, $recipe_id];
            $result = $this->db->executePreparedQuery($sql, $params);
            $row = mysqli_fetch_assoc($result);

            if(empty($row)) throw new Exception("Building does not have given recipe id");
        }
        
        $sql = "UPDATE user_buildings SET collection_time = ?, current_recipe_id = ? WHERE user_id = ? AND id = ?";
        $params = [$collection_time, $recipe_id , $this->user_id, $this->building_id];
        $this->db->executePreparedQuery($sql, $params);
        return true;
    }



    /**
     * Collects the buildings recipe output if the recipe is finished processing
     * 
     * @throws Exception If the user does not own any building with the specified building_id
     * @throws Exception If building has nothing to collect
     * @throws Exception If the building is still processing
     * 
     * @throws Exception and rollback If reseting building info or giving items to the user fails
     * 
     * @return bool Returns true if the building was sucesfully collected from
     * 
     */
    function collect(){
        //Check if user has specific building with unique id, return the type of building the id is associated with
        $user_building_info = $this->get_user_building_info();
        if($user_building_info <= 0) throw new Exception("User does not own building with specified id");
        
        $building_collection_time = $user_building_info["collection_time"];
        if($building_collection_time == 0) throw new Exception("Building does not have anything to collect");

        $current_time = time();
        if($building_collection_time > $current_time) throw new Exception("Building still processing recipe, remaining time = " . $building_collection_time - $current_time);
        $recipe_id = $user_building_info["current_recipe_id"];

        $recipe_info = $this->get_recipe_info_from_id($recipe_id);
        $recipe_output = JsonHelper::decode($recipe_info["recipe_output"]);  

        $this->db->begin_transaction();
        try{
            //Reset collection time and current_recipe_id
            $this->update_building_collection_info(0, null);
            $this->db->upsertObjectsToUser($this->user_id, $recipe_output);
            $this->db->commit();
        }catch(Exception $e){
            $this->db->rollback();
            throw new Exception("Failed to add recipe out to user: " . $e->getMessage());
        }

        return true;

    }

    

    /**
     * Attempts to start crafting a recipe from the current building
     * 
     * @param int $recipe_id Recipe ID to attempt to craft 
     * 
     * @throws Exception If the user does not own any building with the specified building_id
     * @throws Exception If the building is still processing
     * 
     * @throws Exception and rollback If setting building info or removing items from the user fails
     * 
     */
    function craft_recipe($recipe_id){

        //Check if user has specific building with unique id, return the type of building the id is associated with
        $user_building_info = $this->get_user_building_info();
        if($user_building_info <= 0) throw new Exception("User does not own specified unique building id");

        $building_collection_time = $user_building_info["collection_time"];
        if($building_collection_time != 0) throw new Exception("Building still processing recipe, remaining time = " . $building_collection_time - time());

        //Get Recipe Info
        $recipe_info = $this->get_recipe_info_from_id($recipe_id);
        $item_stack_array = JsonHelper::decode($recipe_info["recipe_components"]);  

        $this->db->begin_transaction();
        try{

            //Throw exception if remopve objects fails
            $this->db->remove_objects_from_user($this->user_id, $item_stack_array);
            $collection_time = time() + $recipe_info["base_crafting_time"];
            //Update process start time to current time and current recipe id    
            $this->update_building_collection_info($collection_time, $recipe_id);
      
            $this->db->commit();
        }catch(Exception $e){
            $this->db->rollback();
            throw new Exception("Failed updating user_building or removing objects from user: " .$e->getMessage());
        }
    }

}

//Parameters
//user 
$user_id = 1;
//building id to collect (Look at owned buildings to see unique building id)
$building_id = 4;

$userBuilding = new UserBuilding($user_id, $building_id);

try{
     //$userBuilding->craft_recipe(0);
     $userBuilding->collect();
}catch(Exception $e){
    echo("Action failed: " . $e->getMessage());
}







?>
