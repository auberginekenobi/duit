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
    $userArray[$user_id] = newUser;
    
    if (!array_key_exists($user_id,$tagArray)) {
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

?>