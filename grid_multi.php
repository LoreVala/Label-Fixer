<?php  

// get session variables
 session_start();
 if(isset($_SESSION['table'])) $table_name=$_SESSION['table'];
 if(isset($_SESSION['columns'])) $col_id= $_SESSION['columns'];
 if(isset($_SESSION['output_path'])) $csv_path=$_SESSION['output_path'];
 if(isset($_SESSION['sql_path'])) $sql_path= $_SESSION['sql_path'];
 if(isset($_SESSION['editables_columns'])) $editables_columns=$_SESSION['editables_columns'];
 if(isset($_SESSION['filterable_columns'])) $filterable_columns= $_SESSION['filterable_columns'];

 $connect = mysqli_connect("localhost", "root", "", "test"); // sql connection to label_fixer database

 // get varibles from url if they are set (for filtering purpose)
 $get_array = array();
 $filter_array = array();
 foreach($filterable_columns as $filter => $values){
     $filter_array[] = $filter;
    if (isset($_GET[$filter])){
        $get_array[] = $_GET[$filter];
       }else{ $get_array[] = "any";}
 }
 $get_array = array_combine($filter_array, $get_array);  //join arrays (key->value)

 // construct reference url from the variable and construct query to count data from filters
 $query_selection = "";
 $url_from_get_array = "grid_multi.php?";
 foreach($get_array as $key => $value){
     $url_from_get_array .= "$key=$value&";
     if($value != "any"){
         if(is_numeric($value)){
               $query_selection .= "$key = $value AND ";
         } else{
               $query_selection .= "$key = '".$value."' AND ";
         }
     }
 }
 $url_from_get_array .= "page=";
 $query_selection = substr($query_selection, 0, -4);
 $query = "SELECT COUNT(*) FROM $table_name ";

 if($query_selection != ""){
     $query .= "WHERE ";
 }
 $query .= $query_selection;
 $result = mysqli_query($connect, $query);
 $row = mysqli_fetch_row($result);
 $total = $row[0];

 # Pagination variables
 $limit = 120;
 if (isset($_GET['page'])){
      $page = $_GET['page'];
     }
 else{ $page = 1;}
 $start = ($page - 1) * $limit;
 $pages = ceil($total/$limit);
 $Previous = $page - 1;
 $Next = $page + 1;
 $first = 1;
 $last = $pages;
 $link = 2;
 
 // retrieve data for the page 
 $rows = array();
 $query = "SELECT * FROM $table_name "; //WHERE img_type = $type LIMIT $start, $limit"; 
 if($query_selection != ""){
     $query .= "WHERE ";
 }
 $query .= $query_selection;
 $query .= "LIMIT $start, $limit";
 $result = mysqli_query($connect, $query);
 while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
     $rows[] = $row;
 }

 // Select unique combinations of labels
 $options = "";
 $new_url_from_get_array = $url_from_get_array . $first;
 $log = array();
 foreach($filterable_columns as $label => $label_values){
    $query = "SELECT DISTINCT($label) FROM $table_name";
    $result = mysqli_query($connect, $query);
    $uniques = array();
    while($row = mysqli_fetch_row($result)){
       $uniques[] = preg_replace("/\r/", '', $row[0]); // remove any special character
    }
    array_unshift($uniques,"any");
   
    $options .= "<label>" . $label . "</label>";
    $options .= "<select onchange=" . "window.location=this.value" . ">";
    
    $track_ref = array();
    $track_val = array();
    foreach($get_array as $key => $value){
        if($key == $label){
            $others = array_diff($uniques, array($value));
            $ref = str_replace("$key=$value","$key=$value","$new_url_from_get_array");
            if($value == "any"){
               $options .= "<option value=" . $ref . ">" .$value . "</option>";
            } else {
               $options .= "<option value=" . $ref . ">" .$label_values[$value] . "</option>";
            }
            array_push($track_ref, $ref);
            array_push($track_val, $value);

            foreach($others as $unique){
                $ref = str_replace("$key=$value","$key=$unique","$new_url_from_get_array");
                if($unique == "any"){
                    $options .= "<option value=" . $ref . ">" .$unique . "</option>";
                } else {
                    $options .= "<option value=" . $ref . ">" .$label_values[$unique] . "</option>";
                }
                array_push($track_ref, $ref);
                array_push($track_val, $unique);
            }
        }
    }
    $options .= "</select>";
 }
 
 // images 
 $images = array();
 foreach($rows as $row){
      $images[] = $row[$col_id[0]];
 }

 //dynamic form control for labels visualization and editing
 $edit_form = "";
 foreach($editables_columns as $label => $label_values){
      $edit_form .= "<div>";
      $edit_form .= "<label>" . $label . "</label>";
      $edit_form .= "<select name=".$label." id="."sel_".$label.">";
      $edit_form .= "<option value="."_".">unchanged</option>";
      foreach($label_values as $key => $value){
          $edit_form .= "<option value= " . $key . ">".$value."</option>";
      }
      $edit_form .= "</select>";
      $edit_form .= "</div>";
 }
