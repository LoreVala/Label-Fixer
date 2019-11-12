<?php  
 //fetch.php  

 session_start();
 if(isset($_SESSION['table'])) $table=$_SESSION['table'];
 if(isset($_SESSION['editables_columns'])) $editables_columns=$_SESSION['editables_columns'];
 if(isset($_SESSION['image_column'])) $image_column= $_SESSION['image_column'];

 $id = $_POST["img_id"];
 
 $connect = mysqli_connect("localhost", "root", "", "test");   
 $query = "SELECT * FROM  $table WHERE $image_column = '".$id."' ";  
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