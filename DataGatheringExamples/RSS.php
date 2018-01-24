<?php

  include('core/init.inc.php');
?>
<html>
  <head>
  </head>
  <body>
    <div>
      <pre>
      <?php

foreach(fetch_news() as $article){
    ?>
    <h3><a href="<?php echo $article['link'];?>"><?php echo $article['title'];?> </a></h3>
    <p>
          <?php echo $article['description'];?>
          </p>
          <?php
      }
      
      ?>
      </pre>
      </div>
  </body>
</html>
