<?php  
 $connect = mysqli_connect("localhost", "root", "", "test");  
 if(!empty($_POST))  
 {  
      $output = '';  
      $message = '';  
      $type = mysqli_real_escape_string($connect, $_POST["type"]);  
      $rotation = mysqli_real_escape_string($connect, $_POST["rotation"]);
      $inversion = mysqli_real_escape_string($connect, $_POST["inversion"]);  
      $flip = mysqli_real_escape_string($connect, $_POST["flip"]);
      $img_id = mysqli_real_escape_string($connect, $_POST["img_id"]);  

    $query = "UPDATE label_fixer SET img_type = $type, rotation = $rotation, inversion = $inversion, lateral_flip = $flip  WHERE Image = '".$img_id."' ";  
    $message = 'Data Updated';  
    
    $result = mysqli_query($connect, $query);
 }  
 ?>
 