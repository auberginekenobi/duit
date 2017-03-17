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
 * @param  [string] $query The SQL query statement as a string
 * @global [$log | The open log file]
 * @return [object(mysqli_result)] The result of the query       
 */
function query($query) {

	global $log;

    // Connect to the database
    $connection = connect();

    // Query the database
    $result = $connection->query($query);

    if ($result === false) {
    	// Handle error
    	$output  = date("Y-m-d H:i:s T", time());
    	$output .= " Unable to perform query \"";
    	$output .= $query . "\". ";
		$output .= mysqli_errno($connection) . ": " . mysqli_error($connection) . "\n";
		// Write to log file and kill process
		fwrite($log, $output, 256);
	    exit($output);
    }

    // Record successful query
    $success  = date("Y-m-d H:i:s T", time());
    $success .= " Performed query \"" . $query . "\".\n";
    fwrite($log, $success, 256);

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
	$queryStatement = "SELECT
		  du_id,
          du_timestamp,
          du_name,
          du_has_date,
          du_has_deadline,
          du_has_duration,
          du_time_start,
          du_time_end,
          (CASE WHEN du_priority < tag_priority OR tag_priority IS NULL THEN du_priority ELSE tag_priority END) AS calc_priority,
		  du_note,
		  (GROUP_CONCAT(tag_name separator ', ')) AS du_tags,
		  status_type,
          status_time_start,
          status_time_end,
          score
		FROM
		  (
		  SELECT
		    d.du_id,
            d.du_timestamp,
            d.du_name,
            d.du_has_date,
            d.du_has_deadline,
            d.du_has_duration,
            d.du_time_start,
            d.du_time_end,
            d.du_priority,
		    d.du_note,
            t.tag_id,
		    t.tag_name,
		    t.tag_priority,
            t.tag_note,
		    u.status_type,
            u.status_time_start,
            u.status_time_end,
            u.score
		  FROM Dus as d
		  LEFT JOIN
		    Du_Tag_Pairs AS p
		      ON d.du_id = p.du_id
		  LEFT JOIN
		    Tags AS t
		      ON p.tag_id = t.tag_id
		  LEFT JOIN
		    Statuses AS u
		      ON d.du_id = u.du_id
		  ) AS subq
		GROUP BY du_name
		ORDER BY du_id ASC";

	// Query the database for all du's
	$result = query($queryStatement);

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
    						$currRow['calc_priority'],
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

require("du-class.php");



// Main executions
$log = openLogFile();
$all = getAll();

// Testing

$all[1]->setDuration("2016-03-17 13:30:00", "2016-03-17 14:30:00");

displayAsTable($all);

$all[1]->unsetDuration();

displayAsTable($all);

?>