<?php
function pt($username,$password,$message){
	global $error_msg;
	global $retry;
	global $status_code;
    $host = "http://twitter.com/statuses/update.xml?status=".urlencode(stripslashes(urldecode($message)));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_POST, 1);

    $result = curl_exec($ch);
    // Look at the returned header
    $resultArray = curl_getinfo($ch);

    curl_close($ch);

    if($resultArray['http_code'] == "200"){
        $twitter_status = true;
		$status_code = $resultArray['http_code'];
		}
	if($resultArray['http_code'] == "420"){
		$retry = $resultArray['Retry-After'];// put an if below that will make program sleep if $result !empty
		$error_msg = "Too many searches. Try again in ".$retry." seconds.";
		$status_code = $resultArray['http_code'];
		$twitter_status = false;
		}	
	if($resultArray['http_code'] == "403"){
   		$error_msg = "Too many status updates too fast<br />Use a different twitter account";
		$status_code = $resultArray['http_code'];
		$twitter_status = false;
		}
	if($resultArray['http_code'] == "401"){
   		$error_msg = "Your login info was incorrect";
		$status_code = $resultArray['http_code'];
		$twitter_status = false;
		}
	print_r($resultArray);
	return $twitter_status;
}

function is_alpha($someString){
		return (preg_match("/[A-Z]/i", $someString) > 0) ? true : false;
	}

function toggle_case($string){

	
	$counter = 0;
	$length = strlen($string);
	$rand = rand(0,$length);
	$msg_hld = str_split($string);
	foreach($msg_hld as $idv){
		
		if(is_alpha($idv) && $counter == $rand){
			$msg_hld[$counter] = strtoupper($idv);
			break;
		}
		$counter++;

	}
	$final_msg = implode($msg_hld);
	return $final_msg;

}
?>