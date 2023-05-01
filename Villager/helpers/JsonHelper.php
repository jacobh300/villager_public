<?php

class JsonHelper {

// Function to encode an array to JSON format
public static function encode($array) {
    return json_encode($array);
}

// Function to decode a JSON string to an array
public static function decode($json_string, $assoc = true) {
    return json_decode($json_string, $assoc);
}

// Function to read JSON data from a file and decode it to an array
public static function readJsonFile($filename, $assoc = true) {
    $json_string = file_get_contents($filename);
    return self::decode($json_string, $assoc);
}

// Function to write an array to a JSON file
public static function writeJsonFile($filename, $array) {
    $json_string = self::encode($array);
    file_put_contents($filename, $json_string);
}


public static function printDecodedJson($jsonString) {
    $decodedJson = json_decode($jsonString, true);
    if ($decodedJson === null) {
      echo "Error decoding JSON: " . json_last_error_msg();
    } else {
      print_r($decodedJson);
    }
  }

}

?>