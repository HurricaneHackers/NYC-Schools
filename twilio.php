<?php

$search = $_REQUEST['Body'];

$school = search_schools($search);

if (!empty($school)) {

	$bn = $school['locationCode'];
	$name = ucwords(strtolower($school['name']));
		
	$impacted = search_impacted_schools($bn);


	if (!empty($impacted[0]['receiving_bn'])) {

		$count = 1;
		foreach ($impacted as $check) {
			
			$display_name = ($count == 1) ? "$name:" : ' / ';
			
			$r_name = $check['receiving_name'];
			$r_name = ucwords(strtolower($r_name));
			$r_address = $check['receiving_address'];
			$r_address = ucwords(strtolower($r_address));
			$r_studentopen = $check['studentopen'];
			$r_grades = ucwords(strtolower($check['program']));

			$response .= "$display_name $r_grades relocated to $r_name at $r_address " . check_date($r_studentopen);			
			
			$count++;
		}


	}
	elseif (!empty($impacted[0]['studentopen'])) {

		foreach ($impacted as $check) {
			
			$r_studentopen = $check['studentopen'];
			$r_grades = ucwords(strtolower($check['program']));

			$response .= "$name: $r_grades to return $r_studentopen";			
			
		}


	}
	else {

		$response = "$name is open as normal." . ' ' . check_date('Monday November 11');

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
	
	$query = "select * from `swdata` where bn = '$bn'";		
	$query = urlencode($query);

	$url = "https://api.scraperwiki.com/api/1.0/datastore/sqlite?format=jsondict&name=sandy_impacted_nyc_school_status&query=$query";		

	$data = curl_to_json($url);	
	
	if (!empty($data)) {
		return $data;
	}
	else {
		return false;
	}
	
}


function search_schools($search) {
	
//		http://schoolsstg.nycenet.edu/SchoolSearch/services/schoolrpc.ashx/schoolSearch?search=Bard%20High

	$query = urlencode($search);

	$url = "schools.nyc.gov/SchoolSearch/services/schoolrpc.ashx/schoolSearch?search=$query";		

	$data = curl_to_json($url);	
	
	if (!empty($data['result']['items'][0]['locationCode'])) {
		return $data['result']['items'][0]; //['items'][0]['dUSInformation']['locationCode'];
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

//checks to see if open date has passed by a week, returns appropriate message
function check_date($open_date) {
  
  $open_date_secs = strtotime($open_date);
  
  if ($open_date_secs > time()) {
	$response = "Opens on $open_date.";
	} elseif (($open_date_secs + 604800) > time()) {
 	  $response =  "Opened on $open_date.";
	}	
	//else show nothing
	else {
	  return $response;
	}
}


?>