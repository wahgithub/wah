<?
  session_start();
  
  $dbname = $_SESSION['dbname'];
  $_SESSION["query"] = $dbname;
  $dbuser = "root";
  $dbpwd = "root";
  $_SESSION["province"] = "Tarlac";
  $_SESSION["lgu"] = "Moncada Rural Health Unit 1";
  $_SESSION["barangay_loc"] = "WAH";
  $_SESSION["barangay_id"] = "036906031";
  $_SESSION["doh_facility_code"] = "DOH000000000004925";
 
  $dbconn = mysql_connect("localhost",$dbuser,$dbpwd) or die(mysql_error());
  mysql_select_db($dbname,$dbconn);
?>
