<?php  
  //$command = "php examples/peoplelf.php >> test.log 2>&1 &";
  $command = "php examples/setprophoto.php";
  //$command = "python python/main_rg.py";
  $retval = array();
  exec($command, $retval, $status);
  if ($status == 0) { 
  }
  foreach ($retval as $value)
  {
  	echo $value;
  }
  
  /*$log = shell_exec("tail -n 2 test.log");
  echo $log;*/


  exit(); 
//echo $html;  
?> 