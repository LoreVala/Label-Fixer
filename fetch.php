<?php  
 //fetch.php  

 $id = $_POST["img_id"];
 $table_name=$_POST['table_name'];

 $connect = mysqli_connect("localhost", "root", "", "test");   
 $query = "SELECT * FROM  $table_name WHERE Image = '".$id."' ";  
 $result = mysqli_query($connect, $query);  
 $row = mysqli_fetch_array($result);  
      //echo json_encode($row);  
 
$output = array(
     'type' => $row['img_type'],
     'rotation' => $row['rotation'],
     'inversion' => $row['inversion'],
     'flip' => $row['lateral_flip']
);

 echo json_encode($output)
 ?>