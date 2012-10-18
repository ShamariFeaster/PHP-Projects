<?php

include("simple_html_dom.php");
$time_limit = $_POST['length'];
set_time_limit($time_limit); 
//echo 'Initial: ' . number_format(memory_get_usage(), 0, '.', ',') . " bytes<br>";

//     LOADING MULTIPLE ACCOUNT LOGIN INFO								  
//-----------------------------------------------------------------------
$file_name = 'multi login info\\login_list.txt';
if(file_exists($file_name)){
	$login_cnt = 0;
	$counter = 1;
	$counter2 = 0;
		 
	$name_list = file_get_contents($file_name);
	$name_list = explode(',' , $name_list);
	$login_array = array();
	foreach($name_list as $name){
		if($counter%2 != 0){
			$login_array[] = array('username'=>$name);
			$counter++;
			}
		else{
			$login_array[$counter2]['password'] = $name;
			$counter2++;
			$counter++;
			}
	}
	
}
if($login_array){
	$accts = count($login_array);
}
//-------------------------------------------------------------------------
$username = $_POST['uname'];
$password = $_POST['pword'];
$message  = $_POST['msg'];
$q = urlencode($_POST['kword']);
//$sn = Array();//holds screen names line 179
//                        FILES
//--------------------------------------------------------------------------
	//$indivdualizer = rand(1,30000);
	//$file = "screen name logs\\screen_name_list".$indivdualizer.".txt";
$file = 'screen name logs\\screen_name_list_'.$_POST['kword'].'_'.date("mdHi").'.txt';
$fp = fopen($file,"a"); 
$json_file_name = 'twit_info_'.$_POST['kword'].'_'.date("mdHi").'.json';
$json_hdr = array();
$twitresult = <<< HTML
<html>
<head>
 <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js" type="text/javascript"></script>
 <script type="text/javascript">

function refresh(){
	var interval = setInterval("search_started()",45000);
}



function search_started(){
	$.getJSON('$json_file_name', 
	function(data) {
		var info_array = data.tweet_sent.split("|");
		var length = info_array.length - 1;
	  $('.results').html(
	  '<p id="top">Search Started: ' + data.search_started + '</p><br />'
	  +'<p> You have sent '+length+' messages with this search</p>');
	    var counter = 0;
	    while(counter < info_array.length){
			$('.results').append(info_array[counter]+'<br />');
			counter++;
			}
		if(data.error_msg){
			$('.results #top').append(data.error_msg);
			}
		if(data.new_acc){
			$('.results #top').append(data.new_acc);
			}
					});
	
}

function clear(){
	$('.results').empty();
	var done = clearInterval(interval);
}

</script>
</head>
<body>
	<div class="whole" style="border:2px solid black;width:41%;">
	<table>
	<form name="myform" method="POST" action="" onsubmit="">

		<tr><td><input type="button" value="Get Results" onClick="refresh()"></td><td><input type="button" value="Clear" onClick="clear()"></td>
	</form>
	</div>
	<div class="results" style="border:2px solid black;width:100%;height:30%;overflow:scroll;"></div>
		
	
</body>
</html>
HTML;
$twitresultfile = fopen("twitresult.html","w");
fwrite($twitresultfile,$twitresult);
fclose($twitresultfile);
//--------------------------------------------------------------------------
include('functions.php');
//---------------------------------------------------------------------------
$flag = 1;
$var_uno = 0;
$json_flag = 0;


