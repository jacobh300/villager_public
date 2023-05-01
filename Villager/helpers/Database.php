<?php
    require_once('Identity.php');
class Database{
  private $conn;






function __construct($servername = "localhost", $username = "root", $password = "", $dbname = "villager_db") {



  $this->conn = new mysqli($servername, $username, $password, $dbname);
  if ($this->conn->connect_error) {
    die("Connection failed: " . $this->conn->connect_error);
  }



}

  function __destruct() {
    $this->conn->close();
  }


/**
 * Takes an array of variables and returns a string of letters representing each variable type.
 *
 * @param array $params An array of variables
 *
 * @return string A string of letters representing each variable type in the same order as the input array.
 */
  function array_of_vars_to_string($params){
    if (count($params) > 0) {
      $types = "";
      foreach ($params as $param) {
        if (is_int($param)) {
          $types .= "i";
        } elseif (is_float($param)) {
          $types .= "d";
        } elseif (is_string($param)) {
          $types .= "s";
        } else {
          $types .= "b";
        }
      }

    }
      return $types;
  }

/**
 * Takes a prepared sql statement and array of parameters to execute the query.
 * Handles SELECT querys by returning result before checking Exceptions
 *
 * @param string $sql A string representing the prepared SQL statement
 * @param array $params An array of variables to be used as variables in the sql statement
 *
 * @throws Exception If Execution of the query failed
 * @throws Exception If No rows removed by query
 * @throws Exception If No rows added by query
 * @throws Exception If No rows updated by query
 * 
 * @return mysqli_result Returns the result object of the query
 */
  function executePreparedQuery($sql, $params) {
    $stmt = $this->conn->prepare($sql);

      if (!$stmt) {
        die("Prepare failed: " . $this->conn->error);
      }

    $param_types = $this->array_of_vars_to_string($params);
    $stmt->bind_param($param_types, ...$params);


      if (!$stmt->execute()) {
        throw new Exception("Query failed: " . $stmt->error);
      }

      //If SQL statement is SELECT return result
      if (strpos(strtolower(trim($sql)), 'select') === 0) {
        return $stmt->get_result();
      } 
      
      //If SQL statement did not remove row throw new exception
      if ($stmt->affected_rows < 0) {

        $message = "No rows removed by query with following parameters: " . implode(", ", $params) . "\nSQL: " . $sql;
        throw new Exception($message);
      }

      //If SQL statement did not add row throw new exception
      if ($stmt->affected_rows == 0 && strpos(strtolower(trim($sql)), 'insert into') === 0) {

        $message = "No rows added by query with following parameters: " . implode(", ", $params). "\nSQL: " . $sql;
        throw new Exception($message);
      }

      //If SQL statement did not update any row throw new exception
      if ($stmt->affected_rows == 0 && strpos(strtolower(trim($sql)), 'update') === 0) {
        $message = "No rows updated by query with following parameters: " . implode(", ", $params). "\nSQL: " . $sql;
        throw new Exception($message);
      }


    return $stmt->get_result();
  }


/**
   * Removes an array of JSON objects to a specific user_id
   * JSON Object includes "table_name" , "row_name" , "id" , "amount"
   *
   * @param int $user_id The user_id to remove object from
   * @param array $item_stack_array An array of JSON objects to be removed from the specific user_id
   * 
   * @throws Exception If the attempt to remove an object from the user fails.
   * 
   * @return bool Returns true if successfully removed, or throws an exception if the attempt to remove an object to the user fails.
   */
  function remove_objects_from_user($user_id, $item_stack_array) {
    try {

        foreach ($item_stack_array as $item_entry) {

            $table =  $item_entry['table_name'];
            $row_name = $item_entry['row_name'];
            $itemId = $item_entry['id'];      

            //May need to specify what the "amount" row is called (not all tables will call it amount)
            $amount = $item_entry['amount'];

            $sql = "UPDATE {$table} SET amount = amount - ? WHERE user_id = ? AND {$row_name} = ? AND amount >= ?";
            
            $params = [
                $amount,
                $user_id,
                $itemId,
                $amount
            ];

            $this->executePreparedQuery($sql, $params);
        }

        return true;

    } catch (Exception $e) {
        throw new Exception( "Failed to remove object: ". $e->getMessage());    
    }
  }


/**
   * Upserts an array of JSON objects to a specific user_id
   * JSON Object includes "table_name" , "row_name" , "id" , "amount"
   * 
   * @param int $user_id The user_id to upsert onto
   * @param array $item_stack_array An array of JSON objects to be inserted to specific user_id   * 
   * 
   * @throws Exception If the attempt to add an object to the user fails.
   * 
   * @return bool Returns true if successfully upserted, or throws an exception if the attempt to add an object to the user fails.
   */
  function upsertObjectsToUser($user_id, $item_stack_array){
    try{

      foreach ($item_stack_array as $item_entry){

          $table =  $item_entry['table_name'];
          $row_name = $item_entry['row_name'];
          $itemId = $item_entry['id'];      
          $amount = $item_entry['amount'];
        
          // Check if user already has an entry in the table
          $sql = "SELECT * FROM $table WHERE $row_name = $itemId";
          $result = $this->executeQuery($sql);

          // User already has an entry in the table, so update it
          if (mysqli_num_rows($result) > 0) {

              $row = mysqli_fetch_assoc($result);
              $new_amount = $row['amount'] + $amount;
              $sql = "UPDATE {$table} SET amount = ? WHERE user_id = ? AND {$row_name} = ?";
              

              $params = [
                $new_amount,
                $user_id,
                $itemId,
              ];


              $this->executePreparedQuery($sql, $params);

          //User doesn't have an entry in the table, so insert a new one
          } else {
              $sql = "INSERT INTO {$table} (user_id, {$row_name}, amount) VALUES (?, ?, ?)";

              $params = [
                $user_id,
                $itemId,
                $amount
              ];

              $this->executePreparedQuery($sql, $params);
          }

      }
       

        return true;
    }catch(Exception $e){
        throw new Exception( "Attempt to add object to the user failed: " . $e->getMessage());
    }
  }


  function executeQuery($sql) {
    $result = $this->conn->query($sql);
    if (!$result) {
      die("Query failed: " . $this->conn->error);
    }
    return $result;
  }

  function escapeString($string) {
    return $this->conn->real_escape_string($string);
  }


  function prepare($string){
      return $this->conn->prepare($string);
  }


  function begin_transaction(){
    return $this->conn->begin_transaction();
  }

  function rollback(){
    return $this->conn->rollback();
  }

  function commit(){
    return $this->conn->commit();
  }

  function getAffectedRows(){
    return mysqli_affected_rows($this->conn);
  }



}

?>