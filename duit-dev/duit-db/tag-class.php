<?php

/**
 * Class tag
 *
 * Holds properties about each tag in the database as an object
 * with methods defined for accessing and modifying the properties
 *  PHP v5.6
 *
 * @author    Owen Chapman <osc12013@pomona.edu>
 * @copyright 2017 DUiT
 * @since     File available since the day the music died  
 */

require_once("du-class.php");
class tag {
    // protected tag properties
    protected $tag_id;           // int
    protected $tag_name;         // string
    protected $tag_priority;     // int, default 4
    protected $tag_note;         // string, optional
    protected $user_id;          // string
	public    $tag_dus;		 	 // array of du objects
								 // other classes need to play with this, so it's public.
    
    /**
     * Main constructor
     */
    function __construct(){
    }
    
    /**
     * Constructor with all the fields
     */
    public function setTagFields($tag_id, $tag_name, $tag_priority, $tag_note, $user_id){
        try {
            $this->tag_id       = $tag_id;
            $this->tag_name     = $tag_name;
            $this->tag_priority = $tag_priority;
            $this->tag_note     = $tag_note;
            $this->user_id      = $user_id;
        } catch (Exception $e) {
            // Handle exception
			$output  = "Tried to setTagFields, caught exception: ";
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
	 * Outputs current tag as a row of a table; primarily used for debugging
	 * 
	 * @param  [boolean] $headers Specify whether or not to include a row of headers directly before this du
	 * @return [string] Table row of tag properties, as a string
	 */
	public function displayAsTableRow($headers) {
		// Set up headers
		$addHeaders  = "<tr><th>tag_id</th>";
        $addHeaders .= "<th>tag_name</th>";
        $addHeaders .= "<th>tag_priority</th>";
        $addHeaders .= "<th>tag_note</th>";
        $addHeaders .= "<th>user_id</th>";
        $addHeaders .= "</tr>";
        
        // If request for du headers
		$output   = ($headers) ? $addHeaders : "";
        // Add each cell
		$output .= "<tr class=" . 'tag-' . $this->tag_id . "><td>" . $this->tag_id . "</td>";
        $output .= "<td>" . $this->tag_name . "</td>";
        $output .= "<td>" . $this->tag_priority . "</td>";
        $output .= "<td>" . $this->tag_note . "</td>";
        $output .= "<td>" . $this->user_id . "</td>";
        $output .= "</tr>";
        
        // Done
        return $output;
    }
    /**
 * Function addToDB
 *
 * Adds tag to the database if there is not already an entry at its tag_id. NOTE:
 * This should only be used to add NEW tags to the database, never to update
 * existing tags.
 *
 * @global [$log | The open log file]
 * @return void
 */
	public function addToDB() {

		global $log;

		// Check if there is a tag already with this new tag's tag_id
		$checkQuery = "
			SELECT *
			FROM   tags
			WHERE  tag_id = " . $this->tag_id
		;
    $checkQuery2 = "
      SELECT *
      FROM tags
      WHERE tag_name = " ."'" . $this->tag_name . "'"
    ;
		if (query($checkQuery, "addToDB()")->fetch_array() || query($checkQuery2, "addToDB()")->fetch_array()) { // If a tag already exists
			// Handle failure
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Could not add new tag to database: item with tag_id '";
			$output .= $this->tag_id . "' already exists.\n";
			// Write to log file and don't kill process
			fwrite($log, $output, 2048);
		   // exit($output);
		} else {// If no such tag exists
			// Get current max tag_id from tags table 
			$resetValQuery  = "
				SELECT MAX(tag_id)
				FROM   tags"
			;
			$resetValResult = query($resetValQuery, "addToDB()");
			// Get actual value of max tag_id query result
			$resetVal       = $resetValResult->fetch_array()[0];
			// Reset tags auto-incrementer in case of deletions to ensure proper
			// tag_id is recorded 
			$resetQuery     = "
				ALTER TABLE tags auto_increment = " . ($resetVal + 1)
			;
			query($resetQuery, "addToDB()");
            
            //insert where'
            $insertQuery = "
                INSERT INTO tags
                            (tag_name";
            $insertQuery .= ", tag_priority";
            $insertQuery .= ($this->tag_note) ? ", tag_note" : "";
            $insertQuery .= ", user_id";
            $insertQuery .= ")";
            
            // Insert what
            $insertQuery .= "
                VALUES ('" . $this->tag_name . "'";
            $insertQuery .= ",'" . $this->tag_priority . "'";
            $insertQuery .= ($this->tag_note) ? ",'" . ($this->tag_note) . "'" : "";
            $insertQuery .= ",'" . $this->user_id . "'";
			$insertQuery .= ");";
            
            query($insertQuery, "tag addToDB()");
            
            // Record success
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Added new tag to database with tag_id of '";
			$output .= $this->tag_id . "'.\n";
			fwrite($log, $output, 2048);

			// Get ID created for tag in database
			$getIDResult = query("SELECT LAST_INSERT_ID()", "addToDB()");
			$getID       = $getIDResult->fetch_array()[0];

			// If tag_id does not match ID created in database
			if ($this->tag_id != $getID) {
				// Alert bad add
				$output  = date("Y-m-d H:i:s T", time());
				$output .= " Alert: tag_id in array and tag_id in database do not match. Changing tag_id in array to '" . $getID . "'.\n";
				fwrite($log, $output, 2048);

				// Force tag_id to match database
				$this->tag_id = $getID;
			} else {
                // Record ID match
				$output  = date("Y-m-d H:i:s T", time());
				$output .= " Confirmed tag_id in array (" . $this->tag_id . ") matches tag_id in database (" . $getID . ").\n";
				fwrite($log, $output, 2048);
			}
        }
    }
    
    /**
	 * Function deleteFromDB
	 *
	 * Removes tag from the database if it finds one at its tag_id.
	 *
	 * @todo finish conditions
	 * @global [$log | The open log file]
	 * @return void
	 */
	public function deleteFromDB() {

		global $log;

		// Check if there is a du with this new tag's tag_id to delete
		$checkQuery = "
			SELECT *
			FROM   tags
			WHERE  tag_id = " . $this->tag_id
		;
		if (query($checkQuery, "deleteFromDB()")->fetch_array()) { // If there is a tag
			// dissocuate all dus from the tag to be deleted
			foreach ($tag_dus as $du){
				dissociateDu($du);
			}
			
			$deleteQuery = "
				DELETE FROM tags
				WHERE tag_id   = '" . $this->tag_id . "'"
				;
			// Delete it
			query($deleteQuery, "tag deleteFromDB()");

			// Record success
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Deleted tag from database with tag_id of '";
			$output .= $this->tag_id . "'.\n";
			fwrite($log, $output, 2048);
		} else { // If no tag is found
			// Handle failure
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Could not delete tag from database: item with tag_id '";
			$output .= $this->tag_id . "' does not exist.\n";
			// Write to log file and kill process
			fwrite($log, $output, 2048);
		    exit($output);
		}
    }
    
    // getters and setters
    public function getID() {
        return $this->tag_id;
    }
    
    public function getName() {
        return $this->tag_name;
    }
    
    public function setName($tag_name) {
        global $log;
		// Remember old name, if there was one originally
		$oldname = ($this->tag_name) ? $this->tag_name : NULL;
		$this->tag_name = $tag_name;
		$updateQuery = "
			UPDATE tags 
			SET    tag_name = '" . $tag_name . "' 
			WHERE  tag_id   = '" . $this->tag_id . "'"
			;
		if (query($updateQuery, "setName()") === TRUE) {
			// Record successful record update
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Updated record for tag_id ";
			$output .= $this->tag_id . " successfully: ";
			$output .= ($oldname) ? "changed tag_name from '" . $oldname . "' to '" . $tag_name . "'.\n" :
			                        "set name to '" . $tag_name . "'.\n";
			fwrite($log, $output, 256);
		}
    }
    
    public function getPriority() {
        return $this->tag_priority;
    }
    
    public function setPriority($tag_priority) {
        global $log;
		// Remember old priority, if there was one originally
		$oldname = ($this->tag_priority) ? $this->tag_priority : NULL;
		$this->tag_priority = $tag_priority;
		$updateQuery = "
			UPDATE tags 
			SET    tag_priority = '" . $tag_priority . "' 
			WHERE  tag_id   = '" . $this->tag_id . "'"
			;
		if (query($updateQuery, "setPriority()") === TRUE) {
			// Record successful record update
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Updated record for tag_id ";
			$output .= $this->tag_id . " successfully: ";
			$output .= ($oldname) ? "changed tag_priority from '" . $oldname . "' to '" . $tag_priority . "'.\n" :
			                        "set name to '" . $tag_priority . "'.\n";
			fwrite($log, $output, 256);
		}
    }
    
    public function hasNote(){
        return isset($this->tag_note);
    }
    
    public function getNote() {
        return $this->tag_note;
    }
    
    /**
	 * Function setNote
	 *
	 * Sets or updates the note of a tag at both object- and db-levels and logs
	 * any changes.
	 * 
	 * @param [string] $tag_note The new note to record for the tag
	 * @global [$log | The open log file]
	 * @return void
	 */
	public function setNote($tag_note) {
		global $log;
		// Remember old note, if there was one originally
		$oldnote = ($this->tag_note) ? $this->tag_note : NULL;
		$this->tag_note = $tag_note;
		$updateQuery = "
			UPDATE tags
			SET    tag_note = " . (($tag_note) ? "'" . $tag_note . "'" : "NULL") . "
			WHERE  tag_id   = '" . $this->tag_id . "'"
			;
		if (query($updateQuery, "setNote()") === TRUE) {
			// Record successful record update
			$output  = date("Y-m-d H:i:s T", time());
			$output .= " Updated record for tag_id ";
			$output .= $this->tag_id . " successfully: ";
			$output .= ($oldnote) ? "changed note from '" . $oldnote . "' to '" . $tag_note . "'.\n" :
								    "set note to '" . $tag_note . "'.\n";
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
    
    public function getUserID() {
        return $this->user_id;
    }
	
	
	public function getDus() {
		return $this->tag_dus;
	}
	
	public function associateDu($du) {
		$du->associateTag($this);
	}
	
	public function dissociateDu($du) {
		$du->dissociateTag($this);
	}
}
?>
