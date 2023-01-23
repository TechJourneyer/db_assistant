<?php 
require_once 'config.php';
require_once ROOTDIR . 'functions.php';

?>
<!DOCTYPE html>
<html>
<head>
  <title>DB Assistant</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<style>
  .cbtn-primary{
    background-color:black;
    color:white;
  }
  .cbtn-primary:hover{
    background-color:#5c5858;
    color:white;
  }
  .cbtn-border{
    border:1px solid black;
  }
  .form-group{
    display: flex;
  }
  #selectBox{
    width: auto;
    margin-right: 21px;
  }
  .show-query{
    text-align:left !important;
    padding: 7px;

  }
  .query-box{
    background: #f8eff4 ;
    padding: 7px;
  }

</style>
<body>
<div class="container">
  <div class="text-center">
    <img src="<?php ECHO SITE_URL; ?>images/site-logo.png" alt="Logo" style="height:100px">
  </div>
  <!-- <p>Transforming the way you interact with your data</p> -->
  <div class="form-group">
    <select class="form-control" id="selectBox">
      <?php foreach(app_list() as $key => $value){ ?>
      <option value="<?php echo $key; ?>"><?php echo $key; ?></option>
      <?php } ?>
    </select>
    <input type="text" class="form-control" id="inputString" placeholder="Enter your search query">
  </div>
  <button type="button" class="btn cbtn-primary cbtn-border btn-block" id="submitBtn">Search</button>
  <br>
  <div class="text-center" id="loading" style="display:none"> Generating results...</div>
  <div id="response"></div>
</div>
<script>
  $(document).ready(function() {
    $(document).on('keypress',function(e) {
        if(e.which == 13) {
            generateResults();
        }
    });
    $("#submitBtn").click(function() {
      generateResults();
    });

    function generateResults(){
      var inputString = $("#inputString").val();
      var selectBox = $("#selectBox").val();

      inputString = inputString.trim();
      if(inputString == ''){
          alert('Please enter a search query!');
          return false;
      }
      if(selectBox == ""){
        alert("Please select an option");
        return;
      } 
      $("#response").hide();
      $("#loading").show();
      $.ajax({
        type: "post",
        url: "http://localhost/dba/api.php",
        data: { prompt: inputString, app: selectBox },
        success: function(response) {
          $("#loading").hide();
          $("#response").html(response);
          $("#response").show();
        }
      });
    }
  });
</script>
</body>
</html>