$time_limit /= 45;
for($g=0;$g<$time_limit;$g++){ //controller for
		//echo "Beginning controller loop. time: ".date('h:i:s A')."<br>";
		$s_flag = 0;
		
		if($var_uno == 0){
			$json_hdr['search_started'] = "You initaited a twitter search for the word '".$q."' at ".date('h:i:s A');
			}

		$request = "http://search.twitter.com/search.atom?q=$q&lang=en&rpp=50";
		$curl= curl_init();														
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);							
		curl_setopt ($curl, CURLOPT_URL,$request);
		$response = curl_exec ($curl);
		curl_close($curl);
		$response = str_replace("twitter:", "", $response);
		$xml = simplexml_load_string($response);
		
		for($i=0;$i<count($xml->entry);$i++){// for 1
			$json_write_flag = 0;
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
			//echo "Tweet being analyized: ".$tweet."<br>";
		//--------------------------------------------
			if(strpos($tweet,"<")==0 && strpos($tweet,"a")==1){ //if 3
				//echo "inside html filter<br>";
				$html = str_get_html("<html><body>$tweet</body></html>");
				$ret = $html->find('a', 0);
				$link_txt = $ret->innertext; 
				//---------------------------------
				$hash_cnt = substr_count($link_txt,"#"); //using this to filter out hastag tweets
				$is_at =  strpos($link_txt,"@");
				//echo "hash count: ".$hash_cnt."<br>";
				//echo "link text: ".$link_txt."<br>";
				//echo "strpos of @: ".$is_at."<br>";
				//echo "reply: ".$reply."<br>";
				//---------------------------------
				$screen_name = "@".$screen_name;
				
				if($hash_cnt < 1 && $reply == "web" && $is_at === 0){ // if 4
					//echo "passed hash count, reply, and @ filters<br>";
					$sn = array(); 
					$sn[] = $screen_name;
					//echo "print r sn[]<br>";
					//print_r($sn);											
						foreach($sn as $SN){ // this is where we send tweets and prevent duplicate tweets
							$flag = 0;       // to people. for_
							
							if($SN){// if 5
								$name_list = file_get_contents($file);
								$file_name_array = explode(",",$name_list);
								foreach($file_name_array as $fna){
									if(strcasecmp($SN, $fna)==0){ //inner if 1
										$flag = 1;
										} // end inner if 1
								}// end innerforeach
							
							unset($file_name_array);
							unset($name_list);
							if($flag == 0){ //inner if 2
								$holder = $SN.",";
								fwrite($fp, $holder); // this is screen name log file open on line ~68 in "file" section
								$flag =1;
								$holder = $SN." ".toggle_case($message);
								
								
								
								//---------------TWEET SEND------------------------------------------------------------------
								if(pt($username,$password,$holder)){
									// <IMPORTANT!>ENTER info on when the tweet im responding to was made <IMPORTANT!>
									//echo "Sleep started: ".date('h:i:s A');
									$json_hdr['tweet_sent'] .=  "$SN was sent a tweet at ".date('h:i:s A')." in response to theirs published at ".$published_time.
									"<br />$SN said: $tweet |";//a
									//echo "tweet sent. time: ".date('h:i:s A')."<br>";
											//echo 'Before Json dump: ' . number_format(memory_get_usage(), 0, '.', ',') . " bytes<br>";	
											if(!$json_flag){
												//              JSON
												//------------------------------------------------------
												$json_obj = json_encode($json_hdr);
												$json_rsc = fopen($json_file_name,"w");
												fwrite($json_rsc,$json_obj);
												fclose($json_rsc);
										
												unset($json_obj);
												unset($json_hdr);
												
										
												//print_r($json_hdr);
												$json_flag = 1;
												$json_write_flag = 1;												
												//------------------------------------------------------
											}else{
												$json_contents = file_get_contents($json_file_name);
											
												$json_hdr = json_decode($json_contents, true);
										
												$json_hdr['tweet_sent'] .=  "$SN was sent a tweet at ".date('h:i:s A')." in response to theirs published at ".$published_time.
												"<br />$SN said: $tweet |";
												$json_obj = json_encode($json_hdr);
												$json_rsc = fopen($json_file_name,"w");
												fwrite($json_rsc,$json_obj);
												fclose($json_rsc);
											
												unset($json_obj);
												unset($json_hdr);
												
												$json_write_flag = 1;
												}
												
												//echo 'After Json dump: ' . number_format(memory_get_usage(), 0, '.', ',') . " bytes<br>";	
									//echo "begining initiated sleep. time: ".date('h:i:s A')."<br>";
									flush();
									sleep(45);	
									//echo "ending initiated sleep. time: ".date('h:i:s A')."<br>";
									$s_flag = 1;
									unset($status_code);
									
									}elseif($error_msg){
										$json_hdr['error_msg'] =  "Attempted to send tweet at ".date('h:i:s A').//b
										" failed. Reason: ".$error_msg.
										" Returned status code: ".$status_code;
										
										//            ACCOUNT SWITCHING ACTION
										//-------------------------------------------------------
										if($status_code=="403" or $status_code=="401"){
											if($login_array){
												$password = $login_array[$login_cnt]['password'];
												$username = $login_array[$login_cnt]['username'];
												$login_cnt++;
												$json_hdr['new_acc'] = "<br />Switched to @".$username." account";
												if($login_cnt == $accts){ // this keeps the app cycling from last back to first UN/PW combo
													$login_cnt = 0;
													}
												}
											}
										//--------------------------------------------------------	
										unset($error_msg);
										unset($status_code);
											
											if(!$json_flag){
												//              JSON
												//------------------------------------------------------
												$json_obj = json_encode($json_hdr);
												$json_rsc = fopen($json_file_name,"w");
												fwrite($json_rsc,$json_obj);
												fclose($json_rsc);
												unset($json_obj);
												
												$json_flag = 1;	
												$json_write_flag = 1;
												//------------------------------------------------------
											}else{
												$json_contents = file_get_contents($json_file_name);
												$json_array = json_decode($json_contents, true);
												$json_array['error_msg'] = $json_hdr['error_msg'];
												$json_array['new_acc'] = $json_hdr['new_acc'];
												$json_obj = json_encode($json_array);
												$json_rsc = fopen($json_file_name,"w");
												fwrite($json_rsc,$json_obj);
												fclose($json_rsc);
												unset($json_array);
												unset($json_obj);
											
												$json_write_flag = 1;
												}
												
										//echo "begining tweet initiated sleep. time: ".date('h:i:s A')."<br>";
										flush();
										sleep(45);
										//echo "end initiated sleep. time: ".date('h:i:s A')."<br>";
										$s_flag = 1;
									} //end elseif
								} //end inner if 2	
							
							}// end if 5
							
						
						}// end outter foreach
					
					}// end if 4
			
			
			
			} //end if 3
			
//echo 'Before Json dump: ' . number_format(memory_get_usage(), 0, '.', ',') . " bytes<br>";	
if(!$json_flag && !$json_write_flag){
	//              JSON
	//------------------------------------------------------
	$json_obj = json_encode($json_hdr);
	$json_rsc = fopen($json_file_name,"w");
	fwrite($json_rsc,$json_obj);
	fclose($json_rsc);
	unset($json_obj);
	unset($json_hdr);

	$json_flag = 1;																					
	//------------------------------------------------------
	}elseif(!$json_write_flag){
	$json_contents = file_get_contents($json_file_name);
	$json_hdr = json_decode($json_contents, true);
	$json_obj = json_encode($json_hdr);
	$json_rsc = fopen($json_file_name,"w");
	fwrite($json_rsc,$json_obj);
	fclose($json_rsc);
	unset($json_obj);
	unset($json_hdr);
	
	}
	//echo "Garbage collection initated here.<br>";
	
	//echo 'After Garbage Collection: ' . number_format(memory_get_usage(), 0, '.', ',') . " bytes<br>";	

	//echo 'Peak: ' . number_format(memory_get_peak_usage(), 0, '.', ',') . " bytes<br>";		
	}// end entry for loop 
		unset($xml);
		unset($html);
		gc_collect_cycles();
		$var_uno++; 
		if(!$s_flag){
			//echo "begining default sleep. time: ".date('h:i:s A')."<br>";
			flush();
			sleep(45);
			//echo "end default sleep. time: ".date('h:i:s A')."<br>";
			}
		
		
		
		

} //controller for



?>