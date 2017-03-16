<!--


-->

<?php

function connect() {

	// Define connection as a static variable, to avoid connecting more than once 
    static $connection;

    // If a connection has not yet been established
    if(!isset($connection)) {
		// Load db config file as an array
		$config = parse_ini_file('config.ini'); 

		// Try and connect to the database
		$connection = new mysqli($config['dbhost'], $config['dbuser'], $config['dbpass'], $config['dbname']);
	}

	// If connection was not successful
	if($connection === false || $connection->connect_error) {
	    // Handle error
	    $output = "<p>Unable to connect to database </p>" . $connection->connect_error;
	    exit($output);
	}

	return $connection;

}

function query($query) {

    // Connect to the database
    $connection = connect();

    // Query the database
    $result = $connection->query($query);

    return $result;
}

function getAll() {

	$all = new ArrayObject();

	$result = query("SELECT * FROM Dus;");

	if($result === false) {
	    // Handle failure
	    echo "query getAll() failed";
	} else {
	    while ($currRow = $result->fetch_assoc()) {
	    	$newDu = new du();
	    	$newDu->setDuFields($currRow['du_id'],
	    						$currRow['du_timestamp'],
	    						$currRow['du_name'],
	    						$currRow['du_has_date'],
	    						$currRow['du_has_deadline'],
	    						$currRow['du_has_duration'],
	    						$currRow['du_time_start'],
	    						$currRow['du_time_end'],
	    						$currRow['du_note']);
	    	$all->append($newDu);
	    }
	}

}

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

	if($result === false) {
	    // Handle failure
	    echo "query getUseful() failed";
	} else {
	    echo "query getUseful() succeeded";
	}

}

getAll();


class du {
	protected $du_id;
	protected $du_timestamp;
	protected $du_name;
	protected $du_has_date;
	protected $du_has_deadline;
	protected $du_has_duration;
	protected $du_time_start;
	protected $du_time_end;
	protected $du_note;

	function __construct() {
	}

	public function setDuFields($du_id, $du_timestamp, $du_name, $du_has_date, $du_has_deadline, $du_has_duration, $du_time_start, $du_time_end, $du_note) {
		try {
			$this->du_id = $du_id;
			$this->du_timestamp = $du_timestamp;
			$this->du_name = $du_name;
			$this->du_has_date = $du_has_date;
			$this->du_has_deadline = $du_has_deadline;
			$this->du_has_duration = $du_has_duration;
			$this->du_time_start = $du_time_start;
			$this->du_time_end = $du_time_end;
			$this->du_note = $du_note;
		} catch (Exception $e) {
			$output  = "Caught exception: ";
			$output .= $e->getMessage();
			$output .= "\n";
			echo $output;
		}

	}
}

// Close result and db connection now that we are finished with them
$result->close();
$connection->close();

?>