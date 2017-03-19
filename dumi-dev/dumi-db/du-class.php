<?php

/**
 * Class du
 *
 * Holds protected information for each du in database as individual PHP object
 * with particular object functions for access and modification
 */
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
	protected $du_tags;             // [array(string)]   Tags recorded for the du


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
	 * @param [string] $du_tags               Tags recorded for the du as a string, entries separated by ", "
	 */
	public function setDuFields($du_id, $du_timestamp, $du_name, $du_has_date, $du_has_deadline, $du_has_duration, $du_time_start, $du_time_end, $du_priority, $du_enforce_priority, $tag_priorities, $du_note, $du_tags) {

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
			$this->du_tags             = ($du_tags)                    ? explode(", ", $du_tags) :
			                                                             NULL; // Explode, if tags exist

			// Calculation of tag priority follows the flow below:
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
		$addHeaders  = "<tr><th>du_id</th>";
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
		$addHeaders .= "<th>du_tags</th></tr>";
		// If request for du headers
		$output   = ($headers) ? $addHeaders : "";
		// Convert booleans to strings
		$date     = ($this->du_has_date)         ? "TRUE" : "FALSE";
		$deadline = ($this->du_has_deadline)     ? "TRUE" : "FALSE";
		$duration = ($this->du_has_duration)     ? "TRUE" : "FALSE";
		$priority = ($this->du_enforce_priority) ? "TRUE" : "FALSE";
		// Add each cell
		$output .= "<tr><td>" . $this->du_id . "</td>";
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
		$output .= "<td>" . (($this->du_tags)        ? implode(", ", $this->du_tags) :
			                                           "") . "</td></tr>";

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
	 * Function setDate
	 *
	 * @todo  update this function description
	 * @param [string] $date The new date to link to a du, formatted as "YYYY-MM-DD" 
	 * @global [$log | The open log file]
	 */
	public function setDate($date) {

		global $log;
		$olddate = ($this->du_has_date) ? substr($this->du_time_start, 0, 10) : NULL;
		$justdate = $date;

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
		if ($this->du_has_date && $this->du_time_start) {
			$olddate = substr($this->du_time_start, 0, 10);
			// mark that this du no longer has a date
			$this->du_has_date = FALSE;
			// unset date stored in time_start field
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
			// no date to unset
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
	 * Function setDeadline
	 *
	 * @todo  update this function description
	 * @param [string] $deadline The new deadline to link to a du, formatted as "YYYY-MM-DD HH:MM:SS" 
	 * @global [$log | The open log file]
	 */
	public function setDeadline($deadline) {

		global $log;
		$olddeadline = ($this->du_has_deadline) ? $this->du_time_start : NULL;

		// unset other date-related properties, if they were set
		$this->unsetDate();
		$this->unsetDuration();

		// mark that this du has a deadline, if it didn't have one already
		$this->du_has_deadline = TRUE;
		// store deadline in time_start field
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
	 * @todo  update this function description
	 * @global [$log | The open log file]
	 */
	public function unsetDeadline() {
		global $log;
		// if du had a deadline previously
		if ($this->du_has_deadline && $this->du_time_start) {
			$olddeadline = $this->du_time_start;
			// mark that this du no longer has a date
			$this->du_has_deadline = FALSE;
			// unset deadline stored in time_start field
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
			// no deadline to unset
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " No deadline to unset for du_id ";
			$output .= $this->du_id . ".\n";
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
		$oldstart = ($this->du_has_date) ? $this->du_time_start : NULL;
		$oldend   = ($this->du_has_date) ? $this->du_time_end   : NULL;

		// unset other date-related properties, if they were set
		$this->unsetDate();
		$this->unsetDeadline();

		// mark that this du has a duration, if it didn't have one already
		$this->du_has_duration = TRUE;
		// store start and end times
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
	 * @todo  update this function description
	 * @global [$log | The open log file]
	 */
	public function unsetDuration() {
		global $log;
		// if du had a duration previously
		if ($this->du_has_duration && $this->du_time_start && $this->du_time_end) {
			$oldstart = $this->du_time_start;
			$oldend   = $this->du_time_end;
			// mark that this du no longer has a duration
			$this->du_has_duration = FALSE;
			// unset start and end times
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
			// no deadline to unset
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " No duration to unset for du_id ";
			$output .= $this->du_id . ".\n";
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
	 * Function getDuPriority
	 * @return [int] The priority recorded for the du
	 */
	public function getDuPriority() {
		return $this->du_priority;
	}


	/**
	 * Function setDuPriority
	 *
	 * @todo  update this function description
	 * @param [string] $du_priority The new priority to record for the du
	 * @global [$log | The open log file]
	 */
	public function setDuPriority($du_priority) {
		global $log;
		$oldpriority = ($this->du_enforce_priority) ? $this->du_priority : NULL;
		$oldcalc = $this->calc_priority;

		// mark that this du has a priority, if it didn't have one already
		$this->du_enforce_priority = TRUE;
		// store new priority
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
	 * @todo  update this function description
	 * @global [$log | The open log file]
	 */
	public function unsetDuPriority() {
		global $log;
		// if du had a priority previously
		if ($this->du_enforce_priority) {
			$oldpriority = $this->du_priority;
			// mark that this du no longer has a priority
			$this->du_enforce_priority = FALSE;
			// reset du priority to default
			$this->du_priority = 4;
			// reset calc_priority
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
			// no priority to unset
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " No priority to unset for du_id ";
			$output .= $this->du_id . ".\n";
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
		$oldnote = ($this->du_note) ? $this->du_note : NULL;
		$this->du_note = $du_note;
		$updateQuery = "
			UPDATE dus
			SET    du_note = '" . $du_note . "'
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

}

?>