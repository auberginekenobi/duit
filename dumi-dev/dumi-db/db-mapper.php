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
    if(!isset($connection)) {
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
	if($connection === false || $connection->connect_error) {
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

    if($result === false) {
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

	// Query the database for all du's
	$result = query("SELECT * FROM Dus;");

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
    						$currRow['du_note']);
    	// Store du in array at key that is du_id
    	$all[$du_id] = $newDu;   
	}

	// Close result
	$result->close();

	// Done
	return $all;

}


/**
 * UNFINISHED
 *
 * Function getUseful (tempname)
 *
 * Fetches information about du tags and priority (as determined by several
 * elements) with specific SQL query
 *
 * @todo finish this function
 * @return void
 */
function getUseful() {

	$result = query(
		"SELECT
		  CASE WHEN du_priority < tag_priority OR tag_priority IS NULL THEN du_priority ELSE tag_priority END AS Priority,
		  du_name AS Du,
		  du_note AS Note,
		  GROUP_CONCAT(tag_name separator ', ') AS Tag,
		  status_type as Status
		FROM
		  (
		  SELECT
		    d.du_name,
		    d.du_note,
		    t.tag_name,
		    d.du_priority,
		    t.tag_priority,
		    u.status_type
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
		ORDER BY Priority"
		);

}

require("du-class.php");



// main executions
$log = openLogFile();
$all = getAll();

// testing
$table = "";
foreach ($all as $du) {
	$table .= ($du->getID() == 1) ? $du->displayAsTable(TRUE) : $du->displayAsTable(FALSE);
	// echo nl2br($du->getName() . "\n");
}

echo "<table>";
echo $table;
echo "</table>";

$all[1]->setName("Buy pasta");
echo nl2br("-------\n" . $all[1]->getName() . "\n");
$all[1]->setName("Buy groceries");

// var_dump($all[1]->getID());
// var_dump($all[1]->getTimestamp());
// var_dump($all[1]->getName());
// var_dump($all[1]->hasDate());
// var_dump($all[1]->hasDeadline());
// var_dump($all[1]->hasDuration());
// var_dump($all[1]->getTimeStart());
// var_dump($all[1]->getTimeEnd());
// var_dump($all[1]->getNote());

?>