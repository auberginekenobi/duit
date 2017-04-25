<?php
	require __DIR__ . '/vendor/autoload.php';
	require_once '../duit-db/db-mapper.php';
	use \Firebase\JWT\JWT;

	JWT::$leeway = 60;

	if(!empty($_GET)) {

		//Set timezone for data storage
		date_default_timezone_set("America/Los_Angeles");

		//Initial data needed for verification of user
		$idToken = (isset($_GET["idToken"])) ? $_GET["idToken"] : "";
		$user_id = (isset($_GET["user_id"])) ? $_GET["user_id"] : "";
		$function_name = (isset($_GET["function_name"])) ? $_GET["function_name"] : "";

		//All properties of a du, may need to include check for undefined as well
		$du_name = (isset($_GET["du_name"])) ? $_GET["du_name"] : "";
		$du_has_date = (isset($_GET["du_has_date"])) ? $_GET["du_has_date"] : "";
		$du_has_deadline = (isset($_GET["du_has_deadline"])) ? $_GET["du_has_deadline"] : "";
		$du_has_duration = (isset($_GET["du_has_duration"])) ? $_GET["du_has_duration"] : "";
		$du_time_start = (isset($_GET["du_time_start"])) ? $_GET["du_time_start"] : "";
		$du_time_end = (isset($_GET["du_time_end"])) ? $_GET["du_time_end"] : "";
		$du_priority = (isset($_GET["du_priority"])) ? $_GET["du_priority"] : "";
		$du_enforce_priority = (isset($_GET["du_enforce_priority"])) ? $_GET["du_enforce_priority"] : "";
		$du_note = (isset($_GET["du_note"])) ? $_GET["du_note"] : "";
		$du_tags = (isset($_GET["du_tags"])) ? $_GET["du_tags"] : "";
		$du_status = (isset($_GET["du_status"])) ? $_GET["du_status"] : "";
		$du_id = (isset($_GET["du_id"])) ? $_GET["du_id"] : ""; //id used for deletion

		//User id parameters
		$user_name = (isset($_GET["user_name"])) ? $_GET["user_name"] : "";
		
			echo $function_name;

		if ($function_name=="displayAsTableDus"){
			displayTableDus_wrap($all); //Note is possible to call $function_name(all) 
		} elseif ($function_name =="displayAsTableUsers") {
			displayTableUsers_wrap($allusers); 
		} elseif($function_name == "displayAsTableTags"){
			displayTableTags_wrap($alltags);
		} elseif ($function_name == "addDu") {
			addDu_wrap();
		} elseif ($function_name=="deleteDu"){
			deleteDu_wrap();
		} else if ($function_name=="addTag") {
			addTag_wrap();
		} else if ($function_name=="addUser") {
			addUser_wrap();
		} 
	}

	function addUser_wrap(){
		global $idToken, $user_id, $all, $allusers;
		global $user_name;

		if (validateToken($idToken,$user_id)){
			$user_id = rand()+" "; // NOTE: TEST CODE, RANDOMLY GENERATES A USER ID FOR INSERT

			$parameters = array();

			$user_name != "" ? $parameters["user_name"] = $user_name : "";
			$user_id != "" ? $parameters["user_id"] = $user_id : "";


			print_r($parameters);

			$allusers = addUser($parameters);


			$result = array('message' => "success","added" => $parameters);
			echo json_encode($result);
		} else {
			$result = array('message'=>"fail: user_id or token not validated");
			echo json_encode($result);
		}
	}

	function deleteUser_wrap(){

	}

	function addTag_wrap(){
		global $idToken, $user_id, $all, $allusers, $alltags;
		global $tag_name, $tag_note;

		if (validateToken($idToken,$user_id)){
			$parameters = array();

			$tag_name != "" ? $parameters["tag_name"] = $tag_name : "";
			$tag_note != "" ? $parameters["tag_note"] = $tag_note : "";
			$user_id != "" ? $parameters["user_id"] = $user_id : "";

			$alltags = addUser($parameters);
			$result = array('message' => "success","added" => $parameters);
			echo json_encode($result);
		} else {
			$result = array('message'=>"fail: user_id or token not validated");
			echo json_encode($result);
		}
		
	}

	function deleteTag_wrap(){

	}


	//TODO: Include Tag support
	function addDu_wrap(){
		global $idToken, $user_id, $all;
		global $du_name, $du_has_date, $du_has_deadline, $du_has_duration,
					 $du_time_start, $du_time_end, $du_priority, $du_enforce_priority, $tag_priorities,
					 $calc_priority, $du_note, $du_tags, $du_status, $user_id;

		if (validateToken($idToken,$user_id)) {
			$parameters = array();

			$du_name != "" ? $parameters["du_name"] = $du_name : "";
			$du_has_date != "" ? $parameters["du_has_date"] = $du_has_date : "";
			$du_has_deadline != "" ? $parameters["du_has_deadline"] = $du_has_deadline : "";
			$du_has_duration != "" ? $parameters["du_has_duration"] = $du_has_duration : "";
			$du_time_start != "" ? $parameters["du_time_start"] = $du_time_start : "";
			$du_time_end != "" ? $parameters["du_time_end"] = $du_time_end : "";
			$du_priority != "" ? $parameters["du_priority"] = $du_priority : "";
			$du_enforce_priority != "" ? $parameters["du_enforce_priority"] = $du_enforce_priority : "";
			$tag_priorities != "" ? $parameters["tag_priorities"] = $tag_priorities : "";
			$calc_priority != "" ? $parameters["calc_priority"] = $calc_priority : "";
			$du_note != "" ? $parameters["du_note"] = $du_note : "";
			$du_tags != "" ? $parameters["du_tags"] = $du_tags : "";
			$du_status != "" ? $parameters["du_status"] = $du_status : "";
			$user_id != "" ? $parameters["user_id"] = $user_id : "";

			$all = addDu($parameters);
			$result = array('message' => "success","added" => $parameters);
			echo json_encode($result);
		} else {
			$result = array('message' => "fail: user_id or token not validated");
			echo json_encode($result);
		}
	}

	//TODO: Edit Du
	function editDu_wrap(){

	}

	//deleteDu does not work in cases where there are tags due to foreign key 
	//constraints
	function deleteDu_wrap(){
		global $idToken, $user_id, $all;
		global $du_id;

		if (validateToken($idToken,$user_id)) {
			$all = deleteDu($du_id);
			$result = array('message' => "success","deleted" => $du_id);
			echo json_encode($result);
		} else {
			$result = array('message' => "fail: user_id or token not validated");
			echo json_encode($result);
		}
	}

	function displayTableDus_wrap(){
		global $all;

		displayAsTable($all);
	}

	function displayTableUsers_wrap(){
		global $allusers;

		displayAsTable($allusers);
	}

	function displayTableTags_wrap(){
		global $alltags;

		displayAsTable($alltags);

	}

	//Validate token ensures the identity of the caller
	function validateToken($jwt,$user_id){
		//initial setting for validity
		$valid = false;

		$jwt_array = explode(".",$jwt);
		$jwt_headers = json_decode(base64_decode($jwt_array[0]));

		//Header Claims
		$encryption = "RS256";
		//Secrets from https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com
		$secrets = json_decode(file_get_contents('https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com'));

		$validEncryption = $encryption == $jwt_headers->alg;

		$validKey = false;
		foreach($secrets as $key=>$secret){
			if ($key == $jwt_headers->kid){
				$validKey = true;
				$theSecret = $secret;
			}
		}

		try {
			// Decodes given a specific secret
			$token = JWT::decode($jwt,$theSecret,array('RS256'));

			//Payload Claims
			$currentTime = time();
			$correctAudience = "duit-ba651";
			$correctIssuer = "https://securetoken.google.com/duit-ba651";

			//Payload checks
			$validTime = $currentTime >= $token->iat-60; //include 1 minute leeway
			$validExp = $currentTime < $token->exp;
			$validAud = $correctAudience == $token->aud;
			$validIssuer = $correctIssuer == $token->iss;
			$validSub = !empty($token->sub)  && $user_id == $token->sub;

			//Aggregate of all payload checks
			$valid = $validKey && $validEncryption && $validTime && $validExp && $validAud && $validIssuer && $validSub;
			
			// in the case that the encoding is invalid, keep going
		} catch (\Exception $e){
			$result = array('message' => "error with decoding");
			echo json_encode($result);
		}

		// checks if valid and also the user id of the token is the same
		// as the one locally
		return $valid;
	}



?>
