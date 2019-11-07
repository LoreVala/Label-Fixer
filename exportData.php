<?php
//include database configuration file
//include 'dbConfig.php';

session_start();
 if(isset($_SESSION['table'])) $table_name=$_SESSION['table'];
 if(isset($_SESSION['columns'])) $col_id= $_SESSION['columns'];
 if(isset($_SESSION['csv_path'])) $csv_path=$_SESSION['csv_path'];
 if(isset($_SESSION['sql_path'])) $sql_path= $_SESSION['sql_path'];
 if(isset($_SESSION['editables_columns'])) $editables_columns=$_SESSION['editables_columns'];
 if(isset($_SESSION['filterable_columns'])) $filterable_columns= $_SESSION['filterable_columns'];

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