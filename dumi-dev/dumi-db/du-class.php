<?php

/**
 * Class du
 *
 * Holds protected information for each du in database as individual PHP object
 * with particular object functions for access and modification
 */
class du {

	// Protected properties of du
	protected $du_id;		    // [string]  The du's du_id
	protected $du_timestamp;    // [string]  The timestamp recorded for the du
	protected $du_name;         // [string]  The name recorded for the du
	protected $du_has_date;     // [boolean] If the du is linked to a date
	protected $du_has_deadline; // [boolean] If the du is linked to a deadline
	protected $du_has_duration; // [boolean] If the du is linked to a start and end time
	protected $du_time_start;   // [string]  Deadline or start time of the du, if it has one
	protected $du_time_end;     // [string]  End time of the du, if it has one
	protected $calc_priority;   // [int]     Priority recorded for the du, leveraged by tag priority
	protected $du_note;         // [string]  The note recorded for the du


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
	 * @param [string] $du_id           The du's du_id
	 * @param [string] $du_timestamp    The timestamp recorded for the du
	 * @param [string] $du_name         The name recorded for the du
	 * @param [string] $du_has_date     "0" if the du is not linked to a date, "1" if the du is
	 * @param [string] $du_has_deadline "0" if the du is not linked to a deadline, "1" if the du is
	 * @param [string] $du_has_duration "0" if the du is not linked to a start and end time, "1" if the du is
	 * @param [string] $du_time_start   Deadline or start time of the du, if it has one
	 * @param [string] $du_time_end     End time of the du, if it has one
	 * @param [string] $calc_priority   Priority recorded for the du, leveraged by tag priorities
	 * @param [string] $du_note         The note recorded for the du
	 */
	public function setDuFields($du_id, $du_timestamp, $du_name, $du_has_date, $du_has_deadline, $du_has_duration, $du_time_start, $du_time_end, $calc_priority, $du_note) {

		try {
			// Instantiate parameters as object's properties
			$this->du_id           = $du_id;
			$this->du_timestamp    = $du_timestamp;
			$this->du_name         = $du_name;
			$this->du_has_date     = ($du_has_date == "0") ? FALSE : TRUE; // Convert to corresponding boolean
			$this->du_has_deadline = ($du_has_deadline == "0") ? FALSE : TRUE; // Convert to corresponding boolean
			$this->du_has_duration = ($du_has_duration == "0") ? FALSE : TRUE; // Convert to corresponding boolean
			$this->du_time_start   = $du_time_start;
			$this->du_time_end     = $du_time_end;
			$this->calc_priority   = intval($calc_priority); // Conver to int
			$this->du_note         = $du_note;
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
		$addHeaders  = "<tr><th>du_id</th>";
		$addHeaders .= "<th>du_timestamp</th>";
		$addHeaders .= "<th>du_name</th>";
		$addHeaders .= "<th>du_has_date</th>";
		$addHeaders .= "<th>du_has_deadline</th>";
		$addHeaders .= "<th>du_has_durataion</th>";
		$addHeaders .= "<th>du_time_start</th>";
		$addHeaders .= "<th>du_time_end</th>";
		$addHeaders .= "<th>calc_priority</th>";
		$addHeaders .= "<th>du_note</th></tr>";
		// If request for du headers
		$output   = ($headers) ? $addHeaders : "";
		// Convert booleans to strings
		$date     = ($this->du_has_date) ? "TRUE" : "FALSE";
		$deadline = ($this->du_has_deadline) ? "TRUE" : "FALSE";
		$duration = ($this->du_has_duration) ? "TRUE" : "FALSE";
		// Add each cell
		$output .= "<tr><td>" . $this->du_id . "</td>";
		$output .= "<td>" . $this->du_timestamp . "</td>";
		$output .= "<td>" . $this->du_name . "</td>";
		$output .= "<td>" . $date . "</td>";
		$output .= "<td>" . $deadline . "</td>";
		$output .= "<td>" . $duration . "</td>";
		$output .= "<td>" . $this->du_time_start . "</td>";
		$output .= "<td>" . $this->du_time_end . "</td>";
		$output .= "<td>" . $this->calc_priority . "</td>";
		$output .= "<td>" . $this->du_note . "</td></tr>";

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
 * @todo make function
 * @return void
 */
	public function addToDB() {
		// if entry does not already exist for du_id
			// add it	
		// else log could not add

	}


	/**
	 * Function getID
	 * @return [string] The du's du_id
	 */
	public function getID() {
		return $this->du_id;
	}


	/**
	 * Function getTimestamp
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
	 * @todo  update this function description
	 * @param [string] $du_name The new name to give the du
	 * @global [$log | The open log file]
	 */
	public function setName($du_name) {
		global $log;
		$oldname = $this->du_name;
		$this->du_name = $du_name;
		$updateQuery       = "
			UPDATE Dus
			SET du_name = '" . $du_name . "'
			WHERE du_id = '" . $this->du_id . "'"
			;
		if (query($updateQuery) === TRUE) {
			// Record successful record update
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Updated record for du_id ";
			$output .= $this->du_id;
			$output .= " successfully: changed du_name from '";
			$output .= $oldname . "' to '" . $du_name . "'. \n";
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
	 * Function setDate
	 *
	 * @todo  update this function description
	 * @param [string] $date The new date to link to a du, formatted as "YYYY-MM-DD" 
	 * @global [$log | The open log file]
	 */
	public function setDate($date) {

		global $log;

		// unset other date-related properties, if they were set
		$this->unsetDeadline();
		$this->unsetDuration();

		// mark that this du has a date, if it didn't have one already
		$this->du_has_date = TRUE;
		// append meaningless time to inputted date
		$date .= " 00:00:00";
		// store date in time_start field
		$this->du_time_start = $date;
		$updateQuery = "
			UPDATE Dus
			SET du_time_start = '" . $date . "',
				du_has_date = '1'
			WHERE du_id = '" . $this->du_id . "'"
			;
		if (query($updateQuery) === TRUE) {
			// Record successful record update
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Updated record for du_id ";
			$output .= $this->du_id;
			$output .= " successfully: linked date '";
			$output .= $date . "'. \n";
			fwrite($log, $output, 256);
		}

	}

	/**
	 * Function unsetDate
	 *
	 * Unsets the date from a du if one was originally present. Function sets du
	 * property du_time_start to NULL as opposed to using unset(du_time_start)
	 * in order to maintain du_time_start as a valid variable.
	 * 
	 * @todo  update this function description
	 * @global [$log | The open log file]
	 */
	public function unsetDate() {
		global $log;
		// if du had a date previously
		if ($this->du_time_start) {
			$olddate = substr($this->du_time_start, 0, 10);
			// mark that this du no longer has a date
			$this->du_has_date = FALSE;
			// unset date stored in time_start field
			$this->du_time_start = NULL;
			$updateQuery = "
				UPDATE Dus
				SET du_time_start = NULL,
					du_has_date = '0'
				WHERE du_id = '" . $this->du_id . "'"
				;
			if (query($updateQuery) === TRUE) {
				// Record successful record update
				$output  = date("Y-m-d H:i:s T", time());
				$output .= " Updated record for du_id ";
				$output .= $this->du_id;
				$output .= " successfully: unlinked date '";
				$output .= $olddate . "'. \n";
				fwrite($log, $output, 256);
			}
		} else {
			// no date to unset
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " No date to unset for du_id ";
			$output .= $this->du_id . "\n";
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
	 * Function setDeadline
	 *
	 * @todo  update this function description
	 * @param [string] $deadline The new deadline to link to a du, formatted as "YYYY-MM-DD HH:MM:SS" 
	 * @global [$log | The open log file]
	 */
	public function setDeadline($deadline) {

		global $log;

		// unset other date-related properties, if they were set
		$this->unsetDate();
		$this->unsetDuration();

		// mark that this du has a deadline, if it didn't have one already
		$this->du_has_deadline = TRUE;
		// store deadline in time_start field
		$this->du_time_start = $deadline;
		$updateQuery = "
			UPDATE Dus
			SET du_time_start = '" . $deadline . "',
				du_has_deadline = '1'
			WHERE du_id = '" . $this->du_id . "'"
			;
		if (query($updateQuery) === TRUE) {
			// Record successful record update
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Updated record for du_id ";
			$output .= $this->du_id;
			$output .= " successfully: linked deadline '";
			$output .= $deadline . "'. \n";
			fwrite($log, $output, 256);
		}

	}

	/**
	 * Function unsetDeadline
	 *
	 * @todo  update this function description
	 * @global [$log | The open log file]
	 */
	public function unsetDeadline() {
		global $log;
		// if du had a deadline previously
		if ($this->du_time_start) {
			$olddeadline = $this->du_time_start;
			// mark that this du no longer has a date
			$this->du_has_deadline = FALSE;
			// unset deadline stored in time_start field
			$this->du_time_start = NULL;
			$updateQuery = "
				UPDATE Dus
				SET du_time_start = NULL,
					du_has_deadline = '0'
				WHERE du_id = '" . $this->du_id . "'"
				;
			if (query($updateQuery) === TRUE) {
				// Record successful record update
				$output  = date("Y-m-d H:i:s T", time());
				$output .= " Updated record for du_id ";
				$output .= $this->du_id;
				$output .= " successfully: unlinked deadline '";
				$output .= $olddeadline . "'. \n";
				fwrite($log, $output, 256);
			}
		} else {
			// no deadline to unset
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " No deadline to unset for du_id ";
			$output .= $this->du_id . "\n";
			fwrite($log, $output, 256);
		}
	}


	/**
	 * Function hasDuration
	 * @return [boolean] If the du is linked to start and end time
	 */
	public function hasDuration() {
		return $this->du_has_duration;
	}


	/**
	 * Function setDuration
	 *
	 * @todo  update this function description
	 * @param [string] $start The new start time to link to a du, formatted as "YYYY-MM-DD HH:MM:SS"
	 * @param [string] $end   The new end time to link to a du, formatted as "YYYY-MM-DD HH:MM:SS" 
	 * @global [$log | The open log file]
	 */
	public function setDuration($start, $end) {

		global $log;

		// unset other date-related properties, if they were set
		$this->unsetDate();
		$this->unsetDeadline();

		// mark that this du has a duration, if it didn't have one already
		$this->du_has_duration = TRUE;
		// store start and end times
		$this->du_time_start = $start;
		$this->du_time_end = $end;
		$updateQuery = "
			UPDATE Dus
			SET du_time_start = '" . $start . "',
				du_time_end = '" . $end . "',
				du_has_duration = '1'
			WHERE du_id = '" . $this->du_id . "'"
			;
		if (query($updateQuery) === TRUE) {
			// Record successful record update
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Updated record for du_id ";
			$output .= $this->du_id;
			$output .= " successfully: linked duration '";
			$output .= $start . " - " . $end . "'. \n";
			fwrite($log, $output, 256);
		}

	}

	/**
	 * Function unsetDuration
	 *
	 * @todo  update this function description
	 * @global [$log | The open log file]
	 */
	public function unsetDuration() {
		global $log;
		// if du had a duration previously
		if ($this->du_time_start && $this->du_time_end) {
			$oldstart = $this->du_time_start;
			$oldend   = $this->du_time_end;
			// mark that this du no longer has a duration
			$this->du_has_duration = FALSE;
			// unset start and end times
			$this->du_time_start = NULL;
			$this->du_time_end   = NULL;
			$updateQuery = "
				UPDATE Dus
				SET du_time_start = NULL,
					du_time_end = NULL,
					du_has_duration = '0'
				WHERE du_id = '" . $this->du_id . "'"
				;
			if (query($updateQuery) === TRUE) {
				// Record successful record update
				$output  = date("Y-m-d H:i:s T", time());
				$output .= " Updated record for du_id ";
				$output .= $this->du_id;
				$output .= " successfully: unlinked duration '";
				$output .= $oldstart . " - " . $oldend . "'. \n";
				fwrite($log, $output, 256);
			}
		} else {
			// no deadline to unset
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " No duration to unset for du_id ";
			$output .= $this->du_id . "\n";
			fwrite($log, $output, 256);
		}
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
	 * @todo  update this function description
	 * @param [string] $du_time_start The new start time (or deadline) to give a du, formatted as "YYYY-MM-DD HH:MM:SS" 
	 * @global [$log | The open log file]
	 */
	public function setTimeStart($du_time_start) {
		global $log;
		$oldtime = $this->du_time_start;
		$this->du_time_start = $du_time_start;
		$updateQuery       = "
			UPDATE Dus
			SET du_time_start = '" . $du_time_start . "'
			WHERE du_id = '" . $this->du_id . "'"
			;
		if (query($updateQuery) === TRUE) {
			// Record successful record update
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Updated record for du_id ";
			$output .= $this->du_id;
			$output .= " successfully: changed du_time_start from '";
			$output .= $oldtime . "' to '" . $du_time_start . "'. \n";
			fwrite($log, $output, 256);
		}
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
	 * @todo  update this function description
	 * @param [string] $du_time_end The new end time to give a du, formatted as "YYYY-MM-DD HH:MM:SS" 
	 * @global [$log | The open log file]
	 */
	public function setTimeEnd($du_time_end) {
		global $log;
		$oldtime = $this->du_time_end;
		$this->du_time_end = $du_time_end;
		$updateQuery       = "
			UPDATE Dus
			SET du_time_end = '" . $du_time_end . "'
			WHERE du_id = '" . $this->du_id . "'"
			;
		if (query($updateQuery) === TRUE) {
			// Record successful record update
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Updated record for du_id ";
			$output .= $this->du_id;
			$output .= " successfully: changed du_time_end from '";
			$output .= $oldtime . "' to '" . $du_time_end . "'. \n";
			fwrite($log, $output, 256);
		}
	}


	/**
	 * Function getPriority
	 * @return [int] The priority recorded for the du
	 */
	public function getPriority() {
		return $this->calc_priority;
	}


	/**
	 * Function setPriority
	 *
	 * @todo  update this function description
	 * @param [string] $calc_priority The new note to record for the du
	 * @global [$log | The open log file]
	 */
	public function setPriority($calc_priority) {
		global $log;
		$oldpriority = $this->calc_priority;
		$this->calc_priority = $calc_priority;
		$updateQuery = "
			UPDATE Dus
			SET calc_priority = '" . $calc_priority . "'
			WHERE du_id = '" . $this->du_id . "'"
			;
		if (query($updateQuery) === TRUE) {
			// Record successful record update
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Updated record for du_id ";
			$output .= $this->du_id;
			$output .= " successfully: changed calc_priority from '";
			$output .= $oldpriority . "' to '" . $calc_priority . "'. \n";
			fwrite($log, $output, 256);
		}
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
	 * @todo  update this function description
	 * @param [string] $du_note The new note to record for the du
	 * @global [$log | The open log file]
	 */
	public function setNote($du_note) {
		global $log;
		$oldname = $this->du_note;
		$this->du_note = $du_note;
		$updateQuery = "
			UPDATE Dus
			SET du_note = '" . $du_note . "'
			WHERE du_id = '" . $this->du_id . "'"
			;
		if (query($updateQuery) === TRUE) {
			// Record successful record update
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Updated record for du_id ";
			$output .= $this->du_id;
			$output .= " successfully: changed du_note from '";
			$output .= $oldname . "' to '" . $du_note . "'. \n";
			fwrite($log, $output, 256);
		}
	}

}

?>