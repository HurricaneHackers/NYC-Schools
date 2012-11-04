<?php

$bn = $_REQUEST['Body'];

$search = search_schools($bn);

if (!empty($search)) {

	$check = search_impacted_schools($search);

	if (!empty($check)) {

		$response = "School has been relocated to " . $check;

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
		return $data[0]['receiving_name'];
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
	
	if (!empty($data['result']['items'][0]['dUSInformation']['locationCode'])) {
		return $data['result']['items'][0]['dUSInformation']['locationCode']; //['items'][0]['dUSInformation']['locationCode'];
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