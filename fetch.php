<?php  
 //fetch.php  

 session_start();
 if(isset($_SESSION['file'])) $table_name=$_SESSION['file'];
 if(isset($_SESSION['columns'])) $col_id= $_SESSION['columns'];

 $image = $col_id[0];
 $labels = array_slice($col_id, 1, count($col_id));

 $id = $_POST["img_id"];
 
 $connect = mysqli_connect("localhost", "root", "", "test");   
 $query = "SELECT * FROM  $table_name WHERE $image = '".$id."' ";  
 $result = mysqli_query($connect, $query);  
 $row = mysqli_fetch_array($result); 
 
 $get_val = array();

 foreach($labels as $label){
    $get_val[] = $row[$label];
 }
 $output = array_combine($labels, $get_val);

 echo json_encode($output)
 ?>