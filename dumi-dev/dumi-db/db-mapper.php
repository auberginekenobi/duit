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
 * @return [resource(file stream)] The open log file
 */
function openLogFile() {

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
 * @param  [array] $duArray Array of du objects slated to display in table
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


function addDu($parameters, $duArray) {

	// Create a new du
	$newDu = new du();

	// Get new ID for the du by adding 1 to the last ID used in the array
	end($duArray);
	$du_id = key($duArray) + 1;

	// Preprocess $parameters array
	$p = $parameters;
	$p['du_id']        = $du_id;
	$p['du_timestamp'] = date("Y-m-d H:i:s", time());
	if (!isset($p['du_name'])) {
	    // Name is only required field -- handle erroneous input
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new du: no name found specified in input. Input was:\n";
		$output .= var_export($parameters, true);
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	}
	if (!isset($p['du_has_date'])) {
		$p['du_has_date']         = 0;
	}
	if (!isset($p['du_has_deadline'])) {
		$p['du_has_deadline']     = 0;
	}
	if (!isset($p['du_has_duration'])) {
		$p['du_has_duration']     = 0;
	}
	if (!isset($p['du_time_start'])) {
		$p['du_time_start']       = NULL;
	}
	if (!isset($p['du_time_end'])) {
		$p['du_time_end']         = NULL;
	}
	if (!isset($p['du_priority'])) {
		$p['du_enforce_priority'] = 0;
		$p['du_priority']         = 4;
	} else {
		$p['du_enforce_priority'] = 1;
	}
	if (!isset($p['tag_priorities'])) {
		$p['tag_priorities']      = NULL;
	}
	if (!isset($p['du_note'])) {
		$p['du_note']             = NULL;
	}
	if (!isset($p['du_tags'])) {
		$p['du_tags']             = NULL;
	}

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
	// Push the new du and its properties to the database
	$duArray[$du_id]->addToDB();

	if ($duArray[$du_id] === false) {
	    // Handle error
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new du to duArray. Current state of $newDu is\n";
		$output .= var_export($newDu, true);
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	}

	// Done
	return $duArray;

}


function deleteDu($id, $duArray) {

	$duArray[$id]->deleteFromDB();
	unset($duArray[$id]);

	// Done
	return $duArray;

}


require("du-class.php");



// Main executions
$log = openLogFile();
$all = getAll();

// Testing Example

displayAsTable($all);

//$all = addDu(array('du_name' => 'Take out the trash', 'du_has_date' => 1, 'du_time_start' => '2017-03-25 00:00:00'), $all);

// $all[1]->unsetDuPriority();
// $all[3]->unsetNote();

displayAsTable($all);

$all = deleteDu(7, $all);

displayAsTable($all);

// $all[1]->setDuPriority("4");
// $all[3]->setNote("Make it extra yummy");

?>