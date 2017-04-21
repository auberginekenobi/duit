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

// echo '<pre>';
// var_dump($all);
// echo '</pre>';

displayAsTable($all);

$parameters = array('du_name' => 'pet cattypin\'s "head" if he wants it', 'du_note' => 'this is a tes\'t', 'user_id' => 'aMl3IxVzqQbZ2tcTiBsbC8A0ZfX2');
$all = addDu($parameters);

displayAsTable($all);

$all = deleteDu(5);

displayAsTable($all);

// $all = addDu($parameters);
// $all = addDu($parameters);
// $all = addDu($parameters);

// displayAsTable($all);

?>
	
	
</body>
</html>