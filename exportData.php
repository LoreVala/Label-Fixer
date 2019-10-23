<?php
//include database configuration file
//include 'dbConfig.php';

session_start();
if(isset($_SESSION['file'])) $table_name=$_SESSION['file'];
if(isset($_SESSION['columns'])) $col_id= $_SESSION['columns'];

$connect = mysqli_connect("localhost", "root", "", "test"); 
$allData = "";
$query = "SELECT * FROM $table_name";
    
//output each row of the data, format line as csv and write to file pointer
$result = mysqli_query($connect, $query);
while($data = mysqli_fetch_assoc($result)) {
    for($i = 0; $i < count($col_id); $i++ ){
        $allData .= $data[$col_id[$i]];
        if($i == count($col_id)-1){
            $allData .= "\n";
        } else {
            $allData .= ',';
        }
    }
}

$response = "";
for($i = 0; $i < count($col_id); $i++ ){
    $response .= $col_id[$i];
    if($i == count($col_id)-1){
        $response .= "\n";
    } else {
        $response .= ',';
    }
    
}

$response .= $allData;

    
echo $response;


?>