<?php
include_once("config.php");

class person {

	private $name;
	private $height;
	private $dob;
	private $hobby;
	private $hobbies;
	private $interests;


	public function __construct($name,$height,$dob,$interests,$hobby,$hobbies){
		$this->name = $name;
		$this->height = $height;
		$this->dob = date('Y-m-d',strtotime($dob));
		$this->interests = $interests;
		$this->hobby = $hobby;
		$this->hobbies = $hobbies;
		
	}
	public function get_name(){
		return $this->name ;
	}
	public function get_height(){
		return $this->height ;

	}
	public function get_dob(){
		return $this->dob ;

	}
	public function get_hobby(){
		return $this->hobby; //->name ;

	}
	public function get_interests(){
		return $this->interests ;

	}


}


class hobby {

	var $name;
	var $interests;
	
	public function __construct($name, $interests){
		$this->name = $name;
		$this->interests = $interests;
	}
	

}


class   personalHobby {

	var $persons;
	var $hobby; 
	public function __construct(){
	
	}


	public function get_json(){
		/* returns json on success or an exception on failure */

		$url = "https://api.crystal-d.com/codetest"; 
		$json = file_get_contents($url);
		if (!$json){
			throw new Exception("Can not Load Json");
		}
		$data = json_decode($json);
		if (!$data){
			throw new Exception("Can not decode Json");
		}

		return $data;

	}

	public function load_json(){
		/*  loads json into objects on success or exception on failure */
		try {
			$data = $this->get_json();
		} catch (Exception $e) {
  			print "Exception: ". $e->getMessage(). "\n";
		exit;
		} 

		$hobbies = get_object_vars($data->hobbies);
		$keys = array_keys($hobbies);

		foreach($keys as $key ){
			$this->hobby[] = new hobby($key,$hobbies[$key]);

		}
		foreach($data->people as $person){
			if(!empty($hobbyList)){
				unset($hobbyList);
			}
 			$hobbyList = array();
			foreach($person->interests as $interest){
				foreach ($this->hobby as $hobby){
					if (in_array($interest,$hobby->interests)){
						
								$hobbyList[] = $hobby->name;
					}
									
				
				}
			}
				if(ALLOW_MULTIPLE_HOBBIES){
					asort($hobbyList); //Sorts the list in order
					$hobby = ucwords(implode(", ",array_unique($hobbyList)));
				} else {	
					$hobbyCounts = array_count_values($hobbyList); //get counts of how many time each hobby matched
					$hobbyKeys = array_keys($hobbyCounts);
					asort($hobbyKeys); //sorts matched hobbies in order
					$count=0;
					for($i=0; $i<sizeof($hobbyCounts);$i++){
						if ($hobbyCounts[$hobbyKeys[$i]] > $count){
					      		$count = $hobbyCounts[$hobbyKeys[$i]];			
							$hobby = $hobbyKeys[$i];
						}

					}
					if($count==1 && ALLOW_MULTIPLE_HOBBIES_ON_TIE){
						$hobby= ucwords(implode(", " , $hobbyKeys)); // implode will return a string of hobbies and ucwords will uppercase the first letter of each if needed
					}	
				}
				
				$person->hobby = $hobby;
				//$person->hobbies = $hobbyList;
				asort($person->interests); //sorts the interests list in order
				$this->persons[] = new person($person->name,$person->height,$person->dob,ucwords(implode(", ",$person->interests)),$hobby,$hobbyList); // implode will return a string of interests and ucwords will uppercase the first letter of each if needed
		}	
	}
	public function outputPersonsToJson(){
		/* outputs json string of object data */
		return json_encode($this->persons);
	}
	public function outputPersonsTableData(){
		/* creates simplified json representing object for HTML table creation */
		foreach ($this->persons as $person){
			$row['Name'] 	  = 	$person->get_name();
			$row['Height'] 	  = 	$person->get_height();
			$row['Dob'] 	  = 	$person->get_dob();
			$row['Hobby'] 	  = 	$person->get_hobby();
			$row['Interests'] = 	$person->get_interests();

			
			$tabledata[] = $row; 
			
				

		}
		return json_encode($tabledata);
	}
}

	$ph = new personalHobby();
	$ph->load_json();
	$json = $ph->outputPersonsTableData();
?>
<!DOCTYPE html>
<html lang="en">
<html>
	<head>
<style>
th {
	cursor: pointer;
}
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  padding: 10px;
}
</style>
<script>
	function orderBy(obj,prop) {
		/* This prevents the need to have different functions do the sort for each column. The Object passed in is sorted according to the property passed in */

		if (orderByProp == prop){
			if (orderByDirection == 'Asc'){
				orderByDirection ="Desc";
			} else {
				orderByDirection ="Asc";
			}	
		} else {	
			orderByProp = prop;
			orderByDirection = "Asc";
		}	
 		let sorted = obj.sort(function(a, b){
			/* either an Ascending or Descending sort is done */
			if (orderByDirection == 'Asc') {
				if(eval("a." + prop + " < b." + prop)) { return -1; }
                        	if(eval("a." + prop + " > b." + prop)) { return 1; }
                        	return 0;
			} else {
				if(eval("a." + prop + " > b." + prop)) { return -1; }
                        	if(eval("a." + prop + " < b." + prop)) { return 1; }
                        	return 0;
			}	
                })

                return sorted;
		
	}	

	function makeTable(jsonobj){
	/* table generated from json data */
		var table="<table width='75%' border='0'>";

		table += "<tr>";
		/* property names are pulled dynamically to name columns */
		keys = Object.getOwnPropertyNames(jsonobj[0]);
		for (const key of keys){ 
			table += "<th title=\"Sort By " +key +"\"  onclick='orderBy(jsonobj,\""+key+"\");makeTable(jsonobj);'>" + key + "</th>";
		}
		table += "</tr>";

		for (const obj of jsonobj){ 
			table += "<tr>";
			keys2 = Object.getOwnPropertyNames(obj);
			for (const key2 of keys2){ 
				table += "<td>" + eval('obj.' + key2) + "</td>";
			}
			table += "</tr>";
		}	
		table += "</table> ";
		document.getElementById('ph_table').innerHTML = table;
	}	
</script>
	</head>
	<body>
	<div id="ph_table"> </div>

	</body>
<script>

	json =" <?php print addslashes($json); ?> ";
	jsonobj = JSON.parse(json);
	orderByProp = "";
	orderByDirection = "Asc";
	jsonobj = orderBy(jsonobj,'Name');
	makeTable(jsonobj);
</script>
</html>
