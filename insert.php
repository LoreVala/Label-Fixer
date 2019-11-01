<?php  
 session_start();
 if(isset($_SESSION['file'])) $table_name=$_SESSION['file'];
 if(isset($_SESSION['columns'])) $col_id= $_SESSION['columns'];
 
 $image = $col_id[0];
 $labels = array_slice($col_id, 1, count($col_id));

 $connect = mysqli_connect("localhost", "root", "", "test");  
 if(!empty($_POST))  
 {  
      $post_array = array();
      foreach($labels as $label){
        $post_array[] = mysqli_real_escape_string($connect, $_POST[$label]);
      }  
      $post_array = array_combine($labels, $post_array);  //join arrays (key->value)

      $img_id = mysqli_real_escape_string($connect, $_POST["img_id"]);  


      $query = "UPDATE $table_name SET ";

      foreach($post_array as $key => $value){
        if($value != ""){
          if(is_numeric($value)){
            $query .= "$key = $value, ";
          } else {
            $query .= "$key = '".$value."', ";
          }
        }
      }
      $query = substr($query, 0, -2);
      $query .= " WHERE $image = '".$img_id."' ";
      
    
    $result = mysqli_query($connect, $query);
 }  
 ?>
 