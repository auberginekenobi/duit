<?php

/**
 * db-mapper.php
 *
 * Performs central interactions with the database as well as maintains PHP
 * objects/arrays for data and means of accessing them.
 *
 * PHP v5.6
 *
 * @author    Kelli Rockwell <kellirockwell@mail.com>
 * @copyright 2017 DuMi
 * @since     File available since release 0.0.1  
 */


/**
 * Function openLog
 *
 * Sets up log file and records new instance of loading.
 * db-mapper.php
 *
 * @param [boolean] $clearContents Specify whether or not to clear the current
 * contents of the log file
 * @return [resource(file stream)] The open log file
 */
function openLogFile($clearContents = false) {

	// Clear log file, if specified to
	if ($clearContents) file_put_contents("db-history.log", "");

	// Open log file for write only (existing data preserved, file pointer
	// starts at end of file)
	$log = fopen("db-history.log", "a");

	// Record note of new instance of page load
	fwrite($log, "======================================== LOADING DB-MAPPER.PHP ========================================\n");

	return $log;

}


/**
 * Function connect
 *
 * Connects to database using configuration file 'config.ini' and returns the
 * connection, recording information about the success or failure of the
 * process.
 *
 * @global [$log | The open log file]
 * @return [object(mysqli)] The mysqli database connection
 */
function connect() {

	global $log;

	// Define connection as a static variable, to avoid connecting more than once 
    static $connection;

    // If a connection has not yet been established
    if (!isset($connection)) {
		// Load db config file as an array
		$config = parse_ini_file('config.ini'); 

		// Try and connect to the database
		$connection = new mysqli($config['dbhost'], $config['dbuser'], $config['dbpass'], $config['dbname']);

		// Record successful connection
		$success  = date("Y-m-d H:i:s T", time());
		$success .= " User \"" . $config['dbuser'] . "\" connected to database.\n";
		fwrite($log, $success, 256);
	}

	// If connection was not successful
	if ($connection === false || $connection->connect_error) {
	    // Handle error
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " User \"" . $config['dbuser'] . "\" was unable to connect to database. ";
		$output .= mysqli_errno($connection) . ": " . mysqli_error($connection) . "\n";
		// Write to log file and kill process
		fwrite($log, $output, 256);
	    exit($output);
	} 

	// Done
	return $connection;

}


/**
 * Function query
 *
 * Queries the database for a given SQL statement and returns the result,
 * recording any information about the success or failure of the query.
 * 
 * @param  [string] $query    The SQL query statement as a string
 * @param  [string] $function *OPTIONAL* The name of the function executing the query
 * @global [$log | The open log file]
 * @return [object(mysqli_result)] The result of the query       
 */
function query($query, $function = NULL) {

	global $log;

    // Connect to the database
    $connection = connect();

    // Query the database
    $result = $connection->query($query);

    if ($result === false) {
    	// Handle error
    	$output  = date("Y-m-d H:i:s T", time()) . " ";
    	$output .= ($function) ? $function . " was u" : "U"; 
    	$output .= "nable to perform query \"";
    	$output .= $query . "\". ";
		$output .= mysqli_errno($connection) . ": " . mysqli_error($connection) . "\n";
		// Write to log file and kill process
		fwrite($log, $output, 256);
	    exit($output);
    }

    // Record successful query
    $success  = date("Y-m-d H:i:s T", time()) . " ";
    $success .= ($function) ? $function . " p" : "P";
    $success .= "erformed query: " . $query . ".\n";
    fwrite($log, $success, 4096);

    // Done
    return $result;

}


/**
 * Function getAll
 *
 * Fetches all du's from the database and stores them as du objects in the
 * returned array
 * 
 * @return [array] The array holding all of the du objects, each of whose keys
 * corresponds to the du's du_id
 */