?>  

<!DOCTYPE html>
<html>
 <head>
  <title>grid</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>  
     <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />  
     <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>  
  <style>
  .imgActive{
     border: solid 3px red;
  }

  .box
  {
   max-width:600px;
   width:100%;
   margin: 0 auto;
  }
  </style>
 </head>
<body onload="scrollToBottom()">
<div class="container">
   <br />
   <h3 style="text-align:center">Label Fixer</h3>

   <?php $single_url = str_replace("grid_multi.php","grid.php","$new_url_from_get_array"); ?>

   <?php $download = "export_" . $table_name . "_" . date('Y-m-d-H-i') . ".csv";?>
   <a href="exportData.php" download= <?php echo $download; ?> class="btn btn-success pull-right">Save to csv</a>

   <a href="index.php" class="btn btn-danger pull-right">Home</a>

   <a href= <?php echo $single_url; ?> class="btn btn-info pull-right">Single</a>
   
   <br />
   <div class="row">
     <div class="col-md-10">
          <nav aria-label="Page navigation">
               <ul class="pagination">

                    <?php if($page == $first){ ?>
                         <li class="page-item disabled">
                    <?php } else { ?>
                         <li class="page-item">
                    <?php } ?>                    
                    <a href="<?= $url_from_get_array . $Previous; ?>" aria-label="Previous">
                         <span aria-hidden="true">&laquo; Previous</span>
                    </a>
                    </li>

                    <?php if( ($page-$link) <= $first+1 ){ ?>
                         <?php for($i = $first; $i <= $page; $i++){?>                         
                              <?php if($i == $page){?>
                                   <li class="page-item active"><a href="<?= $url_from_get_array . $i; ?>"><?= $i; ?></a></li>
                              <?php } else {?>
                                   <li class="page-item"><a href="<?= $url_from_get_array . $i; ?>"><?= $i; ?></a></li>
                              <?php }?>                    
                         <?php } ?>
                    <?php } else{ ?>
                         <li class="page-item"><a href="<?= $url_from_get_array . $first; ?>"><?= $first; ?></a></li>
                         <?php for($i = ($page-$link); $i <= $page; $i++){?>                         
                              <?php if($i == $page){?>
                                   <li class="page-item active"><a href="<?= $url_from_get_array . $i; ?>"><?= $i; ?></a></li>
                              <?php } else {?>
                                   <li class="page-item"><a href="<?= $url_from_get_array . $i; ?>"><?= $i; ?></a></li>
                              <?php }?>                    
                         <?php } ?>
                    <?php } ?>
                    
                    <?php if( ($page+$link) >= $last-1 ){ ?>
                         <?php for($i = $page+1; $i <= $last; $i++){?>                         
                              <?php if($i == $page){?>
                                   <li class="page-item active"><a href="<?= $url_from_get_array . $i; ?>"><?= $i; ?></a></li>
                              <?php } else {?>
                                   <li class="page-item"><a href="<?= $url_from_get_array . $i; ?>"><?= $i; ?></a></li>
                              <?php }?>                    
                         <?php } ?>
                    <?php } else{ ?>
                         
                         <?php for($i = $page+1; $i <= ($page+$link); $i++){?>                         
                              <?php if($i == $page){?>
                                   <li class="page-item active"><a href="<?= $url_from_get_array . $i; ?>"><?= $i; ?></a></li>
                              <?php } else {?>
                                   <li class="page-item"><a href="<?= $url_from_get_array . $i; ?>"><?= $i; ?></a></li>
                              <?php }?>                    
                         <?php } ?>
                         <li class="page-item"><a href="<?= $url_from_get_array . $last; ?>"><?= $last; ?></a></li>
                    <?php } ?>
                    
                    <?php if($page == $last){ ?>
                         <li class="page-item disabled">
                    <?php } else { ?>
                         <li class="page-item">
                    <?php } ?>    
                         <a href="<?= $url_from_get_array . $Next; ?>" aria-label="Next">
                              <span aria-hidden="true">Next &raquo;</span>
                         </a>
                    </li>

                    <?php echo $options; ?>
                    
               </ul>
          </nav>
     </div>
   </div>
   

 <table id="image_grid" class="table table-bordered table-sm">
 <tbody>
   <tr>
   
   <?php $line_cont = 1;
   $line = 15;
   $id_cont = 0;?>
   <?php for($id = 0; $id < count($images); $id++){
        $id_cont++;?>
     
      <td><input type="image" src="<?php echo $images[$id];?>" id="<?php echo $images[$id];?>"  onclick="setFormImage(this.id)" alt=''/></td>
  
      <?php if((($id_cont / $line) - $line_cont) == 0){?>
         </tr>
         <?php $line_cont = $line_cont + 1;
         if($id < count($images)){?>
           <tr>
         <?php
         }
      }?>
   
   <?php  
   }  
   ?>
   
   </tbody>
 
 </table>  
 </div>
 <div class="container">
  <div class="row">
    <div class="col text-center">
    <form name="imageSubmit" id="imageSubmit" >
    <input type="button" value="Edit Selected" data-target="#add_data_Modal" class="edit_data"/>
     </form>
    </div>
  </div>
  
