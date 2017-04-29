<?php

/**
 * db-mapper.php
 *
 * Performs central interactions with the database as well as maintains PHP
 * objects/arrays for data and means of accessing them.
 *
 * PHP v5.6
 *
 * @author    Kelli Rockwell <kellirockwell@mail.com>, Patrick Shao <shao.pat@gmail.com>, and Owen Chapman <osc12013@pomona.edu>
 * @copyright 2017 DUiT
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
	
	$logPath = __DIR__ . "/db-history.log";

	// Clear log file, if specified to
	if ($clearContents) file_put_contents($logPath, "");

	// Open log file for write only (existing data preserved, file pointer
	// starts at end of file)
	$log = fopen($logPath, "a");

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
    	$output .= ($function) ? "Function " . $function . " was u" : "U"; 
    	$output .= "nable to perform query \"";
    	$output .= $query . "\". ";
		$output .= mysqli_errno($connection) . ": " . mysqli_error($connection) . "\n";
		// Write to log file and kill process
		fwrite($log, $output, 256);
	    exit($output);
    }

    // Record successful query
    $success  = date("Y-m-d H:i:s T", time()) . " ";
    $success .= ($function) ? "Function " . $function . " p" : "P";
    $success .= "erformed query: " . $query . "\n";
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
           du_status,
           user_id,
	       ( Group_concat(tag_name SEPARATOR ', ') )     AS du_tags
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
                   d.du_status,
                   d.user_id,
	               t.tag_id, 
	               t.tag_name, 
	               t.tag_priority, 
	               t.tag_note
	        FROM   dus AS d 
	               LEFT JOIN du_tag_pairs AS p 
	                      ON d.du_id = p.du_id 
	               LEFT JOIN tags AS t 
	                      ON p.tag_id = t.tag_id ) AS subq
	GROUP  BY du_id 
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
                            $currRow['du_status'],
                            $currRow['user_id'],
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
 * Function getAllTags
 *
 * Fetches all tags from the database and stores them as tag objects in the
 * returned array
 * 
 * @return [array] The array holding all of the tag objects, each of whose keys
 * corresponds to the tag's tag_id
 */
function getAllTags() {

	// Create new array to hold the du's
	$all = array();

	// Query statement for large, formatted table
	$queryStatement = "
	SELECT tag_id,
	       tag_name, 
	       tag_priority, 
	       tag_note, 
           user_id
	FROM   tags
	GROUP  BY tag_name 
	ORDER  BY tag_id ASC";
    
    // Query the database for all du's
	$result = query($queryStatement, "getAllTags()");
    
    // While there is another non-null row of the query result
    while ($currRow = $result->fetch_assoc()) {
    	// Create a new du
    	$newTag = new tag();
    	// Remember the current du's du_id
    	$tag_id = $currRow['tag_id'];
    	// Fill fields of du to match values fetched from the database
    	$newTag->setTagFields($tag_id,
    						$currRow['tag_name'],
    						$currRow['tag_priority'],
    						$currRow['tag_note'],
                            $currRow['user_id']);
    	// Store du in array at key that is du_id
    	$all[$tag_id] = $newTag;   
	}
    
    // Close result
	$result->close();

	// Done
	return $all;
}

// @TODO function associateAll puts all tags in du objects and all dus in tag objects.