function getAll() {

	// Create new array to hold the du's
	$all = array();

	// Query statement for large, formatted table
	$queryStatement = "
	SELECT du_id, 
	       du_timestamp, 
	       du_name, 
	       du_has_date, 
	       du_has_deadline, 
	       du_has_duration, 
	       du_time_start, 
	       du_time_end, 
	       du_priority, 
	       du_enforce_priority, 
	       ( Group_concat(tag_priority SEPARATOR ', ') ) AS tag_priorities, 
	       du_note, 
	       ( Group_concat(tag_name SEPARATOR ', ') )     AS du_tags, 
	       status_type, 
	       status_time_start, 
	       status_time_end, 
	       score 
	FROM   (SELECT d.du_id, 
	               d.du_timestamp, 
	               d.du_name, 
	               d.du_has_date, 
	               d.du_has_deadline, 
	               d.du_has_duration, 
	               d.du_time_start, 
	               d.du_time_end, 
	               d.du_priority, 
	               d.du_enforce_priority, 
	               d.du_note, 
	               t.tag_id, 
	               t.tag_name, 
	               t.tag_priority, 
	               t.tag_note, 
	               u.status_type, 
	               u.status_time_start, 
	               u.status_time_end, 
	               u.score 
	        FROM   dus AS d 
	               LEFT JOIN du_tag_pairs AS p 
	                      ON d.du_id = p.du_id 
	               LEFT JOIN tags AS t 
	                      ON p.tag_id = t.tag_id 
	               LEFT JOIN statuses AS u 
	                      ON d.du_id = u.du_id) AS subq 
	GROUP  BY du_name 
	ORDER  BY du_id ASC";

	// Query the database for all du's
	$result = query($queryStatement, "getAll()");

	// While there is another non-null row of the query result
    while ($currRow = $result->fetch_assoc()) {
    	// Create a new du
    	$newDu = new du();
    	// Remember the current du's du_id
    	$du_id = $currRow['du_id'];
    	// Fill fields of du to match values fetched from the database
    	$newDu->setDuFields($du_id,
    						$currRow['du_timestamp'],
    						$currRow['du_name'],
    						$currRow['du_has_date'],
    						$currRow['du_has_deadline'],
    						$currRow['du_has_duration'],
    						$currRow['du_time_start'],
    						$currRow['du_time_end'],
    						$currRow['du_priority'],
    						$currRow['du_enforce_priority'],
    						$currRow['tag_priorities'],
    						$currRow['du_note'],
    						$currRow['du_tags']);
    	// Store du in array at key that is du_id
    	$all[$du_id] = $newDu;   
	}

	// Close result
	$result->close();

	// Done
	return $all;

}


/**
 * Function displayAsTable
 *
 * Echoes a simple html table of the inputted du's; primarily used for debugging
 * 
 * @param [array(du)] $duArray Array of du objects slated to display in table
 * @return void
 */
function displayAsTable($duArray) {

	// Create variable to hold the table output
	$table  = "";
	// Counter to track number of du's in table
	$number = 0;

	// for each du
	foreach ($duArray as $du) {
		// if it's the first, display the current du preceded by a row of headers
		$table .= ($number == 0) ? $du->displayAsTableRow(TRUE) : $du->displayAsTableRow(FALSE);
		// increment counter
		$number++;
	}

	echo "<table>" .
		 $table .
		 "</table><br /><br />";

}


/**
 * Function addDu
 *
 * Adds a new du with a specified set of properties at both object- and
 * db-levels.
 *
 * Example of how to call the function:
 * $all = addDu(array(
 * 					'du_name' => 'Take out the trash',
 * 					'du_has_date' => 1,
 * 					'du_time_start' => '2017-03-25 00:00:00'
 * 				), $all);
 *
 * All booleans default to false and must be specified 1 otherwise. See function
 * preprocess($parameters) for more details on how $parameters is handled.
 * 
 * @param [array(string => various)] $parameters Array of parameters specifying
 * the properties to be used on the new du. Parameter 'du_name' => 'name' is the
 * only mandatory property. All parameters should be specified in the array as
 * 'field_name' => field_value.
 * @param [array(du)]                $duArray Array of du objects to take new du
 * @global [$log | The open log file]
 * @return [array] $duArray with the new du element added
 */
function addDu($parameters, $duArray = NULL) {

	global $log;

	// If duArray is not specified, add it to array of all du's
	$duArray = ($duArray) ?: $GLOBALS['all'];
	$isAll = ($duArray) ? false : true;

	// Record add call
	$addAlert      = date("Y-m-d H:i:s T", time()) . " ";
	$addAlert     .= "Preparing to add new du with parameters:\n";

	// Unpack parameter array passed
	foreach ($parameters as $p => $v) {
		$addAlert .= "	";
		$addAlert .= var_export($p, true) . " => ";
		$addAlert .= var_export($v, true) . "\n";
	}
	fwrite($log, $addAlert, 4096);

	// Create a new du
	$newDu = new du();

	// Get new ID for the du by adding 1 to the last ID used in the array
	end($duArray);
	$du_id = key($duArray) + 1;
	$parameters['du_id'] = $du_id;

	// Preprocess parameters
	$p = preprocess($parameters);

	// Fill fields of du to match parameter inputs
	$newDu->setDuFields($p['du_id'],
						$p['du_timestamp'],
						$p['du_name'],
						$p['du_has_date'],
						$p['du_has_deadline'],
						$p['du_has_duration'],
						$p['du_time_start'],
						$p['du_time_end'],
						$p['du_priority'],
						$p['du_enforce_priority'],
						$p['tag_priorities'],
						$p['du_note'],
						$p['du_tags']);

	// Store du in array at key that is du_id
	$duArray[$du_id] = $newDu;

	if ($duArray[$du_id] === false) {
	    // Handle error
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new du to duArray. Current state of $newDu is\n";
		$output .= "	" . var_export($newDu, true);
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	} else {
		// Record success
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Added new du to " . (($isAll) ? "\$all" : "duArray");
		$output .= " with du_id of '" . $du_id . "'.\n";
		fwrite($log, $output, 2048);
	}

	// Push the new du and its properties to the database
	$duArray[$du_id]->addToDB();

	// Done
	return $duArray;

}

