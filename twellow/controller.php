<?php
set_time_limit (0);
$answers = file_get_contents('http://98.230.11.152/twellow/twellow.php');
$answers = json_decode($answers);
$x = 1;
while($answers[1] == 0){
	
	$answers1 = file_get_contents('http://98.230.11.152/twellow/twellow.php');
	$answers1 = json_decode($answers1);
	$x++;
	if($x == 2) break;
}
echo "Done!";

?>