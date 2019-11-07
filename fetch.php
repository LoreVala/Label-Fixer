<?php  
 //fetch.php  

 session_start();
 if(isset($_SESSION['table'])) $table_name=$_SESSION['table'];
 if(isset($_SESSION['columns'])) $col_id= $_SESSION['columns'];
 if(isset($_SESSION['output_path'])) $csv_path=$_SESSION['output_path'];
 if(isset($_SESSION['sql_path'])) $sql_path= $_SESSION['sql_path'];
 if(isset($_SESSION['editables_columns'])) $editables_columns=$_SESSION['editables_columns'];
 if(isset($_SESSION['filterable_columns'])) $filterable_columns= $_SESSION['filterable_columns'];

 $image = $col_id[0];

 $id = $_POST["img_id"];
 
 $connect = mysqli_connect("localhost", "root", "", "label_fixer");   
 $query = "SELECT * FROM  $table_name WHERE $image = '".$id."' ";  
 $result = mysqli_query($connect, $query);  
 $row = mysqli_fetch_array($result); 
 
 $get_val = array();
 $edit_array = array();
 foreach($editables_columns as $label => $values){
    $get_val[] = $row[$label];
    $edit_array[] = $label;
 }
 $output = array_combine($edit_array, $get_val);

 echo json_encode($output)
 ?>