<?php

//create_db.php

session_start();

// check if file is uploaded and retrieve json data
if(!empty($_FILES['csv_file']['name']))
{
 $string = file_get_contents($_FILES['csv_file']['tmp_name']);
 $json_a = json_decode($string, true);
};
foreach ($json_a as $index => $value) {
	if($index == "csv_file"){
		$file =  $value;
	}
	if($index == "csv_name"){
		$table =  $value;
	}
	if($index == "output_path"){
		$output_path =  $value;
	}
	if($index == "sql_path"){
		$sql_path =  $value;
	}
	if($index == "columns"){
		$column =  $value;
	}
    if($index == "editables_columns"){
		$editables_columns =  $value;
	}
	if($index == "filterable_columns"){
		$filterable_columns =  $value;
	}
}
	
// create a copy of the csv file in the mysql folder to ensure import
exec("cp -f $output_path"."$file $sql_path"."$file", $o, $return);
if($return){
	$out_2 = str_replace("/","\\","$output_path");
	$sql_2 = str_replace("/","\\","$sql_path");
	exec("copy /y $out_2"."$file $sql_2"."$file");
}

// create dataqbase 
$conn = mysqli_connect("localhost","root","","label_fixer");
$query = "TRUNCATE TABLE $table";
$result = mysqli_query($conn, $query);

if(empty($result)){
	$query = "CREATE TABLE $table ( ";
								
	for ($i=0; $i<count($column); $i++)
		{
			$query .= "$column[$i]" . " " . "VARCHAR(255) NOT NULL";
			$query .= ", ";
			if($i == count($column)-1){
				$query = rtrim($query, ", ");
			}
		}
		$query .= " ) ";         
	$result = mysqli_query($conn, $query);
};

$query = "LOAD DATA INFILE '".$file."' ";
$query .= "INTO TABLE $table ";
$query .= "FIELDS TERMINATED BY ',' ";
$query .= "LINES TERMINATED BY '\n' ";
$query .= "IGNORE 1 LINES";
$result = mysqli_query($conn, $query);

// send important variables to session to be accessible in ther pages
$_SESSION['table'] = $table;
$_SESSION['columns'] = $column;
$_SESSION['output_path'] = $output_path;
$_SESSION['file'] = $file;
$_SESSION['sql_path'] = $sql_path;
$_SESSION['editables_columns'] = $editables_columns;
$_SESSION['filterable_columns'] = $filterable_columns;

$output = array(
	'file' => $table
  );

echo json_encode($output);

?>