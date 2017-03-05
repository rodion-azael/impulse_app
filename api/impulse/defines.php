<?php

class APIConstants {
	public function __construct() {}
	private
	$service_type = array(
		'25' 	=> '19',
		'26'	=> '21',
		'24'	=> '20'
	);	
	
	public function getServiveType($type){
		return $this->service_type[$type];
	}
}
?>