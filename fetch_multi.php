<?php  
 //fetch.php  
 session_start();
 if(isset($_SESSION['file'])) $table_name=$_SESSION['file'];
 if(isset($_SESSION['columns'])) $col_id= $_SESSION['columns'];

 $image = $col_id[0];
 $labels = array_slice($col_id, 1, count($col_id));

 $ids = json_decode($_POST["ids"]);
 $ids = array_slice($ids, 1, count($ids)); // remove first id
 
 //initialize the array ($label => $val) with the first id
 $get_val = array();
 $connect = mysqli_connect("localhost", "root", "", "test");   
 $query = "SELECT * FROM  $table_name WHERE $image = '".$ids[0]."' ";  
 $result = mysqli_query($connect, $query);  
 $row = mysqli_fetch_array($result); 
 foreach($labels as $label){
     $get_val[] = $row[$label];
 }

 // now check if there are more ids and keep the value if equal or empty if not
 if(count($ids) > 1){
      for($i=1; $i<count($ids); $i++){
          $query = "SELECT * FROM  $table_name WHERE $image = '".$ids[$i]."' ";  
          $result = mysqli_query($connect, $query);  
          $row = mysqli_fetch_array($result);

          for($n=0; $n<count($labels); $n++){
               if($get_val[$n] != $row[$labels[$n]]){
                    $get_val[$n] = "";
               }
          }
      }
 }

 $output = array_combine($labels, $get_val);

 echo json_encode($output)
 ?>