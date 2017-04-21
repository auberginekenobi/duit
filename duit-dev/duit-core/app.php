<?php

/**
 * app.php
 *
 * Duit app landing page
 *
 * PHP v5.6
 *
 * @author    Kelli Rockwell <kellirockwell@mail.com>
 * @copyright 2017 DUiT
 * @since     File available since release 0.0.3  
 */

require_once('../duit-db/db-mapper.php');

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>DUiT | Be Fantasktic</title>
</head>
<body>


<?php

// Testing Example

displayAsTable($all);

// $parameters = array('du_name' => 'Take out the trash', 'du_has_date' => 1, 'du_time_start' => '2017-03-30');
// $all = addDu($parameters);

// test

// // $all[1]->unsetDuPriority();
// // $all[3]->unsetNote();

// displayAsTable($all);

// // $all = deleteDu(5);

// displayAsTable($all);

// $all[1]->setDuPriority("4");
// $all[3]->setNote("Make it extra yummy");

?>
	
	
</body>
</html>