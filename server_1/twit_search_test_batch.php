<?php
$holder = file_get_contents("screen name logs\screen_name_list.txt");
$screen_names = explode(",", $holder);
$chunks = array_chunk($screen_names, 2);
$time_array = array();
$tweet_text = array();
$rpp = 50; //returns per page
$limit = 20; //how long between tweets to be considered conversation 
set_time_limit(60000);
$conversation_count = 0;

foreach($chunks as $screen_names_chunk){
	$list_size = count($screen_names_chunk);
	

	
	
	foreach($screen_names_chunk as $screen_name){ //foreach screen name
		$screen_name = str_replace("@","",$screen_name);
		//URL encode the query string
		
		//request URL
		$request = "http://search.twitter.com/search.atom?from=$screen_name&rpp=$rpp";

		$curl= curl_init();

		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt ($curl, CURLOPT_URL,$request);

		$response = curl_exec ($curl);

		curl_close($curl);

		//remove "twitter:" from the $response string
		$response = str_replace("twitter:", "", $response);

		//convert response XML into an object
		$xml = simplexml_load_string($response);

		//wrapping the whole output with <result></result>
		echo "<results>";

		//loop through all the entry(s) in the feed
		for($i=0;$i<count($xml->entry);$i++){ // foreach tweet
			//get the id from entry
			$id = $xml->entry[$i]->id;

			//explode the $id by ":"
			$id_parts = explode(":",$id);

			//the last part is the tweet id
			$tweet_id = array_pop($id_parts);

			//get the account link
			$account_link = $xml->entry[$i]->author->uri;

			//get the image link
			$image_link = $xml->entry[$i]->link[1]->attributes()->href;

			//get name from entry and trim the last ")"
			$name = trim($xml->entry[$i]->author->name, ")");

			//explode $name by the rest "(" inside it
			$name_parts = explode("(", $name);

			//get the real name of user from the last part
			$real_name = trim(array_pop($name_parts));

			//the rest part is the screen name
			$screen_name = trim(array_pop($name_parts));

			//get the published time, replace T and Z with " " and trim the last " "
			$published_time = trim(str_replace(array("T","Z")," ",$xml->entry[$i]->published));

			//get the status link
			$status_link = $xml->entry[$i]->link[0]->attributes()->href;

			//get the tweet
			$tweet = $xml->entry[$i]->content;

			//remove <b> and </b> from the tweet. If you want to show bold keyword then you can comment this line
			$tweet = str_replace(array("<b>", "</b>"), "", $tweet);

			//get the source link
			$source = $xml->entry[$i]->source;

			//the result div that holds the information
			$result =  '<div class="result" id="'. $tweet_id .'">
					<div class="profile_image"><a href="'. $account_link .'"><img src="'. $image_link .'"></a></div>
					<div class="status">
						<div class="content">
							<strong><a href="'. $account_link .'">'.$screen_name.'</a></strong> '. $tweet .'
						</div>
						<div class="time">
							'. $real_name .' at <a href="'. $status_link .'">'. $published_time .'</a> via '. $source .'
						</div>
					</div>
				</div>';
			$hold = explode(" ", $published_time);

			$lcounter = 1;
											foreach($hold as $a){ // foeeach time
												if($lcounter%2 == 0){
													//echo $a."<br />";
													$holder = explode(":",$a);
													$time_array[] = $holder; 
													
													}
												$lcounter++;
											}// END foreach time
			
			$tweet_text[] = $xml->entry[$i]->title;

			//print_r($time_array);
			//print_r($x);
			
			

		}// END foreach tweet
		
			
			
			
			$counter = 1;
			$tcounter = 0;
			foreach($time_array as $time){ // foreach Time stamps
				$flag = 0;
				if($time[0] == $time_array[$counter][0]){
					$difference = intval($time[1]) - intval($time_array[$counter][1]);
					
					if($difference < $limit){
						//echo $time[0].":".$time[1]." - ".$time_array[$counter][0].":".$time_array[$counter][1]."<br />";
						//echo "Time difference: ".$difference."<br />";
						//echo "time less than 20 minutes<br />";
						$flag = 1;
						}

					}
					
					if($time[0] != $time_array[$counter][0]){
						$bottom_time = 60 - intval($time_array[$counter][1]);
						$difference = intval($time[1]) + $bottom_time;
						if($difference < $limit){
							//echo $time[0].":".$time[1]." - ".$time_array[$counter][0].":".$time_array[$counter][1]."<br />";
							//echo "Time difference: ".$difference."<br />";
							//echo "time less than 20 minutes<br />";
							$flag = 1;
							}
						}
					
					if(!$flag){
						//echo $time[0].":".$time[1]." - ".$time_array[$counter][0].":".$time_array[$counter][1]."<br />";
						//echo "Time difference: ".$difference."<br />";
						//echo "time more than 20 minutes<br />";
					}
					
					if(strpos($tweet_text[$tcounter], "@") == 0){
						if($tcounter <= count($xml->entry)){
							//echo "-------------------------------------------------------------------------------<br />";
							//echo $tweet_text[$tcounter]."<br />";
							
							//echo "@ position: ".strpos($tweet_text[$tcounter], "@")."<br />";
							if(preg_match("#@([A-Za-z0-9_]+)#",$tweet_text[$tcounter], $matches)){
								$temp = $tcounter + 1;
								preg_match("#@([A-Za-z0-9_]+)#",$tweet_text[$temp], $matches2);
							}
							if($matches[0] == $matches2[0] && !empty($matches[0]) && !empty($matches2[0]) && $flag){
								//echo "Conditions for conversation have been met<br />";
								
								$conversation_count++;
								
								
							}
							
							//echo "Converstaion count: ".$conversation_count."<br />";
							//echo "-------------------------------break ".$counter."------------------------------<br />";
							//print_r($matches);
							//print_r($matches2);
						}
					}
					
			$counter++;
			$tcounter++;
			}//end foreach time stamp
			
			


		echo "</results>";
	}//END foreach screen name
	$cout = count($tweet_text);
	$percent = $cout/100;
	$success = $conversation_count/$percent;
	echo "<br>Your analysis of <strong>".$list_size."</strong> profiles and <strong>".$cout."</strong> tweets  has yielded <strong>".$conversation_count.
			"</strong> instances where the user would have seen our ad.<br />This is a success rate of ".round($success,2)."%";
	$results = "\r\nYour analysis of ".$list_size." profiles and ".$cout." tweets  has yielded ".$conversation_count.
			" instances where the user would have seen our ad.\r\nThis is a success rate of ".round($success,2)."%";
	$fp = fopen("success_rate_logs\\success_rate_log.txt","a");
	fwrite($fp,$results);
	fclose($fp);
	$conversation_count = 0;
	$tweet_text = array();
	$time_array = array();
	$success = 0;
	/*
	print_r($tweet_text);
	echo "<br>TIME ARRAY<BR>";
	print_r($time_array);
	*/
}
?>