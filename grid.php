<?php  

// get session variables
session_start();
if(isset($_SESSION['table'])) $table=$_SESSION['table'];
if(isset($_SESSION['columns'])) $columns= $_SESSION['columns'];
if(isset($_SESSION['path_to_csv_file'])) $csv_path=$_SESSION['path_to_csv_file'];
if(isset($_SESSION['file'])) $file=$_SESSION['file'];
if(isset($_SESSION['sql_path'])) $sql_path= $_SESSION['sql_path'];
if(isset($_SESSION['editables_columns'])) $editables_columns=$_SESSION['editables_columns'];
if(isset($_SESSION['filterable_columns'])) $filterable_columns= $_SESSION['filterable_columns'];
if(isset($_SESSION['image_column'])) $image_column= $_SESSION['image_column'];
if(isset($_SESSION['rel_path_to_thumb'])) $rel_path_to_thumb=$_SESSION['rel_path_to_thumb'];

 $connect = mysqli_connect("localhost", "root", "", "test"); 

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

 # number of rows of the image grid
 if (isset($_GET['grid_r'])){
      $grid_r = $_GET['grid_r'];
     }
 else{ $grid_r = 8;}
 # number of columns of the image grid
 if (isset($_GET['grid_c'])){
      $grid_c = $_GET['grid_c'];
    }
 else{ $grid_c = 15;}
 # page
 if (isset($_GET['page'])){
      $page = $_GET['page'];
     }
 else{ $page = 1;}

 // construct reference url from the variable and construct query to retrieve only data from filters
 $query_selection = "";
 $url_from_get_array = "grid.php?";
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
 $final_url = $url_from_get_array . "grid_r=";
 $final_url .= $grid_r;
 $final_url .= "&grid_c=";
 $final_url .= $grid_c;
 $final_url .= "&page=";

 $query_selection = substr($query_selection, 0, -4);
 $query = "SELECT COUNT(*) FROM $table ";

 if($query_selection != ""){
     $query .= "WHERE ";
 }
 $query .= $query_selection;
 $result = mysqli_query($connect, $query);
 $row = mysqli_fetch_row($result);
 $total = $row[0];
 $total_info = "<p align="."left".">".$total." records found</p>";

 # Pagination variables
 $limit = $grid_r * $grid_c;
 $start = ($page - 1) * $limit;
 $pages = ceil($total/$limit);
 $Previous = $page - 1;
 $Next = $page + 1;
 $first = 1;
 $last = $pages;
 $link = 2;
 

 $rows = array();
 $query = "SELECT * FROM $table "; //WHERE img_type = $type LIMIT $start, $limit"; 
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
 $new_url_from_get_array = $final_url . $first;
 $url_multi = $final_url . $page;
 $log = array();
 foreach($filterable_columns as $label => $label_values){
    $query = "SELECT DISTINCT($label) FROM $table";
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
     $basename = basename($row[$image_column], ".png");
     $images[] =  join(DIRECTORY_SEPARATOR, array($rel_path_to_thumb, $basename.".png"));
 }

 //dynamic form control for labels visualization and editing
 $edit_form = "";
 foreach($editables_columns as $label => $label_values){
      $edit_form .= "<div>";
      $edit_form .= "<label>" . $label . "</label>";
      $edit_form .= "<select name=".$label." id="."sel_".$label.">";
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
  .box
  {
   max-width:600px;
   width:100%;
   margin: 0 auto;
  }
  .flex-box {
  display:flex;
  justify-content:space-between;
  } 
  .flex-box > div {
     display: flex;
     justify-content: center;
}
  </style>
 </head>
<body onload="scrollToBottom()">
<div class="container">
   <br />
   <h3 style="text-align:center">Label Fixer</h3>

   <?php $download = "export_" . $table . "_" . date('Y-m-d-H-i') . ".csv";?>
   <a href="exportData.php" download= <?php echo $download; ?> class="btn btn-success pull-right">Save to csv</a>

   <a href="index.php" class="btn btn-danger pull-right">Home</a>

   <?php $multi_url = str_replace("grid.php","grid_multi.php","$url_multi"); ?>
   <a href= <?php echo $multi_url; ?> class="btn btn-info pull-right">Multi</a>
                    
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
                    <a href="<?= $final_url . $Previous; ?>" aria-label="Previous">
                         <span aria-hidden="true">&laquo; Previous</span>
                    </a>
                    </li>

                    <?php if( ($page-$link) <= $first+1 ){ ?>
                         <?php for($i = $first; $i <= $page; $i++){?>                         
                              <?php if($i == $page){?>
                                   <li class="page-item active"><a href="<?= $final_url . $i; ?>"><?= $i; ?></a></li>
                              <?php } else {?>
                                   <li class="page-item"><a href="<?= $final_url . $i; ?>"><?= $i; ?></a></li>
                              <?php }?>                    
                         <?php } ?>
                    <?php } else{ ?>
                         <li class="page-item"><a href="<?= $final_url . $first; ?>"><?= $first; ?></a></li>
                         <?php for($i = ($page-$link); $i <= $page; $i++){?>                         
                              <?php if($i == $page){?>
                                   <li class="page-item active"><a href="<?= $final_url . $i; ?>"><?= $i; ?></a></li>
                              <?php } else {?>
                                   <li class="page-item"><a href="<?= $final_url . $i; ?>"><?= $i; ?></a></li>
                              <?php }?>                    
                         <?php } ?>
                    <?php } ?>
                    
                    <?php if( ($page+$link) >= $last-1 ){ ?>
                         <?php for($i = $page+1; $i <= $last; $i++){?>                         
                              <?php if($i == $page){?>
                                   <li class="page-item active"><a href="<?= $final_url . $i; ?>"><?= $i; ?></a></li>
                              <?php } else {?>
                                   <li class="page-item"><a href="<?= $final_url . $i; ?>"><?= $i; ?></a></li>
                              <?php }?>                    
                         <?php } ?>
                    <?php } else{ ?>
                         
                         <?php for($i = $page+1; $i <= ($page+$link); $i++){?>                         
                              <?php if($i == $page){?>
                                   <li class="page-item active"><a href="<?= $final_url . $i; ?>"><?= $i; ?></a></li>
                              <?php } else {?>
                                   <li class="page-item"><a href="<?= $final_url . $i; ?>"><?= $i; ?></a></li>
                              <?php }?>                    
                         <?php } ?>
                         <li class="page-item"><a href="<?= $final_url . $last; ?>"><?= $last; ?></a></li>
                    <?php } ?>
                    
                    <?php if($page == $last){ ?>
                         <li class="page-item disabled">
                    <?php } else { ?>
                         <li class="page-item">
                    <?php } ?>    
                         <a href="<?= $final_url . $Next; ?>" aria-label="Next">
                              <span aria-hidden="true">Next &raquo;</span>
                         </a>
                    </li>
                    <input id="pn" type="number" min="<?php echo $first?>" max="<?php echo $last?>" 
                         placeholder="<?php echo $first."/".$last; ?>" required> 
                    <button onclick="go2Page();">Go</button>

                    <?php echo $options; ?>
                    
               </ul>
          </nav>
     </div>
   </div>
   

 <table id="image_grid" class="table table-bordered table-sm">
 <tbody>
   <tr>
   
   <?php $line_cont = 1;
   $line = $grid_c;
   $id_cont = 0;?>
   <?php for($id = 0; $id < count($images); $id++){
        $id_cont++;?>
      
      <td><input type="image" src="<?php echo $images[$id];?>" id="<?php echo basename($images[$id], ".png");?>" data-toggle="modal" data-target="#add_data_Modal" class="edit_data" onclick="AjaxResponse()" alt=''/></td>

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
 <div class="flex-box">
 <div>
 <?php echo $total_info; ?>
 </div>

 <div>
 <p>Grid: </p>
 <input id="grid_r" type="number" min="1" max="100" 
          placeholder="<?php echo $grid_r; ?>" required> 
 <p> X </p>
 <input id="grid_c" type="number" min="1" max="100" 
          placeholder="<?php echo $grid_c; ?>" required>  
 <button onclick="go2Page();">Go</button>
 </div>

 <div>
 <form name="imageSubmit" id="imageSubmit" >
 <input type="button" value="Edit Selected" data-target="#add_data_Modal" class="edit_data" style="float: right;"/>
 </form>
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
					<div class="col-md-4 ml-auto">
					<img class="img-responsive" src="" width="100%" height="auto" alt=""/>
					</div>
				     
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
function go2Page() 
{ 
    var c = document.getElementById("grid_c").value;
    if (! $.isNumeric(c)){
     var c = document.getElementById("grid_c").placeholder;
    }
    var r = document.getElementById("grid_r").value;
    if (! $.isNumeric(r)){
     var r = document.getElementById("grid_r").placeholder;
    }
    var pn = document.getElementById("pn").value; 
    // Check if pn is between the max and min. 
  pn = ((pn><?php echo $last; ?>)?<?php echo $last; ?>:((pn<1)?1:pn)); 

  var final_url = '<?php echo $url_from_get_array; ?>' + 'grid_r=';
  final_url = final_url + r;
  final_url = final_url + '&grid_c=';
  final_url = final_url + c;
  final_url = final_url + '&page=';
  window.location.href = final_url + pn; 
} 
function scrollToBottom () {
   scrollingElement.scrollTop = scrollingElement.scrollHeight;
}

 $(document).ready(function(){ 
   $('#add').click(function(){  
           $('#insert').val("Insert");  
           $('#insert_form')[0].reset();  
      });

     $('#add_data_Modal').on('show.bs.modal', function (e) {
            var image = $(e.relatedTarget).attr('src');
            $(".img-responsive").attr("src", image);
        });

   $(document).on('click', '.edit_data', function(){ 
     
           var img_id = $(this).attr("id");           
           
           $.ajax({  
                url:"fetch.php",  
                method:"POST",  
                data:{img_id:img_id},  
                dataType:"json",  
                success:function(data){                                       
                    jQuery.each(data, function(k, v) {
                         $('#sel_'+k+' option[value='+v+']').prop('selected', true);                        
                    });                       
                    $('#i_m_g_i_d').val(img_id);  
                    $('#insert').val("Update");  
                    $('#add_data_Modal').modal('show');  
                }  
           });  
      });
      
      $('#insert_form').on("submit", function(event){  
           event.preventDefault();  
           $.ajax({  
               url:"insert.php",  
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