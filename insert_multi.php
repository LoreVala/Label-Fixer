<?php  
 session_start();
 if(isset($_SESSION['table'])) $table_name=$_SESSION['table'];
 if(isset($_SESSION['columns'])) $col_id= $_SESSION['columns'];
 if(isset($_SESSION['output_path'])) $csv_path=$_SESSION['output_path'];
 if(isset($_SESSION['sql_path'])) $sql_path= $_SESSION['sql_path'];
 if(isset($_SESSION['file'])) $file= $_SESSION['file'];
 if(isset($_SESSION['editables_columns'])) $editables_columns=$_SESSION['editables_columns'];
 if(isset($_SESSION['filterable_columns'])) $filterable_columns= $_SESSION['filterable_columns'];
 
 $image = $col_id[0];

 $connect = mysqli_connect("localhost", "root", "", "label_fixer");  
 if(!empty($_POST))  
 {  
      $post_array = array();
      $edit_array = array();
      foreach($editables_columns as $label => $values){
        $edit_array[] = $label;
        $post_array[] = mysqli_real_escape_string($connect, $_POST[$label]);
      }  
      $post_array = array_combine($edit_array, $post_array);  //join arrays (key->value)

      $ids = json_decode($_POST["i_m_g_i_d"]); 
      $ids = array_slice($ids, 1, count($ids)); // remove first id 


      $query = "UPDATE $table_name SET ";

      foreach($post_array as $key => $value){
        if($value != "_"){
          if(is_numeric($value)){
            $query .= "$key = $value, ";
          } else {
            $query .= "$key = '".$value."', ";
          }
        }
      }
      $query = substr($query, 0, -2);
      $query .= " WHERE ";
      foreach($ids as $img_id){
        $query .= "$image = '".$img_id."' OR ";
      }
      $query = substr($query, 0, -4);
      
  
      $result = mysqli_query($connect, $query);

      // save updated csv to temp file
    
      $temp_file = $table_name . "_temp.csv";
      $query = "SELECT ";
      foreach($col_id as $col){
        $query .= "'".$col."', ";
      }
      $query = substr($query, 0, -2);
      $query .= " UNION ALL SELECT * FROM $table_name INTO OUTFILE '".$temp_file."' ";
      $query .= "FIELDS TERMINATED BY '".","."' LINES TERMINATED BY '"."\n"."';";
      $result = mysqli_query($connect, $query);

      // delete previous csv file and rename temp 
      $comm = "rm -f $sql_path"."$file";
      $comm .= " && ";
      $comm .= "mv -f $sql_path"."$temp_file $sql_path"."$file";
      exec($comm);
      $comm = "cp -f $sql_path"."$file $csv_path"."$file";
      exec($comm);
 }  
 ?>
