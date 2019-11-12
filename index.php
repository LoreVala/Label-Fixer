<?php 
    session_start();
?>

<!DOCTYPE html>
<html>
 <head>
  <title>Label Fixer</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
  <style>
  .box
  {
   max-width:600px;
   width:100%;
   margin: 0 auto;;
  }
  </style>
 </head>
 <body>
  <div class="container">
   <br />
   <h3 align="center">Label Fixer</h3>
   <br />
   <form id="upload_csv" method="post" enctype="multipart/form-data">
    <div class="col-md-3">
     <br />
     <label>Select JSON File</label>
    </div>  
                <div class="col-md-4">  
                    <input type="file" name="csv_file" id="csv_file" accept=".json" style="margin-top:15px;" />
                </div>  
                <div class="col-md-5">  
                    <input type="submit" name="upload" id="upload" value="Upload" style="margin-top:10px;" class="btn btn-info" />
                </div>  
                <div style="clear:both"></div>
   </form>
   <br />
   <br />
   <div id="csv_file_data"></div>

  </body>
</html>

<script>

$(document).ready(function(){
 $('#upload_csv').on('submit', function(event){
  event.preventDefault();
  $.ajax({
   url:"create_db.php",
   method:"POST",
   data:new FormData(this),
   dataType:'json',
   contentType:false,
   cache:false,
   processData:false,
   beforeSend:function(){  
                        $('#upload').val("Uploading...");  
                     },
   success:function(data)
   {

       if(data){
        
        var html = '<form action="grid_multi.php" method="POST">'
        html += '<strong>Data from '+data.file+' Imported and'+data.boh+'</strong>'; 
        html += '<input type="hidden" name="file" value="'+data.file+'" />'
        html += '<input type="submit" value="Visualize" />'
        html += '</form>'
       }
    
    $('#csv_file_data').html(html);
    $('#upload_csv')[0].reset();
    $('#upload').val("Upload");
   }
  });
 });
});

</script>