<?php
header('Content-type: text/html; charset=utf-8');
$data = array(
	0 => array("5 + 5 =",10),	
	1 => array("6 + 2 =",8),
	2 => array("2 + 2 =",4),
	3 => array("8 + 1 =",9),
	4 => array("3 + 4 =",7)
);

class AntiSpam{

	public static function getAnswerById($id){
		global $data;
		
		return $data[$id][1];
	}	
	
	public static function getRandomQuestion(){
		global $data;
		
		$rand = rand(0,count($data)-1);
		return array($rand,$data[$rand][0]);
	}
	
}

?>