<?php

//create_db.php

session_start();

if(!empty($_FILES['csv_file']['name']))
{
 $file_data = fopen($_FILES['csv_file']['tmp_name'], 'r');
 $column = fgetcsv($file_data);
 while($row = fgetcsv($file_data))
 {
	 for($count = 0 ; $count < count($column) ; $count++)
	{
		$row_data[] = array(
			$column[$count] => $row[$count],
		);
	};
};
 

foreach ($row_data as $d){
	foreach ($d as $key => $value){
		$k[]=$key;
		$v[]="'".$value."'";
	};
};

$file = $_FILES['csv_file']['name'];
$file_server = $_FILES['csv_file']['tmp_name'];
$t = explode('.', $file);

$table = $t[0];
$conn = mysqli_connect("localhost","root","","test") or die ('Error connecting database: '.mysqli_error($conn));
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

$output = array(
  'file' => $table,
  'columns'  => $column
);

//count total number of rows
$query = "SELECT COUNT($column[0]) AS num_rows FROM $table";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_object( $result );
$total = $row->num_rows;

$_SESSION['file'] = $table;
$_SESSION['columns'] = $column;
$_SESSION['total_rows'] = $total;

echo json_encode($output);

}

?>