/**
 * Function preprocess
 *
 * Fills in unspecified properties of $parameters array and handles bad or
 * misformatted inputs. See individual fields for more detailed information on
 * how they are preprocessed.
 *
 * @param [array(string => various)] $parameters Array of parameters specifying
 * the properties to be used on the new du. Parameter 'du_name' => 'name' is the
 * only mandatory property. All parameters should be specified in the array as
 * 'field_name' => field_value.
 * @global [$log | The open log file]
 * @return [array(string => various)] Preprocessed array of parameters
 */
function preprocess($parameters) {

	global $log;

	$p = $parameters;

	// Field 'du_timestamp'        : AUTO SET
	// 
	// Set the timestamp (format "YYYY-MM-DD HH:MM:SS")
	$p['du_timestamp'] = date("Y-m-d H:i:s", time());

	// Field 'du_name'             : REQUIRED (string name)
	// Handle case where du_name is not specified
	if (!isset($p['du_name'])) {
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new du: no name found specified in input. Input was:\n";
		$output .= "	" . var_export($parameters, true);
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	}

	// Field 'du_has_date'         : OPTIONAL (1), DEFAULT 0
	// 
	// Set du_has_date to 0 if it is not specified and handle case where it is
	// mal-specified
	if (!isset($p['du_has_date'])) {
		$p['du_has_date'] = 0;
	} elseif ($p['du_has_date'] != 1) { // Input mal-specified
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new du: bad 'du_has_date' value specified. Input was:\n";
		$output .= "	" . var_export($parameters, true);
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	}

	// Field 'du_has_deadline'     : OPTIONAL (1), DEFAULT 0
	// 
	// Set du_has_deadline to 0 if it is not specified and handle case where it
	// is mal-specified
	if (!isset($p['du_has_deadline'])) {
		$p['du_has_deadline'] = 0;
	} elseif ($p['du_has_deadline'] != 1) { // Input mal-specified
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new du: bad 'du_has_deadline' value specified. Input was:\n";
		$output .= "	" . var_export($parameters, true);
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	}

	// Field 'du_has_duration'     : OPTIONAL (1), DEFAULT 0
	// 
	// Set du_has_duration to 0 if it is not specified and handle case where it
	// is mal-specified
	if (!isset($p['du_has_duration'])) {
		$p['du_has_duration'] = 0;
	} elseif ($p['du_has_duration'] != 1) { // Input mal-specified
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new du: bad 'du_has_duration' value specified. Input was:\n";
		$output .= "	" . var_export($parameters, true);
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	}

	// Field 'du_time_start'       : OPTIONAL (string "YYYY-MM-DD[ HH:MM:SS]")
	// 
	// Set du_time_start to NULL if it is not specified, add default 00:00:00
	// if time is not specified (as in the case of linking a date), and handle
	// case where input is mal-specified
	if (!isset($p['du_time_start'])) {
		$p['du_time_start'] = NULL;
	} elseif (preg_match('/\d\d\d\d-\d\d-\d\d/', $p['du_time_start'])) {
		// Input format matches date but not time
		if (!preg_match('/\d\d:\d\d:\d\d/', $p['du_time_start'])) {
			// Append default time
			$p['du_time_start'] .= " 00:00:00";
		}
	} else { // Input mal-specified
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new du: 'du_time_start' specified in wrong format. Input was:\n";
		$output .= "	" . var_export($parameters, true);
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	}

	// Field 'du_time_end'         : OPTIONAL (string "YYYY-MM-DD[ HH:MM:SS]")
	// 
	// Set du_time_end to NULL if it is not specified, add default 00:00:00
	// if time is not specified (as in the case of linking a date), and handle
	// case where input is mal-specified
	if (!isset($p['du_time_end'])) {
		$p['du_time_end'] = NULL;
	} elseif (!preg_match('/\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d/', $p['du_time_end'])) { // Input mal-specified
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Could not add new du: 'du_time_end' specified in wrong format. Input was:\n";
			$output .= "	" . var_export($parameters, true);
			// Write to log file and kill process
			fwrite($log, $output, 2048);
		    exit($output);
	}

	// Field 'du_priority'         : OPTIONAL (int 1, 2, 3, 4), DEFAULT 4
	// Field 'du_enforce_priority' : AUTO SET
	// 
	// Set du_priority and du_enforce_priority: if du priority is not
	// specified, set du_priority to 4 and do not enforce (0), otherwise
	// enforce (1), and handle case where input is mal-specified
	if (!isset($p['du_priority'])) {
		$p['du_enforce_priority'] = 0;
		$p['du_priority']         = 4;
	} elseif (preg_match('/[1234]/', $p['du_priority'])) {
		$p['du_enforce_priority'] = 1;
	} else { // Input mal-specified
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new du: bad 'du_priority' value specified. Input was:\n";
		$output .= "	" . var_export($parameters, true);
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	}

	// Field 'tag_priorities'      : OPTIONAL (array of ints)
	// 
	// @todo integrate with tag class
	if (!isset($p['tag_priorities'])) {
		$p['tag_priorities']      = NULL;
	}

	// Field 'du_note'             : OPTIONAL (string note)
	// 
	// Set du_note to NULL if it is not specified and handle case where it is
	// mal-specified
	if (!isset($p['du_note'])) {
		$p['du_note']             = NULL;
	} elseif (!preg_match('/[\w~!@\$%\^&\*\(\)-\+=\{\}\[]\.\?\\/,:;"\']/', $p['du_time_end'])) { // Input mal-specified
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new du: 'du_note' specified in wrong format. Input was:\n";
		$output .= "	" . var_export($parameters, true);
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	}

	// Field 'du_tags'            : OPTIONAL (array of strings)
	// 
	// @todo integrate with tag class
	if (!isset($p['du_tags'])) {
		$p['du_tags']             = NULL;
	}

	// Record successful preprocessing
	$success      = date("Y-m-d H:i:s T", time()) . " ";
	$success     .= "Preprocessed du successfuly. Preprocessed parameters:\n";
	// Unpack preprocessed parameters
	foreach ($p as $f => $v) {
		$success .= "	";
		$success .= var_export($f, true) . " => ";
		$success .= var_export($v, true) . "\n";
	}
	fwrite($log, $success, 4096);

	// Done
	return $p;

}


