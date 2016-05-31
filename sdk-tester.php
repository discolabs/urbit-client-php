<?php
	header('Content-Type: text/plain; charset=UTF-8');
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	ini_set('html_errors', false);
	
	require('UrbRequest.php');
	
	try {
		$urbit = new UrbRequest('40675642-c06d-490d-9cac-e0e5e085036f', 'AFXZD0XJ/7pcesnvi8dJRJ6ssUCj/kdJyQFJnmsj7JiBvmoq504GVjfwaOP1GG2iAJYcIXsCIH12q+8GKlcVTw==', true);
		
		var_dump($urbit->GetOpeningHours('2016-05-05', '2016-05-08'));
	}
	catch(Exception $e) {
		echo $e->getMessage();
	}