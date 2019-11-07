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

 $ids = json_decode($_POST["ids"]);
 $ids = array_slice($ids, 1, count($ids)); // remove first id (it refers to the .edit button)
 
 //initialize the array ($label => $val) with the first id
 $get_val = array();
 $edit_array = array();
 $connect = mysqli_connect("localhost", "root", "", "test");   
 $query = "SELECT * FROM  $table_name WHERE $image = '".$ids[0]."' ";  
 $result = mysqli_query($connect, $query);  
 $row = mysqli_fetch_array($result); 
 foreach($editables_columns as $label => $values){
     $get_val[] = $row[$label];
     $edit_array[] = $label;
 }
 $output = array_combine($edit_array, $get_val);

 // now check if there are more ids and keep the value if equal or empty if not
 if(count($ids) > 1){
      for($i=1; $i<count($ids); $i++){
          $query = "SELECT * FROM  $table_name WHERE $image = '".$ids[$i]."' ";  
          $result = mysqli_query($connect, $query);  
          $row = mysqli_fetch_array($result);

          foreach($editables_columns as $label => $values){
               if($output[$label] != $row[$label]){
                    $output[$label] = "_";
               }
          }
      }
 } 

 echo json_encode($output)
 ?>