/**
 * Function deleteDu
 *
 * Deletes the du of a specified du_id at both object- and db-levels.
 * 
 * @param [int]       $id      Id of du to be removed
 * @param [array(du)] $duArray Array of du objects to take new du
 * @global [$log | The open log file]
 * @return [array] $duArray with the specified du element removed
 */
function deleteDu($id, $duArray = NULL) {

	global $log;

	// If duArray is not specified, delete it from array of all du's
	$duArray = ($duArray) ?: $GLOBALS['all'];
	$isAll = ($duArray) ? false : true;

	// If no du exists for specified ID
	if ($duArray[$id] === false) {
	    // Handle error
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not find du in " . (($isAll) ? "\$all" : "duArray");
		$output .= " with du_id of '" . $id . "' to delete.\n";
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	} else {
		// Remove it from DB
		$duArray[$id]->deleteFromDB();
		// Remove it from du array
		unset($duArray[$id]);
		// Record success
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Deleted du from " . (($isAll) ? "\$all" : "duArray");
		$output .= " with du_id of '" . $id . "'.\n";
		fwrite($log, $output, 2048);
	}

	// Done
	return $duArray;

}

// Get du class object definitions
require("du-class.php");



// Main executions
$log = openLogFile(true);
$all = getAll();

// Testing Example

displayAsTable($all);

$parameters = array('du_name' => 'Take out the trash', 'du_has_date' => 1, 'du_time_start' => '2017-03-30');
$all = addDu($parameters);

// $all[1]->unsetDuPriority();
// $all[3]->unsetNote();

displayAsTable($all);

$all = deleteDu(5);

displayAsTable($all);

// $all[1]->setDuPriority("4");
// $all[3]->setNote("Make it extra yummy");

?>