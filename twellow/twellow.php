<?php

$search = "dj";

include("simple_html_dom.php");
/*
set_time_limit($time_limit);
$ret = $html->find('a[id^="screen_name"]');
foreach($ret as $a){print $a."<br>";}
*/ 
$result = array();
$filename = "page_num.txt";
$fname2 = "screen_names.txt";
$snfile = fopen($fname2,'a');
$x = file_get_contents($filename);

$url = "http://www.twellow.com/search?q=$search&page_num=$x";
$html = file_get_html($url);

//   GET MAX NUMBER OF PAGES
$max_page =  $html->find('.num-of-num', 0);
$holder = explode(" ",$max_page);
$holder2 = array_pop($holder);
$fin_num = str_replace(",","",$holder2);
$fin_num = (int)$fin_num;
$result[] = $fin_num;
//----------------------------------------
$x < $fin_num ? $result[] = 0 : $result[] = 1;

echo json_encode($result);
for($i=0;$i<20;$i++){
	
	$ret = $html->find('a[id^="screen_name"]', $i);
	//print $ret->innertext."<br>";
	if($i%5 == 0 && $i != 0){
	$sn = $ret->innertext.",\r\n";
	}else{$sn = $ret->innertext.",";}
	fwrite($snfile,$sn);
	

}
$pnum = fopen($filename,'w');
$x = (int)$x;
$x++;
fwrite($pnum,$x);
fclose($pnum);
fclose($snfile);
		
		
		


?>