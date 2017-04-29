<?php

/**
 * Class du
 *
 * Holds properties about each du in database as individual PHP object
 * with methods defined for accessing and modifying the properties
 *  PHP v5.6
 *
 * @author    Kelli Rockwell <kellirockwell@mail.com>, Patrick Shao <shao.pat@gmail.com>, and Owen Chapman <osc12013@pomona.edu>
 * @copyright 2017 DUiT
 * @since     File available since release 0.0.2  
 */

require_once("tag-class.php");
class du {

	// Protected properties of du
	protected $du_id;		        // [string]          The du's du_id
	protected $du_timestamp;        // [string]          The timestamp recorded for the du
	protected $du_name;             // [string]          The name recorded for the du
	protected $du_has_date;         // [boolean]         If the du is linked to a date
	protected $du_has_deadline;     // [boolean]         If the du is linked to a deadline
	protected $du_has_duration;     // [boolean]         If the du is linked to a start and end time
	protected $du_time_start;       // [string]          Deadline or start time of the du, if it has one
	protected $du_time_end;         // [string]          End time of the du, if it has one
	protected $du_priority;         // [int]             Priority recorded for the du
	protected $du_enforce_priority; // [boolean]         If the du has been set to a priority
	protected $tag_priorities;      // [array(int)]      Priorities specifications for each tag recorded for the du
	protected $calc_priority;       // [int]             Priority calculated for the du; see setting of $calc_priority for more detailed information
	protected $du_note;             // [string]          The note recorded for the du
	protected $du_tags;             // [array(tag)]   	 Tags recorded for the du
	protected $du_status;			// [string]					 The status of a given Du. (Open/Active/Completed)
  protected $user_id;             // [string]            User associated with the du


	/**
	 * Main constructor
	 */
	function __construct() {
	}


	/**
	 * Function setDuFields
	 *
	 * Accomplishes main constructional tasks of creating a du by assigning
	 * values to all du properties at once
	 * 
	 * @param [string] $du_id                 The du's du_id
	 * @param [string] $du_timestamp          The timestamp recorded for the du
	 * @param [string] $du_name               The name recorded for the du
	 * @param [string] $du_has_date           "0" if the du is not linked to a date, "1" if the du is
	 * @param [string] $du_has_deadline       "0" if the du is not linked to a deadline, "1" if the du is
	 * @param [string] $du_has_duration       "0" if the du is not linked to a start and end time, "1" if the du is
	 * @param [string] $du_time_start         Deadline or start time of the du, if it has one
	 * @param [string] $du_time_end           End time of the du, if it has one
	 * @param [string] $du_priority           Priority recorded for the du
	 * @param [string] $du_enforce_priority   "0" if the du is not given a priority, "1" if the du is
	 * @param [string] $tag_priorities        Priority specifications for each tag recorded for the du as a string, entries separated by ", ""
	 * @param [string] $du_note               The note recorded for the du
     * @param [string] $du_status			  The status of a given Du. (Open/Active/Completed)
     * @param [int] $user_id                  User associated with du
	 * @param [tag] $du_tags                  Tags recorded for the du, each as an object in an array
	 */
	public function setDuFields($du_id,
								$du_timestamp,
								$du_name,
								$du_has_date,
								$du_has_deadline,
								$du_has_duration,
								$du_time_start,
								$du_time_end,
								$du_priority,
								$du_enforce_priority,
								$tag_priorities,
								$du_note,
                                $du_status,
                                $user_id
								) {

		try {
			// Instantiate parameters as object's properties
			$this->du_id               = $du_id;
			$this->du_timestamp        = $du_timestamp;
			$this->du_name             = $du_name;
			$this->du_has_date         = ($du_has_date == "0")         ? FALSE :
																         TRUE; // Convert to corresponding boolean
			$this->du_has_deadline     = ($du_has_deadline == "0")     ? FALSE : 
																	     TRUE; // Convert to corresponding boolean
			$this->du_has_duration     = ($du_has_duration == "0")     ? FALSE : 
																	     TRUE; // Convert to corresponding boolean
			$this->du_time_start       = $du_time_start;
			$this->du_time_end         = $du_time_end;
			$this->du_priority         = intval($du_priority); // Convert to int
			$this->du_enforce_priority = ($du_enforce_priority == "0") ? FALSE :
			                                                             TRUE; // Convert to corresponding boolean
			$this->tag_priorities      = ($tag_priorities)             ? array_map('intval', explode(", ", $tag_priorities)) : 
															             NULL; // Explode and convert to ints, if tag priorities exist
			$this->du_note             = $du_note;
            $this->du_status 		   = $du_status;
            $this->user_id             = $user_id;
//			$this->du_tags             = ($du_tags)                    ? explode(", ", $du_tags) :
//			                                                             NULL; // Explode, if tags exist


			// Calculation of overall du priority follows the flow below:
			// If du_enforce_priority is TRUE  --> calc_priority = du_priority
			// If du_enforce_priority is FALSE --> calc_priority = min(tag_priorities) or 4 if tag_priorities is NULL
			$this->calc_priority       = ($this->du_enforce_priority)  ? $this->du_priority :
			                                                             (($this->tag_priorities) ? min($this->tag_priorities) : 
			                                                             	                        4);

		} catch (Exception $e) {
			// Handle exception
			$output  = "Tried to setDuFields, caught exception: ";
			$output .= $e->getMessage();
			$output .= "\n";
			// Write to log file and echo error message
			fwrite($log, $output, 256);
			echo $output;
		}

	}


