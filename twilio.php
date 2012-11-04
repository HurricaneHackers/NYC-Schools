<?php

$search = $_REQUEST['Body'];

$bn = search_schools($search);

if (!empty($bn)) {

	$check = search_impacted_schools($bn);

	if (!empty($check)) {

		$r_name = $check['receiving_name'];
		$r_name = ucwords(strtolower($r_name));
		$r_address = $check['receiving_address'];
		$r_address = ucwords(strtolower($r_address));
		$r_studentopen = $check['studentopen'];		
	

		$response = "School relocated to " . $r_name . ' at ' . $r_address . '. Open for students ' . $r_studentopen;

	}
	else {

		$response = "School open as normal";

	}

} else {
	
	$response = "School not found";

}


header("content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>


<Response>
<Sms><?php echo $response ?></Sms>
</Response>


<?php

function search_impacted_schools($bn) {
	
//	https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=sandy_impacted_nyc_school_status&query=select%20*%20from%20%60swdata%60%20where%20bn%20%3D%20'M696'
	
	$query = "select * from `swdata` where bn = '$bn' limit 1";		
	$query = urlencode($query);

	$url = "https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=sandy_impacted_nyc_school_status&query=$query";		

	$data = curl_to_json($url);	
	
	if (!empty($data[0]['receiving_bn'])) {
		return $data[0];
	}
	else {
		return false;
	}
	
}


function search_schools($search) {
	
//		http://schoolsstg.nycenet.edu/SchoolSearch/services/schoolrpc.ashx/schoolSearch?search=Bard%20High

	$query = urlencode($search);

	$url = "http://schoolsstg.nycenet.edu/SchoolSearch/services/schoolrpc.ashx/schoolSearch?search=$query";		

	$data = curl_to_json($url);	
	
	if (!empty($data['result']['items'][0]['locationCode'])) {
		return $data['result']['items'][0]['locationCode']; //['items'][0]['dUSInformation']['locationCode'];
	}
	else {
		return false;
	}
	
}



function curl_to_json($url) {
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	$data=curl_exec($ch);
	curl_close($ch);


	return json_decode($data, true);	
	
}


?>