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
	if($index == "path_to_csv_file"){
		$path_to_csv_file =  $value;
	}
	if($index == "sql_path"){
		$sql_path =  $value;
	}
	if($index == "columns"){
		$columns =  $value;
    }
    if($index == "image_column"){
		$image_column =  $value;
    }
    if($index == "rel_path_to_thumb"){
		$rel_path_to_thumb =  $value;
    }
    if($index == "editables_columns"){
		$editables_columns =  $value;
	}
	if($index == "filterable_columns"){
		$filterable_columns =  $value;
	}
}

$table = basename($path_to_csv_file, ".csv");
$file = basename($path_to_csv_file);
	
// create a copy of the csv file in the mysql folder to ensure import
exec("cp -f $path_to_csv_file $sql_path"."$file", $o, $return);
if($return){
	$f2 = str_replace("/","\\","$path_to_csv_file");
	$sql_2 = str_replace("/","\\","$sql_path");
	exec("copy /y $f2 $sql_2"."$file");
};


// create dataqbase 
$conn = mysqli_connect("localhost","root","","test");
$query = "TRUNCATE TABLE $table";
$result = mysqli_query($conn, $query);

if(empty($result)){
	$query = "CREATE TABLE $table ( ";
								
	for ($i=0; $i<count($columns); $i++)
		{
			$query .= "$columns[$i]" . " " . "VARCHAR(255) NOT NULL";
			$query .= ", ";
			if($i == count($columns)-1){
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
$_SESSION['columns'] = $columns;
$_SESSION['path_to_csv_file'] = $path_to_csv_file;
$_SESSION['file'] = $file;
$_SESSION['sql_path'] = $sql_path;
$_SESSION['editables_columns'] = $editables_columns;
$_SESSION['filterable_columns'] = $filterable_columns;
$_SESSION['image_column'] = $image_column;
$_SESSION['rel_path_to_thumb'] = $rel_path_to_thumb;

$output = array(
	'file' => $table
  );

echo json_encode($output);

?>