/**
 * Function displayAsTable
 *
 * Echoes a simple html table of the inputted database items; primarily used for debugging
 * 
 * @param [array] $duArray Array of object wrappers for database entries slated to display in table. Must implement the function displayAsTableRow($headers);
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


function preprocessTag($parameters) {
    global $log;
    
    $p = $parameters;
    
    // Field 'tag_name'             : REQUIRED (string name)
	// Handle case where tag_name is not specified
	if (!isset($p['tag_name'])) {
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new tag: no name found specified in input. Input was:\n";
		$output .= "	" . var_export($parameters, true);
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	}
    
    // Field 'tag_priority'     OPTIONAL int
    // if not set, default 4
    if (!isset($p['tag_priority'])) {
        $p['tag_priority'] = 4;
    } elseif ($p['tag_priority']!=1 && $p['tag_priority']!=2 &&
              $p['tag_priority']!=3){
        $p['tag_priority']=4;
    }
    
    // Field 'du_note'             : OPTIONAL (string note)
	// 
	// Set du_note to NULL if it is not specified and handle case where it is
	// mal-specified
	if (!isset($p['tag_note'])) {
		$p['tag_note']             = NULL;
	} 
    //TODO: regex
    
    // Field 'user_id'            : REQUIRED (varchar user_id)
  // Check if provided user_id matches an extant user_id
	// Handle case where user_id is not specified
	if (!isset($p['user_id'])) {
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new tag: no user id found specified in input. Input was:\n";
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
        if ($currRow == NULL){
            $output  = date("Y-m-d H:i:s T", time());
            $output .= " Could not add new tag: input user id does not correspond to an extant user. Input was:\n";
            $output .= "	" . var_export($parameters, true);
            // Write to log file and kill process
            fwrite($log, $output, 2048);
            exit($output);
        }
    }
    
    return $p;
}

/** Function addTag
 *
 * Adds a new tag with the specified set of properties at both object and db levels.
 *
 * Example call:
 * $all = addTag(array(
 * 					'tag_name' => 'Job hunt',
 * 					'tag_note' => 'WHYYYYYYYYYYYYY',
 * 				), $alltags);
 *
 * @param [array(tag)]                $tagArray Array of tag objects to take new tag
 * @global [$log | The open log file]
 * @return [array] $tagArray with the new du element added
 */
