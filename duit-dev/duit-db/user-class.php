<?php

/**
 * Class user
 *
 * For storing users and connecting them to user-created dus and tags
 *
 * @author Owen Chapman <owenchapman1@gmail.com>
 * @copyright  2017 DUiT
 * @since Time immemorial
 */
class user {
	
	// Protected properties of user
	protected $user_id;       // [string]
	protected $user_name;     // [string]     username
	
	/** 
	 * Constructor. Both fields required.
	 */
	function __construct($user_id, $user_name){
		$this->user_id = $user_id;
		$this->user_name = $user_name;
	}
	
	
	/**
	 * Function displayAsTableRow
	 *
	 * Outputs current user as a row of a table; primarily used for debugging
	 * 
	 * @param  [boolean] $headers Specify whether or not to include a row of headers directly before this user
	 * @return [string] Table row of user properties, as a string
	 */
	public function displayAsTableRow($headers) {
		// Set up headers
		$addHeaders = "<tr><th>user_id</th>";
		$addHeaders .= "<th>user_name</th>";
		$addHeaders .= "</tr>";
		
		// If request for headers
		$output   = ($headers) ? $addHeaders : "";
		
		// Add each cell
		$output .= "<tr><td>" . $this->user_id . "</td>";
		$output .= "<td>" . $this->user_name . "</td>";
		$output .= "</tr>";
		
		//done
		return output;
	}
	
	/**
	 * Function addToDB
	 *
	 * Adds user to the database if there is not already an entry at its user_id. NOTE:
	 * This should only be used to add NEW users to the database, never to update
	 * existing users.
	 *
	 * @global [$log | The open log file]
	 * @return void
	 */
	public function addToDB() {
		global $log;

		// Check if there is a user already with this new user's user_id
		$checkQuery = "
		SELECT *
		FROM   users
		WHERE  user_id = " . $this->user_id
		;
		if (query($checkQuery, "addToDB()")->fetch_array()) { // If a user already exists
			// Handle failure
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Could not add new user to database: user with user_id '";
			$output .= $this->user_id . "' already exists.\n";
			// Write to log file and kill process
			fwrite($log, $output, 2048);
			exit($output);
		} else { // If no such user exists
			// Get current max user_id from users table 
			$resetValQuery  = "
			SELECT MAX(user_id)
			FROM   users"
			;
			$resetValResult = query($resetValQuery, "addToDB()");
			// Get actual value of max user_id query result
			$resetVal       = $resetValResult->fetch_array()[0];
			// Reset user auto-incrementer in case of deletions to ensure proper
			// user_id is recorded 
			$resetQuery     = "ALTER TABLE users auto_increment = " . ($resetVal + 1);
			query($resetQuery, "addToDB()");

			// Insert where
			$insertQuery  = "
			INSERT INTO users
			(user_name)";

			// Insert what
			$insertQuery .= "
			VALUES ('" . $this->user_name . "'";
			$insertQuery .= ");";

			query($insertQuery, "addToDB()");

			// Record success
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Added new user to database with user_id of '";
			$output .= $this->user_id;
			$output .= " and user_name" . $this->user_name . ".\n";
			fwrite($log, $output, 2048);

			// Get ID created for user in database
			$getIDResult = query("SELECT LAST_INSERT_ID()", "addToDB()");
			$getID       = $getIDResult->fetch_array()[0];

			// If user_id does not match ID created in database
			if ($this->user_id != $getID) {
		// Alert bad add
				$output  = date("Y-m-d H:i:s T", time());
				$output .= " Alert: user_id in array and user_id in database do not match. Changing user_id in array to '" . $getID . "'.\n";
				fwrite($log, $output, 2048);

		// Force user_id to match database
				$this->user_id = $getID;
			} else {
		// Record ID match
				$output  = date("Y-m-d H:i:s T", time());
				$output .= " Confirmed user_id in array (" . $this->user_id . ") matches user_id in database (" . $getID . ").\n";
				fwrite($log, $output, 2048);
			}
		}
	}
	
	/**
	 * Function deleteFromDB
	 *
	 * Removes du from the database if it finds one at its du_id.
	 *
	 * @todo finish conditions
	 * @global [$log | The open log file]
	 * @return void
	 */
	public function deleteFromDB() {
		global $log;

		// Check if there is a user with this new user's id to delete
		$checkQuery = "
		SELECT *
		FROM users
		WHERE user_id = " . $this->user_id
		;
		if (query($checkQuery, "deleteFromDB()")->fetch_array()) { // If user exists
			$deleteQuery = "
			DELETE FROM users
			WHERE user_id = '" . $this->user_id . "'"
			;
				// Delete
			query($deleteQuery, "deleteFromDB()");

				// Record success
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Deleted du from database with user_id of '";
			$output .= $this->user_id . "'.\n";
			fwrite($log, $output, 2048);
			} else { // If no user is found
				// Handle failure
				$output  = date("Y-m-d H:i:s T", time());
				$output .= " Could not delete user from database: item with user_id '";
				$output .= $this->user_id . "' does not exist.\n";
				// Write to log file and kill process
				fwrite($log, $output, 2048);
				exit($output);
			}
		}

		public function getID() {
			return $this->user_id;
		}

		public function getUsername() {
			return $this->user_name;
		}
	}
	?>