<?php
set_time_limit (0);
include('functions.php');
//------------------------------------------------
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
	$accts = count($login_array) - 1;
}
//--------------------------------------------------


//INPUT
$username = 'alistproducer2';
$password = 'drumnba422';
$msg = "imm a big fan";
$msg = toggle_case($msg);
//--------------------------


$big_string = file_get_contents('screen_names.txt');
echo $big_string;
$big_list = explode(',', $big_string);
print_r($big_list);
unset($big_string);

foreach($big_list as $screen_name){

	$message = '@'.$screen_name.' '.$msg;

	pt($username,$password,$message);
	//            ACCOUNT SWITCHING ACTION
	//-------------------------------------------------------
	if($status_code=="403" or $status_code=="401"){
		if($login_array){
			$password = $login_array[$login_cnt]['password'];
			$username = $login_array[$login_cnt]['username'];
			$login_cnt++;
			if($login_cnt == $accts){ // this keeps the app cycling from last back to first UN/PW combo
				$login_cnt = 0;
				}
			}
		}
	//--------------------------------------------------------	
	
	sleep(10);
}				
												
								
										
		
		
		
		





?>