<?php

/**
 * db-mapper-user.php
 *
 * Performs central interactions with the database as well as maintains PHP
 * objects/arrays for data and means of accessing them.
 * Only does so for the User table. 
 * 
 * Required by db-mapper.php
 *
 * PHP v5.6
 *
 * @author    Kelli Rockwell <kellirockwell@mail.com>, Patrick Shao <shao.pat@gmail.com>, and Owen Chapman <osc12013@pomona.edu>
 * @copyright 2017 DUiT
 * @since     File available since the dawn of the Fourth Age
 */

//////////////////////////////////////////////////////////////////
require("user-class.php");

/**
 * Function getAllUsers
 *
 * Fetches all tags from the database and stores them as user objects in the
 * returned array
 * 
 * @return [array] The array holding all of the user objects, each of whose keys
 * corresponds to the user's user_id
 */
function getAllUsers() {

	// Create new array to hold the du's
	$allusers = array();

	// Query statement for large, formatted table
	$queryStatement = "
	SELECT user_id,
	       user_name
	FROM   users
	GROUP  BY user_name 
	ORDER  BY user_id ASC";
    
    // Query the database for all du's
	$result = query($queryStatement, "getAllUsers()");
    
    // While there is another non-null row of the query result
    while ($currRow = $result->fetch_assoc()) {
		// Remember the current user's user_id
		$user_id = $currRow['user_id'];
		$user_name = $currRow['user_name'];
    	// Create a new user
    	$newUser = new user($user_id,$user_name);
    	// Store user in array at key that is user_id
    	$all[$user_id] = $newUser;   
	}
    
    // Close result
	$result->close();

	// Done
	return $all;
}

/** Function addUser
 *
 * Adds a new tag with the specified set of properties at both object and db levels.
 *
 * Example call:
 * $allusers = addUser(array(
 *                  'user_id' => 'firebase mumbo jumbo'
 * 					'user_name' => 'Jimbo the Amazing Guy'
 * 				), $allusers);
 *
 * @param [array(user)]                $tagArray Array of user objects to take new user
 * @global [$log | The open log file]
 * @return [array] $userArray with the new du element added
 */
function addUser($parameters, $userArray = NULL) {
    
    global $log;
    
    // If userArray is not specified, add it to all tags
    $isAll = ($userArray) ? false : true;
    $userArray = ($userArray) ?: $GLOBALS['allusers'];
    
    // Record add call
    $addAlert      = date("Y-m-d H:i:s T", time()) . " ";
	$addAlert     .= "Preparing to add new user with parameters:\n";

	// Unpack parameter array passed
	foreach ($parameters as $p => $v) {
		$addAlert .= "	";
		$addAlert .= var_export($p, true) . " => ";
		$addAlert .= var_export($v, true) . "\n";
	}
	fwrite($log, $addAlert, 4096);
    
    // Preprocess parameters
    $p = preprocessUser($parameters);
    
    $user_id = $p['user_id'];
    
    // Create new user
    $newUser = new user($p['user_id'],$p['user_name']);
    
    // Store user in array at user_id key
    $userArray[$user_id] = $newUser;
    
    if (!array_key_exists($user_id,$userArray)) {
	    // Handle error
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new user to userArray. Current state of $newUser is\n";
		$output .= "	" . var_export($newUser, true);
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	} else {
		// Record success
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Added new user to " . (($isAll) ? "\$allusers" : "userArray");
		$output .= " with user_id of '" . $user_id . "'.\n";
		fwrite($log, $output, 2048);
	}
    
    // Push the new du and its properties to the database
	$userArray[$user_id]->addToDB();

	// Done
	return $userArray;

}

/**
 * Function preprocessUser
 *
 */
function preprocessUser($parameters) {
    global $log;
    
    $p = $parameters;
    
    // Field 'user_id'            : REQUIRED (varchar user_id)
    // Check if provided user_id matches an extant user_id
	// Handle case where user_id is not specified
	if (!isset($p['user_id'])) {
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new user: no user id found specified in input. Input was:\n";
		$output .= "	" . var_export($parameters, true);
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	} else {
        $queryStatement = "
        SELECT user_id
        FROM users
        WHERE user_id = '" . $p['user_id'] . "'";
        $result = query($queryStatement, "preprocess()");
        $currRow = $result->fetch_assoc();
        if ($currRow != NULL){
            $output  = date("Y-m-d H:i:s T", time());
            $output .= " Could not add new tag: input user id already corresponds to an extant user. Input was:\n";
            $output .= "	" . var_export($parameters, true);
            // Write to log file and kill process
            fwrite($log, $output, 2048);
            exit($output);
        }
    }
	
	// Field 'user_name'             : REQUIRED (string)
	// Handle case where user_name is not specified
	if (!isset($p['user_name'])) {
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new uer: no name found specified in input. Input was:\n";
		$output .= "	" . var_export($parameters, true);
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	}
	
	return $p;
}

/**
* Function deleteTag
* 
* Delete a tag with the provided id
 * @param [int]       $id      Id of tag to be removed
 * @param [array(du)] $tagArray Array of du objects to delete du from
 * @global [$log | The open log file]
 * @return [array] $duArray with the specified du element removed
 */
function deleteUser($id, $userArray = NULL) {
	global $log;
	
	// If userArray is not specified, delete it from array of all tags
	$isAll = ($userArray) ? false : true;
	$userArray = ($userArray) ?: $GLOBALS['allusers'];
	
	// If no user exists for specified ID
	if (!array_key_exists($id,$userArray)) {
	    // Handle error
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not find user in " . (($isAll) ? "\$allusers" : "userArray");
		$output .= " with user_id of '" . $id . "' to delete.\n";
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	} else {
		// Remember current element to delete
		$thisuser = $userArray[$id];
		// Remove it from tag array
		unset($userArray[$id]);
		// Record success
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Deleted user from " . (($isAll) ? "\$all" : "userArray");
		$output .= " with user_id of '" . $id . "'.\n";
		fwrite($log, $output, 2048);
		// Remove it from DB
		$thisuser->deleteFromDB();
	}

	// Done
	return $userArray;
}

?>