function addTag($parameters, $tagArray = NULL) {
    
    global $log;
    
    // If tagArray is not specified, add it to all tags
    $isAll = ($tagArray) ? false : true;
    $tagArray = ($tagArray) ?: $GLOBALS['alltags'];
    
    // Record add call
    $addAlert      = date("Y-m-d H:i:s T", time()) . " ";
	$addAlert     .= "Preparing to add new tag with parameters:\n";

	// Unpack parameter array passed
	foreach ($parameters as $p => $v) {
		$addAlert .= "	";
		$addAlert .= var_export($p, true) . " => ";
		$addAlert .= var_export($v, true) . "\n";
	}
	fwrite($log, $addAlert, 4096);
    
    // Create a new tag
    $newTag = new tag();
    
    // Get new ID for the du by adding 1 to the last ID used in the array
	end($tagArray);
	$tag_id = key($tagArray) + 1;
	$parameters['tag_id'] = $tag_id;

    // Preprocess params
    $p = preprocessTag($parameters);
    
    // Fill fields of tag to match parameter inputs
	$newTag->setTagFields($p['tag_id'],
						$p['tag_name'],
						$p['tag_priority'],
						$p['tag_note'],
                        $p['user_id']);
    
    // Store du in array at key that is tag_id
	$tagArray[$tag_id] = $newTag;
    
    if (!array_key_exists($tag_id,$tagArray)) {
	    // Handle error
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new tag to tagArray. Current state of $newTag is\n";
		$output .= "	" . var_export($newTag, true);
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	} else {
		// Record success
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Added new tag to " . (($isAll) ? "\$alltags" : "tagArray");
		$output .= " with tag_id of '" . $tag_id . "'.\n";
		fwrite($log, $output, 2048);
	}
    
    // Push the new du and its properties to the database
	$tagArray[$tag_id]->addToDB();

	// Done
	return $tagArray;

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

function deleteTag($id,$tagArray = NULL) {
    global $log;
    
    // If tagArray is not specified, delete it from array of all tags
	$isAll = ($tagArray) ? false : true;
	$tagArray = ($tagArray) ?: $GLOBALS['alltags'];

    // If no tag exists for specified ID
	if (!array_key_exists($id,$tagArray)) {
	    // Handle error
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not find tag in " . (($isAll) ? "\$alltags" : "tagArray");
		$output .= " with tag_id of '" . $id . "' to delete.\n";
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	} else {
        // Remember current element to delete
		$thistag = $tagArray[$id];
		// Remove it from tag array
		unset($tagArray[$id]);
		// Record success
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Deleted tag from " . (($isAll) ? "\$all" : "tagArray");
		$output .= " with tag_id of '" . $id . "'.\n";
		fwrite($log, $output, 2048);
		// Remove it from DB
		$thistag->deleteFromDB();
	}

	// Done
	return $tagArray;
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
	$isAll = ($duArray) ? false : true;
	$duArray = ($duArray) ?: $GLOBALS['all'];

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
                        $p['du_status'],
                        $p['user_id'],
						$p['du_tags']);

	// Store du in array at key that is du_id
	$duArray[$du_id] = $newDu;

	if (!array_key_exists($du_id,$duArray)) {
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
 * the properties to be used on the new du. Parameters 'du_name' => 'name' and
 * 'user_name' => 'user' are the only mandatory property. All parameters should
 * be specified in the array as 'field_name' => field_value.
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
	} elseif (!preg_match('/^[\w,:&\-\'\(\)\+\" ]+$/' , $p['du_name'])) { // Input mal-specified
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new du: 'du_name' specified in wrong format. Input was:\n";
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


// MAY WANT TO CHECK THAT THE DEADLINE IS IN THE FUTURE, UNLESS
	// WE WANT TO SUPPORT DEADLINES IN THE PAST

	// Pair check                  : du_has_date => du_time_start
	if ($p['du_has_date']) {
		if (!isset($p['du_time_start'])) { // if has_date but no date set
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Could not add new du: 'du_has_date' set to true but no date specified. Input was:\n";
			$output .= "	" . var_export($parameters, true);
			// Write to log file and kill process
			fwrite($log, $output, 2048);
		    exit($output);
		}
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

	// Pair check                  : du_has_deadline => du_time_start
	if ($p['du_has_deadline']) {
		if (!isset($p['du_time_start'])) { // if has_deadline but no date set
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Could not add new du: 'du_has_deadline' set to true but no deadline specified. Input was:\n";
			$output .= "	" . var_export($parameters, true);
			// Write to log file and kill process
			fwrite($log, $output, 2048);
		    exit($output);
		}
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

	// Pair check                  : du_has_duration => du_time_start && du_time_end
	if ($p['du_has_duration']) {
		if (!isset($p['du_time_start']) || !isset($p['du_time_end'])) { // if has_duration but no start/end time set
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Could not add new du: 'du_has_duration' set to true but no start and/or end time specified. Input was:\n";
			$output .= "	" . var_export($parameters, true);
			// Write to log file and kill process
			fwrite($log, $output, 2048);
		    exit($output);
		}
	}
	
	// Pair check                  : du_time_start => du_has_date || deadline || duration
	if (isset($p['du_time_start'])) {
		if (!($p['du_has_date'] || $p['du_has_deadline'] || $p['du_has_duration'])) { // if has start time but no date/deadline/duration
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Could not add new du: found 'du_time_start' but 'du_has_date/deadline/duration' set to false. Input was:\n";
			$output .= "	" . var_export($parameters, true);
			// Write to log file and kill process
			fwrite($log, $output, 2048);
		    exit($output);
		}
	}
	
	// Pair check                  : du_time_end => du_has_duration
	if (isset($p['du_time_end'])) {
		if (!$p['du_has_duration']) { // if has end time but no duration
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Could not add new du: found 'du_time_end' but 'du_has_duration' set to false. Input was:\n";
			$output .= "	" . var_export($parameters, true);
			// Write to log file and kill process
			fwrite($log, $output, 2048);
		    exit($output);
		}
	}
	
	// Pair check                  : du_has_date => !deadline & !duration
	// 							   : du_has_deadline => !date & !duration
	// 							   : du_has_duration => !date & !deadline
	if ( ($p['du_has_date']     && ($p['du_has_deadline'] || $p['du_has_duration'])) ||
		 ($p['du_has_deadline'] && ($p['du_has_date']     || $p['du_has_duration'])) ||
		 ($p['du_has_duration'] && ($p['du_has_date']     || $p['du_has_deadline'])) ) { // if any combination of two
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new du: more than one of 'du_has_date', 'du_has_duration', 'du_has_deadline' set to true. Input was:\n";
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
	} 
	// Apparently PHP needs FOUR BACKSLASHES to accept a single literal backslash 
	// in the case that there is another escape character after it, 3 otherwise...?!?
	elseif (!preg_match('/^[\w,:;~!@%&=\-\'\$\^\*\(\)\+\[\]\{\}\.\?\\\\\/\" ]+$/' , $p['du_note'])) { // Input mal-specified
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new du: 'du_note' specified in wrong format. Input was:\n";
		$output .= "	" . var_export($parameters, true);
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	}
    
  	// Field 'du_status'           : OPTIONAL (string status), DEFAULT 'Open'
	// 
	// Set du_status to Open if it is not specified and handle case where it is
	// mal-specified
	if (!isset($p['du_status'])) {
		$p['du_status']             = 'Open';
	} elseif ($p['du_status']!='Open' && $p['du_status']!='Completed' && $p['du_status']!='Active') { // Input mal-specified
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new du: 'du_status' was an invalid status. Input was:\n";
		$output .= "	" . var_export($parameters, true);
        $output .= ". Input should be 'Open', 'Completed', or 'Active'";
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	}
    
	// FIELD 'user_id'             : REQUIRED (varchar user_id)
    // Check if provided user_id matches an extant user_id

	// Handle case where user_id is not specified
	if (!isset($p['user_id'])) {
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not add new du: no user id found specified in input. Input was:\n";
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
        if ($currRow == NULL){
            $output  = date("Y-m-d H:i:s T", time());
            $output .= " Could not add new du: input user id does not correspond to an extant user. Input was:\n";
            $output .= "	" . var_export($parameters, true);
            // Write to log file and kill process
            fwrite($log, $output, 2048);
            exit($output);
        }
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
 * @param [array(du)] $duArray Array of du objects to delete du from
 * @global [$log | The open log file]
 * @return [array] $duArray with the specified du element removed
 */
function deleteDu($id, $duArray = NULL) {

	global $log;

	// If duArray is not specified, delete it from array of all du's
	$isAll = ($duArray) ? false : true;
	$duArray = ($duArray) ?: $GLOBALS['all'];

	// If no du exists for specified ID
	if (!array_key_exists($id,$duArray)) {
	    // Handle error
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Could not find du in " . (($isAll) ? "\$all" : "duArray");
		$output .= " with du_id of '" . $id . "' to delete.\n";
		// Write to log file and kill process
		fwrite($log, $output, 2048);
	    exit($output);
	} else {
		// Remember current element to delete
		$thisDu = $duArray[$id];
		// Remove it from du array
		unset($duArray[$id]);
		// Record success
		$output  = date("Y-m-d H:i:s T", time());
		$output .= " Deleted du from " . (($isAll) ? "\$all" : "duArray");
		$output .= " with du_id of '" . $id . "'.\n";
		fwrite($log, $output, 2048);
		// Remove it from DB
		$thisDu->deleteFromDB();
	}

	// Done
	return $duArray;
}

// Wrapper functions

function getDuByID($id){
	$all = $GLOBALS['all'];
	return $all[$id];
}

function getTagByID($id){
	$alltags = $GLOBALS['alltags'];
	return $alltags[$id];
}

function getUserByID($id){
	$allusers = $GLOBALS['allusers'];
	return $allusers[$id];
}

// Get du class object definitions
require_once("du-class.php");
require_once("tag-class.php");
require_once("db-mapper-user.php");

//Get user class object definitions
require_once("user-class.php");


// Main executions
$log = openLogFile(true);
$all = getAll();
$alltags = getAllTags();
$allusers = getAllUsers();

// Testing Example

displayAsTable($all);
//$parameters = array('user_name' => 'Winky'.rand(), 'user_id' => 'herbivore');
//$allusers = addUser($parameters);
//displayAsTable($allusers);
//$allusers = deleteUser('herbivore');
//displayAsTable($allusers);


//$parameters = array('tag_name' => 'binge drinking'.rand(), 'user_id' => 1);
//$alltags = addTag($parameters);
// displayAsTable($alltags);
//$alltags = deleteTag(500);
//displayAsTable($alltags);

//bad tags
//$parameters = array('tag_name' => 'sinking slowly into a morass'.rand(), 'user_id' => 5000);
//$alltags = addTag($parameters);
//$parameters = array('tag_priority' => 1, 'user_id' => 1);
//$alltags = addTag($parameters);
//displayAsTable($alltags);

//$testertag = new tag();
//var_dump($testertag);
//$testertag->setTagFields(1,"asf",4," a anote",1);
//var_dump($testertag);


//$parameters = array('du_name' => 'Take out the trash'.rand(), 'du_has_date' => 1, 'du_time_start' => '2017-03-30', 'user_id' => 1);
//$all = addDu($parameters);

// $all[1]->unsetDuPriority();
// $all[3]->unsetNote();

//displayAsTable($all);

//$all = deleteDu(5);
//
//displayAsTable($all);

// $all[1]->setDuPriority("4");
// $all[3]->setNote("Make it extra yummy");

?>