	/**
	 * Function displayAsTableRow
	 *
	 * Outputs current du as a row of a table; primarily used for debugging
	 * 
	 * @param  [boolean] $headers Specify whether or not to include a row of headers directly before this du
	 * @return [string] Table row of du properties, as a string
	 */
	public function displayAsTableRow($headers) {
		// Set up headers
		$addHeaders = "<tr>";
		$addHeaders .= "<th>Delete</th>";
		$addHeaders .= "<th>du_id</th>";
		$addHeaders .= "<th>du_timestamp</th>";
		$addHeaders .= "<th>du_name</th>";
		$addHeaders .= "<th>du_has_date</th>";
		$addHeaders .= "<th>du_has_deadline</th>";
		$addHeaders .= "<th>du_has_durataion</th>";
		$addHeaders .= "<th>du_time_start</th>";
		$addHeaders .= "<th>du_time_end</th>";		
		$addHeaders .= "<th>du_priority</th>";
		$addHeaders .= "<th>du_enforce_priority</th>";
		$addHeaders .= "<th>tag_priorities</th>";
		$addHeaders .= "<th>calc_priority</th>";
		$addHeaders .= "<th>du_note</th>";
        $addHeaders .= "<th>du_status</th>";
        $addHeaders .= "<th>user_id</th>";
//		$addHeaders .= "<th>du_tags</th>";
		$addHeaders .= "</tr>";

		// If request for du headers
		$output   = ($headers) ? $addHeaders : "";
		// Convert booleans to strings
		$date     = ($this->du_has_date)         ? "TRUE" : "FALSE";
		$deadline = ($this->du_has_deadline)     ? "TRUE" : "FALSE";
		$duration = ($this->du_has_duration)     ? "TRUE" : "FALSE";
		$priority = ($this->du_enforce_priority) ? "TRUE" : "FALSE";
		// Add each cell
		$output .= "<tr>";
		$output .= "<td><button class='deleteDu du-" . $this->du_id . "'> x </button></td>";
		$output .= "<td>" . $this->du_id . "</td>";
		$output .= "<td>" . $this->du_timestamp . "</td>";
		$output .= "<td>" . $this->du_name . "</td>";
		$output .= "<td>" . $date . "</td>";
		$output .= "<td>" . $deadline . "</td>";
		$output .= "<td>" . $duration . "</td>";
		$output .= "<td>" . $this->du_time_start . "</td>";
		$output .= "<td>" . $this->du_time_end . "</td>";
		$output .= "<td>" . $this->du_priority . "</td>";		
		$output .= "<td>" . $priority . "</td>";
		$output .= "<td>" . (($this->tag_priorities) ? implode(", ", $this->tag_priorities) : 
			                                           "" ) . "</td>";
		$output .= "<td>" . $this->calc_priority . "</td>";
		$output .= "<td>" . $this->du_note . "</td>";
    $output .= "<td>" . $this->du_status . "</td>";
    $output .= "<td>" . $this->user_id . "</td>";
//		$output .= "<td>" . (($this->du_tags)        ? implode(", ", $this->du_tags) :
//			                                           "") . "</td>";
		$output .= "</tr>";

		// Done
		return $output;
	}

/**
 * Function addToDB
 *
 * Adds du to the database if there is not already an entry at its du_id. NOTE:
 * This should only be used to add NEW du's to the database, never to update
 * existing du's.
 *
 * @global [$log | The open log file]
 * @return void
 */
	public function addToDB() {

		global $log;

		// Check if there is a du already with this new du's du_id
		$checkQuery = "
			SELECT *
			FROM   dus
			WHERE  du_id = " . $this->du_id
		;
		if (query($checkQuery, "addToDB()")->fetch_array()) { // If a du already exists
			// Handle failure
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Could not add new du to database: item with du_id '";
			$output .= $this->du_id . "' already exists.\n";
			// Write to log file and kill process
			fwrite($log, $output, 2048);
		    exit($output);
		} else { // If no such du exists
			// Get current max du_id from dus table 
			$resetValQuery  = "
				SELECT MAX(du_id)
				FROM   dus"
			;
			$resetValResult = query($resetValQuery, "addToDB()");
			// Get actual value of max du_id query result
			$resetVal       = $resetValResult->fetch_array()[0];
			// Reset dus auto-incrementer in case of deletions to ensure proper
			// du_id is recorded 
			$resetQuery     = "
				ALTER TABLE dus auto_increment = " . ($resetVal + 1)
			;
			query($resetQuery, "addToDB()");

			// Insert where
			$insertQuery  = "
				INSERT INTO dus
							(du_name";
			$insertQuery .= ($this->du_has_date) ? ",
							 du_has_date" : "";
			$insertQuery .= ($this->du_has_deadline) ? ",
							 du_has_deadline" : "";
			$insertQuery .= ($this->du_has_duration) ? ",
							 du_has_duration" : "";
			$insertQuery .= ($this->du_time_start) ? ",
							 du_time_start" : "";
			$insertQuery .= ($this->du_time_end) ? ",
							 du_time_end" : "";
			$insertQuery .= ($this->du_enforce_priority) ? ",
							 du_priority,
							 du_enforce_priority" : "";
			$insertQuery .= ($this->du_note) ? ",
							 du_note" : "";
			$insertQuery .= ", du_status";
            $insertQuery .= ", user_id";
			$insertQuery .= ")";

			// Insert what
			// Name accepts single and double quotes; make sure to escape them
			$insertQuery .= "
				VALUES ('" . $this->escapeQuotes($this->du_name) . "'";
			$insertQuery .= ($this->du_has_date) ? ",
							 1" : "";
			$insertQuery .= ($this->du_has_deadline) ? ",
							 1" : "";
			$insertQuery .= ($this->du_has_duration) ? ",
							 1" : "";
			$insertQuery .= ($this->du_time_start) ? ",
							 '" . $this->du_time_start . "'" : "";
			$insertQuery .= ($this->du_time_end) ? ",
							 '" . $this->du_time_end . "'" : "";
			$insertQuery .= ($this->du_enforce_priority) ? ",
							 " . $this->du_priority . ",
							 1" : "";
			// Note accepts single and double quotes; make sure to escape them
			$insertQuery .= ($this->du_note) ? ",
							 '" . $this->escapeQuotes($this->du_note) . "'" : "";
			$insertQuery .= ",'" . $this->du_status . "'";
            $insertQuery .= ",'" . $this->user_id . "'";
			$insertQuery .= ");";

			query($insertQuery, "addToDB()");

			// Record success
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Added new du to database with du_id of '";
			$output .= $this->du_id . "'.\n";
			fwrite($log, $output, 2048);

			// Get ID created for du in database
			$getIDResult = query("SELECT LAST_INSERT_ID()", "addToDB()");
			$getID       = $getIDResult->fetch_array()[0];

			// If du_id does not match ID created in database
			if ($this->du_id != $getID) {
				// Alert bad add
				$output  = date("Y-m-d H:i:s T", time());
				$output .= " Alert: du_id in array and du_id in database do not match. Changing du_id in array to '" . $getID . "'.\n";
				fwrite($log, $output, 2048);

				// Force du_id to match database
				$this->du_id = $getID;
			} else {
				// Record ID match
				$output  = date("Y-m-d H:i:s T", time());
				$output .= " Confirmed du_id in array (" . $this->du_id . ") matches du_id in database (" . $getID . ").\n";
				fwrite($log, $output, 2048);
			}

		}
		
	}

	/**
	 * Function escapeQuotes
	 *
	 * Looks at a string and inserts an escape character (a single "\") before
	 * any single or double quote found so that it may be properly inserted
	 * into the DB without issue; currently only performed on name and note
	 *
	 * @param [string] $s The string to be escaped, typically $this->du_note or $this->du_name
	 * @global [$log | The open log file]
	 * @return [string] $s plus escape characters around single and double quotes
	 */
	public function escapeQuotes($s) {

		global $log;

		$escaped = "";
		$sq = $dq = FALSE;

		// For each character of the string
		for ($i = 0; $i < strlen($s); $i++) {
			if ($s[$i] == "'") { // If it is a single quote
				$escaped .= "\'";
				// Remember something has been escaped, for logging
				$sq = TRUE;
			} elseif ($s[$i] == '"') { // If it is a double quote
				$escaped .= "\"";
				// Remember something has been escaped, for logging
				$dq = TRUE;
			} else { // Otherwise just copy the character verbatim
				$escaped .= $s[$i];
			}
		}

		// Log if any escaping has taken place.
		if ($sq or $dq) {
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Notice: One or more instances of ";
			if ($sq and !$dq) { // Just single quotes
				$output .= "single quote(s) ";
			} elseif ($dq and !$sq) { // Just double quotes
				$output .= "double quote(s) ";
			} else { // Both
				$output .= "both single and double quote(s) ";
			}
			$output .= "were found in ";
			// Currently only checks if input was name or note
			$output .= ($this->du_name == $s) ? "du_name" : "du_note";
			$output .= ". Characters were escaped before adding to the database. View data for du_id '" . $this->du_id . "' to verify fields were interpreted correctly. \n" ;
			fwrite($log, $output, 2048);
		}

		// Done
		return $escaped;
	}


	/**
	 * Function deleteFromDB
	 *
	 * Removes du from the database if it finds one at its du_id.
	 *
	 * @global [$log | The open log file]
	 * @return void
	 */
	public function deleteFromDB() {

		global $log;

		// Check if there is a du with this new du's du_id to delete
		$checkQuery = "
			SELECT *
			FROM   dus
			WHERE  du_id = " . $this->du_id
		;
		if (query($checkQuery, "deleteFromDB()")->fetch_array()) { // If there is a du
			// dissociate all tags from the du to be deleted
			foreach ($du_tags as $tag) {
				dissociateTag($tag);
			}
			
			$deleteQuery = "
				DELETE FROM dus
				WHERE du_id   = '" . $this->du_id . "'"
				;
			// Delete it
			query($deleteQuery, "deleteFromDB()");

			// Record success
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Deleted du from database with du_id of '";
			$output .= $this->du_id . "'.\n";
			fwrite($log, $output, 2048);
		} else { // If no du is found
			// Handle failure
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Could not delete du from database: item with du_id '";
			$output .= $this->du_id . "' does not exist.\n";
			// Write to log file and kill process
			fwrite($log, $output, 2048);
		    exit($output);
		}

	}


	/**
	 * Function getID
	 * Du ID's should never be set or unset
	 * @return [string] The du's du_id
	 */
	public function getID() {
		return $this->du_id;
	}


	/**
	 * Function getTimestamp
	 * Du Timestamps set automatically and should never be unset
	 * @return [string] The timestamp recorded for the du
	 */
	public function getTimestamp() {
		return $this->du_timestamp;
	}


	/**
	 * Function getName
	 * @return [string] The name recorded for the du
	 */
	public function getName() {
		return $this->du_name;
	}

	/**
	 * Function setName
	 *
	 * Sets or updates the name of a du at both object- and db-levels and logs
	 * any changes. Du names should never be unset.
	 * 
	 * @param [string] $du_name The new name to give the du
	 * @global [$log | The open log file]
	 * @return void
	 */
	public function setName($du_name) {
		global $log;
		// Remember old name, if there was one originally
		$oldname = ($this->du_name) ? $this->du_name : NULL;
		$this->du_name = $du_name;
		$updateQuery = "
			UPDATE dus 
			SET    du_name = '" . $du_name . "' 
			WHERE  du_id   = '" . $this->du_id . "'"
			;
		if (query($updateQuery, "setName()") === TRUE) {
			// Record successful record update
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Updated record for du_id ";
			$output .= $this->du_id . " successfully: ";
			$output .= ($oldname) ? "changed du_name from '" . $oldname . "' to '" . $du_name . "'.\n" :
			                        "set name to '" . $du_name . "'.\n";
			fwrite($log, $output, 256);
		}
	}


	/**
	 * Function hasDate
	 * @return [boolean] If the du is linked to a date
	 */
	public function hasDate() {
		return $this->du_has_date;
	}


	/**
	 * Function getDate
	 * @return [string] The date linked to a du, formatted as "YYYY-MM-DD"
	 */
	public function getDate() {
		return ($this->du_has_date) ? substr($this->du_time_start, 0, 10) : NULL;
	}


	/**
	 * Function setDate
	 *
	 * Sets or updates the date linked to a du at both object- and db-levels and
	 * logs any changes.
	 * 
	 * @param [string] $date The new date to link to a du, formatted as "YYYY-MM-DD"
	 * @global [$log | The open log file]
	 * @return void
	 */
	public function setDate($date) {

		global $log;
		// Remember old date, if there was one originally
		$olddate = ($this->du_has_date) ? substr($this->du_time_start, 0, 10) : NULL;
		$justdate = $date;

		// Unset other date-related properties, if they were set
		$this->unsetDeadline();
		$this->unsetDuration();

		// Mark that this du has a date, if it didn't have one already
		$this->du_has_date = TRUE;
		// Append meaningless time to inputted date
		$date .= " 00:00:00";
		// Store date in time_start field
		$this->du_time_start = $date;
		$updateQuery = "
			UPDATE dus 
			SET    du_time_start = '" . $date . "', 
			       du_has_date   = '1' 
			WHERE  du_id         = '" . $this->du_id . "'"
			;
		if (query($updateQuery, "setDate()") === TRUE) {
			// Record successful record update
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Updated record for du_id ";
			$output .= $this->du_id . " successfully: ";
			$output .= ($olddate) ? "changed date from '" . $olddate . "' to '" . $justdate . "'.\n" :
									"linked date '" . $justdate . "'.\n";
			fwrite($log, $output, 256);
		}

	}

	/**
	 * Function unsetDate
	 *
	 * Unsets the date linked to a du at both object- and db-levels, if one was
	 * originally present, and logs any changes. Function sets du property
	 * du_time_start to NULL as opposed to using unset(du_time_start) in order
	 * to maintain du_time_start as a valid variable.
	 * 
	 * @global [$log | The open log file]
	 * @return void
	 */
	public function unsetDate() {
		global $log;
		// If du had a date previously
		if ($this->du_has_date && $this->du_time_start) {
			$olddate = substr($this->du_time_start, 0, 10);
			// Mark that this du no longer has a date
			$this->du_has_date = FALSE;
			// Unset date stored in time_start field
			$this->du_time_start = NULL;
			$updateQuery = "
				UPDATE dus 
				SET    du_time_start = NULL, 
				       du_has_date   = '0' 
				WHERE  du_id         = '" . $this->du_id . "'"
				;
			if (query($updateQuery, "unsetDate()") === TRUE) {
				// Record successful record update
				$output  = date("Y-m-d H:i:s T", time());
				$output .= " Updated record for du_id ";
				$output .= $this->du_id;
				$output .= " successfully: unlinked date '";
				$output .= $olddate . "'. \n";
				fwrite($log, $output, 256);
			}
		} else {
			// No date to unset
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " No date to unset for du_id ";
			$output .= $this->du_id . ".\n";
			fwrite($log, $output, 256);
		}
	}


	/**
	 * Function hasDeadline
	 * @return [boolean] If the du is linked to a deadline
	 */
	public function hasDeadline() {
		return $this->du_has_deadline;
	}


	/**
	 * Function getDeadline
	 * @return [string] The deadline linked to a du, formatted as "YYYY-MM-DD
	 * HH:MM:SS"
	 */
	public function getDeadline() {
		return ($this->du_has_deadline) ? $this->du_time_start : NULL;
	}


	/**
	 * Function setDeadline
	 *
	 * Sets or updates the deadline linked to a du at both object- and db-levels
	 * and logs any changes.
	 * 
	 * @param [string] $deadline The new deadline to link to a du, formatted as
	 * "YYYY-MM-DD HH:MM:SS"
	 * @global [$log | The open log file]
	 * @return void
	 */
	public function setDeadline($deadline) {

		global $log;
		// Remember old deadline, if there was one originally
		$olddeadline = ($this->du_has_deadline) ? $this->du_time_start : NULL;

		// Unset other date-related properties, if they were set
		$this->unsetDate();
		$this->unsetDuration();

		// Mark that this du has a deadline, if it didn't have one already
		$this->du_has_deadline = TRUE;
		// Store deadline in time_start field
		$this->du_time_start = $deadline;
		$updateQuery = "
			UPDATE dus 
			SET    du_time_start   = '" . $deadline . "', 
			       du_has_deadline = '1' 
			WHERE  du_id           = '" . $this->du_id . "'"
			;
		if (query($updateQuery, "setDeadline()") === TRUE) {
			// Record successful record update
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Updated record for du_id ";
			$output .= $this->du_id . " successfully: ";
			$output .= ($olddate) ? "changed deadline from '" . $olddeadline . "' to '" . $deadline . "'.\n" :
								    "linked deadline '" . $deadline . "'.\n";
			fwrite($log, $output, 256);
		}

	}

	/**
	 * Function unsetDeadline
	 *
	 * Unsets the deadline linked to a du at both object- and db-levels, if one
	 * was originally present, and logs any changes.
	 * 
	 * @global [$log | The open log file]
	 * @return void
	 */
	public function unsetDeadline() {
		global $log;
		// If du had a deadline previously
		if ($this->du_has_deadline && $this->du_time_start) {
			$olddeadline = $this->du_time_start;
			// Mark that this du no longer has a date
			$this->du_has_deadline = FALSE;
			// Unset deadline stored in time_start field
			$this->du_time_start = NULL;
			$updateQuery = "
				UPDATE dus 
				SET    du_time_start   = NULL, 
				       du_has_deadline = '0' 
				WHERE  du_id           = '" . $this->du_id . "'"
				;
			if (query($updateQuery, "unsetDeaadline()") === TRUE) {
				// Record successful record update
				$output  = date("Y-m-d H:i:s T", time());
				$output .= " Updated record for du_id ";
				$output .= $this->du_id;
				$output .= " successfully: unlinked deadline '";
				$output .= $olddeadline . "'. \n";
				fwrite($log, $output, 256);
			}
		} else {
			// No deadline to unset
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " No deadline to unset for du_id ";
			$output .= $this->du_id . ".\n";
			fwrite($log, $output, 256);
		}
	}


	/**
	 * Function hasDuration
	 * @return [boolean] If the du is linked to a start and end time
	 */
	public function hasDuration() {
		return $this->du_has_duration;
	}


	/**
	 * Function getDuration
	 * @return [array(string)] The duration linked to a du as a pair whose first
	 * element is the start time and whose second element is the end time, each
	 * formatted as "YYYY-MM-DD HH:MM:SS"
	 */
	public function getDuration() {
		return ($this->du_has_duration) ? array($this->du_time_start, $this->du_time_end) : NULL;
	}


	/**
	 * Function setDuration
	 *
	 * Sets or updates the duration (start and end time) linked to a du at both
	 * object- and db-levels and logs any changes.
	 * 
	 * @param [string] $start The new start time to link to a du, formatted as
	 * "YYYY-MM-DD HH:MM:SS"
	 * @param [string] $end   The new end time to link to a du, formatted as
	 * "YYYY-MM-DD HH:MM:SS"
	 * @global [$log | The open log file]
	 * @return void
	 */
	public function setDuration($start, $end) {

		global $log;
		// Remember old start and end times, if there were ones originally
		$oldstart = ($this->du_has_date) ? $this->du_time_start : NULL;
		$oldend   = ($this->du_has_date) ? $this->du_time_end   : NULL;

		// Unset other date-related properties, if they were set
		$this->unsetDate();
		$this->unsetDeadline();

		// Mark that this du has a duration, if it didn't have one already
		$this->du_has_duration = TRUE;
		// Store start and end times
		$this->du_time_start = $start;
		$this->du_time_end = $end;
		$updateQuery = "
			UPDATE dus
			SET    du_time_start   = '" . $start . "',
				   du_time_end     = '" . $end . "',
				   du_has_duration = '1'
			WHERE  du_id           = '" . $this->du_id . "'"
			;
		if (query($updateQuery, "setDuration()") === TRUE) {
			// Record successful record update
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Updated record for du_id ";
			$output .= $this->du_id . " successfully: ";
			$output .= ($oldstart) ? "changed duration from '" . $oldstart . " - " . $oldend . "' to '"
															   . $start    . " - " . $end    . "'.\n" :
								     "linked duration '" . $start . " - " . $end . "'.\n";
			fwrite($log, $output, 256);
		}

	}

	/**
	 * Function unsetDuration
	 *
	 * Unsets the duration (start and end time) linked to a du at both object-
	 * and db-levels, if one was originally present, and logs any changes.
	 * 
	 * @global [$log | The open log file]
	 * @return void
	 */
	public function unsetDuration() {
		global $log;
		// If du had a duration previously
		if ($this->du_has_duration && $this->du_time_start && $this->du_time_end) {
			$oldstart = $this->du_time_start;
			$oldend   = $this->du_time_end;
			// Mark that this du no longer has a duration
			$this->du_has_duration = FALSE;
			// Unset start and end times
			$this->du_time_start = NULL;
			$this->du_time_end   = NULL;
			$updateQuery = "
				UPDATE dus
				SET    du_time_start   = NULL,
					   du_time_end     = NULL,
					   du_has_duration = '0'
				WHERE  du_id           = '" . $this->du_id . "'"
				;
			if (query($updateQuery, "unsetDuration()") === TRUE) {
				// Record successful record update
				$output  = date("Y-m-d H:i:s T", time());
				$output .= " Updated record for du_id ";
				$output .= $this->du_id;
				$output .= " successfully: unlinked duration '";
				$output .= $oldstart . " - " . $oldend . "'. \n";
				fwrite($log, $output, 256);
			}
		} else {
			// No deadline to unset
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " No duration to unset for du_id ";
			$output .= $this->du_id . ".\n";
			fwrite($log, $output, 256);
		}
	}


	/**
	 * Function hasTimeStart
	 * @return [boolean] If the du is linked to a start time
	 */
	public function hasTimeStart() {
		return isset($this->du_time_start);
	}


	/**
	 * Function getTimeStart
	 * @return [string] Deadline or start time of the du, if it has one
	 */
	public function getTimeStart() {
		return $this->du_time_start;
	}


	/**
	 * Function setTimeStart
	 *
	 * Sets or updates the start time of a du at both object- and db-levels and
	 * logs any changes. Start time should only ever be unset as part of
	 * unsetting a date, deadline, or duration, and unsetting it by itself may
	 * cause problems.
	 * 
	 * @param [string] $du_time_start The new start time (or deadline) to give a
	 * du, formatted as "YYYY-MM-DD HH:MM:SS"
	 * @global [$log | The open log file]
	 * @return void
	 */
	public function setTimeStart($du_time_start) {
		global $log;
		// Remember old start time, if there was one originally
		$oldtime = ($this->du_time_start) ? $this->du_time_start : NULL;
		$this->du_time_start = $du_time_start;
		$updateQuery = "
			UPDATE dus
			SET    du_time_start = '" . $du_time_start . "'
			WHERE  du_id         = '" . $this->du_id . "'"
			;
		if (query($updateQuery, "setTimeStart()") === TRUE) {
			// Record successful record update
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Updated record for du_id ";
			$output .= $this->du_id . " successfully: ";
			$output .= ($oldtime) ? "changed time_start from '" . $oldtime . "' to '" . $du_time_start . "'.\n" :
								    "set time_start to '" . $du_time_start . "'.\n";
			fwrite($log, $output, 256);
		}
	}


	/**
	 * Function hasTimeEnd
	 * @return [boolean] If the du is linked to an end time
	 */
	public function hasTimeEnd() {
		return isset($this->du_time_start);
	}


	/**
	 * Function getTimeEnd
	 * @return [string] End time of the du, if it has one
	 */
	public function getTimeEnd() {
		return $this->du_time_end;
	}


	/**
	 * Function setTimeEnd
	 *
	 * Sets or updates the end time of a du at both object- and db-levels and
	 * logs any changes. End time should only ever be unset as part of unsetting
	 * a duration, and unsetting it by itself may cause problems.
	 * 
	 * @param [string] $du_time_end The new end time to give a du, formatted as
	 * "YYYY-MM-DD HH:MM:SS"
	 * @global [$log | The open log file]
	 * @return void
	 */
	public function setTimeEnd($du_time_end) {
		global $log;
		// Remember old end time, if there was one originally
		$oldtime = ($this->du_time_end) ? $this->du_time_end : NULL;
		$this->du_time_end = $du_time_end;
		$updateQuery = "
			UPDATE dus
			SET    du_time_end = '" . $du_time_end . "'
			WHERE  du_id       = '" . $this->du_id . "'"
			;
		if (query($updateQuery, "setTimeEnd()") === TRUE) {
			// Record successful record update
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Updated record for du_id ";
			$output .= $this->du_id . " successfully: ";
			$output .= ($oldtime) ? "changed time_start from '" . $oldtime . "' to '" . $du_time_end . "'.\n" :
								    "set time_start to '" . $du_time_end . "'.\n";
			fwrite($log, $output, 256);
		}
	}


	/**
	 * Function hasPriority
	 * @return [boolean] If the du is associated with (and enforces) a priority
	 */
	public function hasDuPriority() {
		return $this->du_enforce_priority;
	}


	/**
	 * Function getDuPriority
	 * @return [int] The priority recorded for the du
	 */
	public function getDuPriority() {
		return $this->du_priority;
	}


	/**
	 * Function setDuPriority
	 *
	 * Sets or updates the priority associated with a du at both object- and
	 * db-levels and logs any changes. As outlined earlier with regards to
	 * calculating the overall du priority, calc_priority follows a specific
	 * flow:
	 * 
	 * If du_enforce_priority is TRUE --> calc_priority = du_priority
	 * If du_enforce_priority is FALSE --> calc_priority = min(tag_priorities)
	 * or 4 if tag_priorities is NULL
	 * 
	 * Setting or updating the du priority enforces its priority.
	 * 
	 * @param [string] $du_priority The new priority to record and enforce for the du
	 * @global [$log | The open log file]
	 * @return void
	 */
	public function setDuPriority($du_priority) {
		global $log;
		// Remember old du priority, if there was one originally specified
		$oldpriority = ($this->du_enforce_priority) ? $this->du_priority : NULL;
		$oldcalc = $this->calc_priority;

		// Mark that this du has a priority, if it wasn't already marked
		$this->du_enforce_priority = TRUE;
		// Store new priority
		$this->du_priority = intval($du_priority);
		$this->calc_priority = intval($du_priority);
		$updateQuery = "
			UPDATE dus
			SET    du_enforce_priority = '1',
				   du_priority         = '" . $du_priority . "'
			WHERE  du_id               = '" . $this->du_id . "'"
			;
		if (query($updateQuery, "setDuPriority()") === TRUE) {
			// Record successful record update
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Updated record for du_id ";
			$output .= $this->du_id . " successfully: ";
			$output .= ($oldpriority) ? "changed priority from '" . $oldpriority . "' to '" . $du_priority . "'. " :
			                            "set priority to '" . $du_priority . "'. ";
			$output .= "Set calc_priority to '" . $du_priority . "'. \n";
			fwrite($log, $output, 256);
		}
	}


	/**
	 * Function unsetDuPriority
	 *
	 * Unsets the priority associated with a du at both object- and db-levels
	 * and logs any changes. As outlined earlier with regards to calculating the
	 * overall du priority, calc_priority follows a specific flow:
	 *
	 * If du_enforce_priority is TRUE --> calc_priority = du_priority
	 * If du_enforce_priority is FALSE --> calc_priority = min(tag_priorities)
	 * or 4 if tag_priorities is NULL
	 *
	 * Unsetting the du priority un-enforces its priority.
	 * 
	 * @global [$log | The open log file]
	 * @return void
	 */
	public function unsetDuPriority() {
		global $log;
		// If du had a priority previously
		if ($this->du_enforce_priority) {
			$oldpriority = $this->du_priority;
			// Mark that this du no longer has a priority
			$this->du_enforce_priority = FALSE;
			// Reset du priority to default
			$this->du_priority = 4;
			// Reset calc_priority
			$this->calc_priority = ($this->tag_priorities) ? min($this->tag_priorities) : 
						                                     4;
			$updateQuery = "
				UPDATE dus
				SET    du_enforce_priority = '0',
					   du_priority         = '4'
				WHERE  du_id               = '" . $this->du_id . "'"
				;
			if (query($updateQuery, "unsetDuPriority()") === TRUE) {
				// Record successful record update
				$output  = date("Y-m-d H:i:s T", time());
				$output .= " Updated record for du_id ";
				$output .= $this->du_id;
				$output .= " successfully: unset priority '";
				$output .= $oldpriority;
				$output .= "'. Set calc_priority to '" . $this->calc_priority . "'. \n";
				fwrite($log, $output, 256);
			}	
		} else {
			// No priority to unset
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " No priority to unset for du_id ";
			$output .= $this->du_id . ".\n";
			fwrite($log, $output, 256);
		}
		
	}


	/**
	 * Function hasNote
	 * @return [boolean] If the du is linked to note
	 */
	public function hasNote() {
		return isset($this->du_note);
	}


	/**
	 * Function getNote
	 * @return [string] The note recorded for the du
	 */
	public function getNote() {
		return $this->du_note;
	}


	/**
	 * Function setNote
	 *
	 * Sets or updates the note of a du at both object- and db-levels and logs
	 * any changes.
	 * 
	 * @param [string] $du_note The new note to record for the du
	 * @global [$log | The open log file]
	 * @return void
	 */
	public function setNote($du_note) {
		global $log;
		// Remember old note, if there was one originally
		$oldnote = ($this->du_note) ? $this->du_note : NULL;
		$this->du_note = $du_note;
		$updateQuery = "
			UPDATE dus
			SET    du_note = " . (($du_note) ? "'" . $du_note . "'" : "NULL") . "
			WHERE  du_id   = '" . $this->du_id . "'"
			;
		if (query($updateQuery, "setNote()") === TRUE) {
			// Record successful record update
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Updated record for du_id ";
			$output .= $this->du_id . " successfully: ";
			$output .= ($oldnote) ? "changed note from '" . $oldnote . "' to '" . $du_note . "'.\n" :
								    "set note to '" . $du_note . "'.\n";
			fwrite($log, $output, 256);
		}
	}


	/**
	 * Function unsetNote
	 *
	 * Sets note to NULL with setNote(NULL).
	 * 
	 * @return void
	 */
	public function unsetNote() {
		$this->setNote(NULL);
	}

	/**
	 * Function getStatus
	 * @return [string]  The status of a given Du. (Open/Active/Completed)
	 */ 
	public function getStatus() {
		return $this->du_status;
	}	

	/**
	 * Function setStatus
	 *
	 * Updates the status 
	 * 
	 * @param [string] $du_status The new status for the du
	 * @global [$log | The open log file]
	 * @return If successful
	 */
	public function setStatus($du_status) {
		if ($du_status == "Open" || $du_status == "Active" || $du_status == "Complete") {
			// remember the old status 
			$oldstatus = ($this->du_status);
			$this->du_status = $du_status;

			$updateQuery = "
				UPDATE dus 
				SET du_status = '" . $du_status . "'
				WHERE du_id = '" . $this->du_id . "'"; 

            if (query($updateQuery, "setStatus()") === TRUE) {
                // Record successful record update
                $output  = date("Y-m-d H:i:s T", time());
                $output .= " Updated record for du_id ";
                $output .= $this->du_id . " successfully: ";
                $output .= ($oldstatus) ? "changed status from '" . $oldstatus . "' to '" . $du_status . "'.\n" :
								    "set status to '" . $du_status . "'.\n";
			    fwrite($log, $output, 256);
		    }
			return true; 
		} else {
			return false;
		}
	}
    
    public function getUserID() {
        return $this->user_id;
    }
	
	/** function getTags
	*
	* return the tags
	* 
	* @return [array(tag)]
	*/
	public function getTags() {
		return $this->du_tags;
	}
	
	/**
	* function associateTag
	*
	* associates a tag with this du
	*
	* @param [tag] the tag to associate
	* @global log
	* @return boolean success
	*/
	public function associateTag($tag) {
		global $log;
		// check that the tag exists
		$tag_id = $tag->getID();
		$queryStatement = "
        SELECT tag_id
        FROM tags
        WHERE tag_id = '" . $tag_id . "'";
        $result = query($queryStatement, "associateTag");
        $currRow = $result->fetch_assoc();
        if ($currRow == NULL){
            $output  = date("Y-m-d H:i:s T", time());
            $output .= " Could not associate du " . $this->du_name . 
				"with tag id " . $tag_id . 
				": no tag with that ID found.";
            // Write to log file and kill process
            fwrite($log, $output, 2048);
            exit($output);
        }
		
		// check that the pairing doesn't already exist
		$queryStatement = "
        SELECT *
        FROM du_tag_pairs
        WHERE tag_id = '" . $tag_id . "'" . 
			"AND du_id = '" . $this->du_id . "'" . 
			";";
        $result = query($queryStatement, "associateTag");
        $currRow = $result->fetch_assoc();
        if ($currRow != NULL){
            $output  = date("Y-m-d H:i:s T", time());
            $output .= " Could not associate du " . $this->du_name . 
				"with tag id " . $tag_id . 
				": du-tag pair already found.";
            // Write to log file BUT DO NOT kill process
            fwrite($log, $output, 2048);
			$returnval = false;
            //exit($output);
        } else {
		
			// update database
			$queryStatement = "
			INSERT INTO du_tag_pairs (
			du_id, tag_id)
			VALUES (" 
				. $this->du_id . ","
				. $tag_id . ");";
			if (query($queryStatement, "associateTag") === true) {
				// Record successful record update
				$output  = date("Y-m-d H:i:s T", time());
				$output .= " Updated record for du_id ";
				$output .= $this->du_id . " successfully: ";
				$output .= "associated with tag " . $tag_id;
				fwrite($log, $output, 256);
				$returnval = true;
			} else {
				$returnval = false;
			}
		}
		// and add to array
		$this->du_tags[$tag_id]=$tag;
		$tag->tag_dus[$this->du_id]=$this;
		return $returnval;
	}
	
	/** function dissociateTag
	*
	* dissociates a tag with this du
	*
	* @param [tag] the tag to dissociate
	* @global log
	* @return boolean success
	*/
	function dissociateTag ($tag) {
		global $log;
		// check that the tag exists
		$tag_id = $tag->getID();
		$queryStatement = "
        SELECT tag_id
        FROM tags
        WHERE tag_id = '" . $tag_id . "'";
        $result = query($queryStatement, "associateTag");
        $currRow = $result->fetch_assoc();
        if ($currRow == NULL){
            $output  = date("Y-m-d H:i:s T", time());
            $output .= " Could not dissociate du " . $this->du_name . 
				"with tag id " . $tag_id . 
				": no tag with that ID found.";
            // Write to log file and kill process
            fwrite($log, $output, 2048);
            exit($output);
        }
		
		// check that the pairing exists
		$queryStatement = "
        SELECT *
        FROM du_tag_pairs
        WHERE tag_id = '" . $tag_id . "'" . 
			"AND du_id = '" . $this->du_id . "'" . 
			";";
        $result = query($queryStatement, "associateTag");
        $currRow = $result->fetch_assoc();
        if ($currRow == NULL){
            $output  = date("Y-m-d H:i:s T", time());
            $output .= " Could not dissociate du " . $this->du_name . 
				"with tag id " . $tag_id . 
				": du-tag pair not found.";
            // Write to log file and kill process
            fwrite($log, $output, 2048);
            exit($output);
        }
		// update database
		$queryStatement = "
		DELETE FROM du_tag_pairs 
		WHERE " 
			. "du_id = '" . $this->du_id . "'"
			. "AND tag_id = '" . $tag_id . "';";
		if (query($queryStatement, "dissociateTag") === true) {
			// Record successful record update
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Updated record for du_id ";
			$output .= $this->du_id . " successfully: ";
			$output .= "dissociated from tag " . $tag_id;
			fwrite($log, $output, 256);
			// and remove from arrays
			unset($this->du_tags[$tag_id]);
			unset($tag->tag_dus[$this->du_id]);
			return true; 
		} else {
			return false;
		}
		
	}
}

?>