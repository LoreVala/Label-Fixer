<?php  
 session_start();
 if(isset($_SESSION['file'])) $table_name=$_SESSION['file'];
 if(isset($_SESSION['columns'])) $col_id= $_SESSION['columns'];
 if(isset($_SESSION['total_rows'])) $total= $_SESSION['total_rows'];

 $connect = mysqli_connect("localhost", "root", "", "test"); 
 
 # Pagination variables
 $limit = 120;
 if (isset($_GET['page'])){
      $page = $_GET['page'];
     }
 else{ $page = 1;}
 $start = ($page - 1) * $limit;
 $query = "SELECT * FROM $table_name LIMIT $start, $limit";  
 $pages = ceil($total/$limit);
 $Previous = $page - 1;
 $Next = $page + 1;
 $first = 1;
 $last = $pages;
 $link = 2;
 
 $rows = array();
 $result = mysqli_query($connect, $query);
 while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
     $rows[] = $row;
 }

 $images = array();
 $labels = array();

 foreach($rows as $row){
	 $images[] = $row[$col_id[0]];
	 for($i = 1; $i < count($col_id); $i++){
		 $labels[] = $row[$col_id[$i]];
	 }
 }

 //mysqli_free_result($result);
 //mysqli_close($connect);
    // look for image buttons and follow examples
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
  </style>
 </head>
<body onload="scrollToBottom()">
<div class="container">
   <br />
   <h3 style="text-align:center">Label Fixer</h3>
   <br />
   <div class="row">
     <div class="col-md-10">
          <nav aria-label="Page navigation">
               <ul class="pagination">
                    <li>
                    <a href="grid.php?page=<?= $Previous; ?>" aria-label="Previous">
                         <span aria-hidden="true">&laquo; Previous</span>
                    </a>
                    </li>

                    <?php if( ($page-$link) <= $first+1 ){ ?>
                         <?php for($i = $first; $i <= $page; $i++){?>                         
                              <?php if($i == $page){?>
                                   <li class="page-item active"><a href="grid.php?page=<?= $i; ?>"><?= $i; ?></a></li>
                              <?php } else {?>
                                   <li class="page-item"><a href="grid.php?page=<?= $i; ?>"><?= $i; ?></a></li>
                              <?php }?>                    
                         <?php } ?>
                    <?php } else{ ?>
                         <li class="page-item"><a href="grid.php?page=<?= $first; ?>"><?= $first; ?></a></li>
                         <?php for($i = ($page-$link); $i <= $page; $i++){?>                         
                              <?php if($i == $page){?>
                                   <li class="page-item active"><a href="grid.php?page=<?= $i; ?>"><?= $i; ?></a></li>
                              <?php } else {?>
                                   <li class="page-item"><a href="grid.php?page=<?= $i; ?>"><?= $i; ?></a></li>
                              <?php }?>                    
                         <?php } ?>
                    <?php } ?>
                    
                    <?php if( ($page+$link) >= $last-1 ){ ?>
                         <?php for($i = $page+1; $i <= $last; $i++){?>                         
                              <?php if($i == $page){?>
                                   <li class="page-item active"><a href="grid.php?page=<?= $i; ?>"><?= $i; ?></a></li>
                              <?php } else {?>
                                   <li class="page-item"><a href="grid.php?page=<?= $i; ?>"><?= $i; ?></a></li>
                              <?php }?>                    
                         <?php } ?>
                    <?php } else{ ?>
                         
                         <?php for($i = $page+1; $i <= ($page+$link); $i++){?>                         
                              <?php if($i == $page){?>
                                   <li class="page-item active"><a href="grid.php?page=<?= $i; ?>"><?= $i; ?></a></li>
                              <?php } else {?>
                                   <li class="page-item"><a href="grid.php?page=<?= $i; ?>"><?= $i; ?></a></li>
                              <?php }?>                    
                         <?php } ?>
                         <li class="page-item"><a href="grid.php?page=<?= $last; ?>"><?= $last; ?></a></li>
                    <?php } ?>
                    
                    <li>
                         <a href="grid.php?page=<?= $Next; ?>" aria-label="Next">
                              <span aria-hidden="true">Next &raquo;</span>
                         </a>
                    </li>
                    
                    <a href="index.php" class="btn btn-danger align-right">Home</a>
                    
                    <?php $download = "export_" . $table_name . "_" . date('Y-m-d-H-i') . ".csv";?>
                    <a href="exportData.php" download= <?php echo $download; ?> class="btn btn-success pull-right">Save to csv</a>
                    
               </ul>
          </nav>
     </div>
   </div>
   

 <table id="image_grid" class="table table-bordered table-sm">
 <input type="hidden" name='table' id='table' value="<?php echo $table_name;?>"/>
 <input type="hidden" name='columns' id='columns' value="<?php echo json_encode($col_id);?>"/>
 <tbody>
   <tr>
   
   <?php $line_cont = 1;
   $line = 15;
   $id_cont = 0;?>
   <?php for($id = 0; $id < count($images); $id++){
        $id_cont++;?>
      
      <td><input type="image" src="<?php echo $images[$id];?>" id="<?php echo $images[$id];?>" data-toggle="modal" data-target="#add_data_Modal" class="edit_data" onclick="AjaxResponse()" alt=''/></td>

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
                          <label>Type</label>  
                          <input type="text" name="type" id="type" class="form-control" />  
                          <br />  
                          <label>Rotation</label>  
                          <input type="text" name="rotation" id="rotation" class="form-control" />  
                          <br /> 
                          <label>Inversion</label>  
                          <input type="text" name="inversion" id="inversion" class="form-control" />  
                          <br />
                          <label>Lateral flip</label>  
                          <input type="text" name="flip" id="flip" class="form-control" />  
                          <br />
                          </div>
				      

                          </div> 
                          <div class="text-right">
                         <input type="submit" name="insert" id="insert" value="Insert" class="btn btn-success" /> 
                         </div>
                          <input type="text" name="img_id" id="img_id" />  
                            
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
           var table_name = jQuery('#table').val();
           var columns = jQuery('#columns').val();
           
           
           $.ajax({  
                url:"fetch.php",  
                method:"POST",  
                data:{img_id:img_id, table_name:table_name},  
                dataType:"json",  
                success:function(data){  
                     $('#type').val(data.type);  
                     $('#rotation').val(data.rotation);  
                     $('#inversion').val(data.inversion);  
                     $('#flip').val(data.flip);  
                     $('#img_id').val(img_id);  
                     $('#insert').val("Update");  
                     $('#add_data_Modal').modal('show');  
                }  
           });  
      });
      
      $('#insert_form').on("submit", function(event){  
           event.preventDefault();  
           if($('#type').val() == "")  
           {  
                alert("type is required");  
           }  
           else if($('#rotation').val() == '')  
           {  
                alert("rotation is required");  
           }  
           else if($('#inversion').val() == '')  
           {  
                alert("inversion is required");  
           }  
           else if($('#flip').val() == '')  
           {  
                alert("flip is required");  
           }  
           else  
           {  
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
 
                     }  
                });  
           }  
      });

     
    
     });  
 </script>