<?php
session_start();
session_destroy();
header("Location: /MenteSerena-master/index.php");
exit();
?>