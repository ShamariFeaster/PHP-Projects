<?php
include("simple_html_dom.php");

$rpp = 100;
$json_file_name = "trend_server_1.json";
$graph_file_name = "trend_server_1_graph.json";
$json_array = array();
$graph = array();
$sn = array();
$json_write_flag = 0;
$sleep_time = 60;
$time_limit = 16200; // in seconds
$temp = $time_limit;
$time_limit += 5;
$q = "&ands=ozone+magazine";
set_time_limit($time_limit); 
$loop_limit = $temp/$sleep_time;
$virgin = 0;

for($g=0;$g<$loop_limit;$g++){

		$request = "http://search.twitter.com/search.atom?q=$q&lang=en&rpp=$rpp";
		$curl= curl_init();														
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);							
		curl_setopt ($curl, CURLOPT_URL,$request);
		$response = curl_exec ($curl);
		curl_close($curl);
		$response = str_replace("twitter:", "", $response);
		$xml = simplexml_load_string($response);
		
		$entry_counter = 0;
	
		for($i=0;$i<count($xml->entry);$i++){// ENTRY FOR
				
			//echo "top of entry loop. time: ".date('h:i:s A')."<br>";
			$id = $xml->entry[$i]->id;
			$id_parts = explode(":",$id);
			$tweet_id = array_pop($id_parts);
			$account_link = $xml->entry[$i]->author->uri;
			$image_link = $xml->entry[$i]->link[1]->attributes()->href;
			$name = trim($xml->entry[$i]->author->name, ")");
			$name_parts = explode("(", $name);
			$real_name = trim(array_pop($name_parts));
			$screen_name = trim(array_pop($name_parts));
			$published_time = trim(str_replace(array("T","Z")," ",$xml->entry[$i]->published));
			$status_link = $xml->entry[$i]->link[0]->attributes()->href;
			$tweet = $xml->entry[$i]->content;
			$tweet = str_replace(array("<b>", "</b>"), "", $tweet);
			$source = $xml->entry[$i]->source; 
			$html = str_get_html("<html><body>$source</body></html>");
			$ret = $html->find('a', 0);
			$reply = $ret->innertext; 
			if($entry_counter == 0){
				$temp_hdr = $screen_name;
				}
			
			
			if(isset($sn[0])){
					if($sn[0] == $screen_name){
						$sn[0] = $temp_hdr;
						break;
						}
				}	
			
			if(!$virgin){$sn[] = $screen_name;$virgin = 1;}
			
			$entry_counter++;
			
		}// END ENTRY FOR


			
			
		
		
		if(!$json_write_flag){
			$json_array['search'] = $q; 
			$json_array['new_entry'] = 0;
			$json_array['sleep_period_secs'] = $sleep_time; 
			$json_array['start_time'] = date('m.d.y h:i:s A');	

			$graph['elements'] = array(array("type"=>"line","values" => array(),"width" => 1));
			$graph['title'] = array("text"=>"Searches for $q");
			$graph['y_axis'] = array("min"=>0,"max"=>100,"steps"=>10,"style"=>"font-size:5px;");	
			$graph['x_axis'] = array("label"=>"Running Average: Tweets/Minute");
			
			
			$json_obj = json_encode($json_array);
			$json_rsc = fopen($json_file_name,"w");
			fwrite($json_rsc,$json_obj);
			fclose($json_rsc);
			
			$json_obj = json_encode($graph);
			$json_rsc = fopen($graph_file_name,"w");
			fwrite($json_rsc,$json_obj);
			fclose($json_rsc);
			
			$json_write_flag = 1;
	
		}
		else{
			$json_contents = file_get_contents($json_file_name);
			$json_array = json_decode($json_contents, true);
			
			$graph_contents = file_get_contents($graph_file_name);
			$graph_object = json_decode($graph_contents);
			
			$json_array['search'] = $q; 
			$json_array['new_entry'] = $entry_counter;
			$json_array['entry_log'] .= $entry_counter.",";
			$averages = explode(',', $json_array['entry_log']);
			if(count($averages) > 1){
				$moving_avg = array_sum($averages)/(count($averages)-1);
				}
			if(isset($moving_avg)){
				$json_array['moving_avg'] .= round($moving_avg,2).",";
				$graph_object->elements[0]->values[] = round($moving_avg,2);
				}
			
			$json_obj = json_encode($json_array);
			$json_rsc = fopen($json_file_name,"w");
			fwrite($json_rsc,$json_obj);
			fclose($json_rsc);
			
			$json_obj = json_encode($graph_object);
			$json_rsc = fopen($graph_file_name,"w");
			fwrite($json_rsc,$json_obj);
			fclose($json_rsc);
			
			
			
				
		}
		
		unset($graph_contents);
		unset($graph_object);
		unset($graph);
		unset($averages);
		unset($json_obj);
		unset($json_array);
		unset($xml);
		unset($html);
		unset($response );
		unset($tweet);
		unset($name_parts);
		gc_collect_cycles();

		sleep($sleep_time);

}//CONTROLLER FOR LOOP END HERE

?>