</div>
 
</body>
</html>


<div id="add_data_Modal" class="modal fade">  
      <div class="modal-dialog">  
           <div class="modal-content">  
                <div class="modal-header">  
                     <button type="button" class="close" data-dismiss="modal">&times;</button>  
                     <h4 class="modal-title">Edit Labels</h4>  
                </div>  
                <div class="modal-body">  
                     <form method="post" id="insert_form">
                         <div class="container-fluid">
				     <div class="row">
					<div class="col-md-6 ml-auto">
                          
                              <?php echo $edit_form; ?>

                          </div>
				      

                          </div> 
                          <div class="text-right">
                         <input type="submit" name="insert" id="insert" value="Insert" class="btn btn-success" /> 
                         </div>
                          <input type="hidden" name="i_m_g_i_d" id="i_m_g_i_d" />  
                            
                     </form>  
                </div>  
                <div class="modal-footer"> 
                
                <div class="text-right">
                     <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>  
                     </div>
                </div>  
           </div>  
      </div>  
 </div>
 

 <script>  
scrollingElement = (document.scrollingElement || document.body)
function scrollToBottom () {
   scrollingElement.scrollTop = scrollingElement.scrollHeight;
}
function setFormImage(id) {
     
         
    if (id != '' && !document.getElementById('input_'+id)) {
        var img = document.createElement('input');
        img.type = 'hidden';
        img.id = 'input_'+id;
        img.name = 'images[]';
        img.value = id;
          
        document.imageSubmit.appendChild(img);
    }

    else if (id != '' && document.getElementById('input_'+id)) {
          
        document.imageSubmit.removeChild(document.getElementById('input_'+id));
    }
}

$('input[type="image"]').on("click",
            function(){
                $(this).toggleClass("imgActive");
            });

 $(document).ready(function(){ 
   $('#add').click(function(){  
           $('#insert').val("Insert");  
           $('#insert_form')[0].reset();  
      });


   $(document).on('click', '.edit_data', function(event){ 

           event.preventDefault();  
           var img_id = [];       
           $('#imageSubmit').find(':input').each(function () {
                img_id.push(this.value);
           });
           if(img_id.length == 1)  
           {  
                alert("You must select at least one image!"); 
                $('#add_data_Modal').modal('hide'); 
           }else{   
               var ids = JSON.stringify(img_id);        
               $.ajax({  
                    url:"fetch_multi.php",  
                    method:"POST",  
                    data:{ids:ids},  
                    dataType:"json",  
                    success:function(data){                     
                         jQuery.each(data, function(k, v) {
                              $('#sel_'+k+' option[value='+v+']').prop('selected', true);                         
                         });
                         $('#i_m_g_i_d').val(ids);  
                         $('#insert').val("Update");  
                         $('#add_data_Modal').modal('show');
                    }  
               });
           }  
      });
      
      $('#insert_form').on("submit", function(event){  
           event.preventDefault();  
           $.ajax({  
               url:"insert_multi.php",  
               method:"POST",  
               data:$('#insert_form').serialize(),  
               beforeSend:function(){  
                    $('#insert').val("Inserting");  
               },  
               success:function(data){  
                    $('#insert_form')[0].reset();  
                    $('#add_data_Modal').modal('hide');  
                    location.reload();
 
               }  
          });    
      });

     
    
     });  
 </script>