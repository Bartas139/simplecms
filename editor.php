<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
  $content = $_POST['content'];
    
  
}

?><!DOCTYPE html>
<html>
<head>
  <title>TEST</title>
  <script src="https://cloud.tinymce.com/5/tinymce.min.js?apiKey=ywkhpwowowayd8oa38hdkmmpyxvl43sgtxhzss3uukoiarn1"></script> 
  <script>
  tinymce.init({
    selector: '#mytextarea'
  });
  </script>
</head>
<body>


<h1>TinyMCE Quick Start Guide</h1>
  <form method="post">
    <textarea name="content" id="mytextarea">Hello, World!</textarea>
    <button type="submit">uložit</button>
  </form>

<p>
  <?php echo $content; ?>
</p>

</body>
</html>


