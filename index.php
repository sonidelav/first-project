<?php
define('_E_EXEC',TRUE);
require_once __DIR__.'/exam/examCore.php';
require_once __DIR__.'/resources/langMan.php';

$CookieObj = new ExamCookieObject();
$cookieExist = $CookieObj->GetFromBrowser();

$EndExam = isset($_POST['finishExam']) ? true : false;
$StartExam = isset($_POST['startexam']) ? true : false;

ob_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="<?php echo $GLOBALS['Lang'];?>">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link href="css/style1.css" rel="stylesheet" type="text/css" >
        <link href="css/style2.css" rel="stylesheet" type="text/css" >
        <!--[if lte IE 9]>
	<link rel="stylesheet" type="text/css" href="css/ie.css" media="screen" />
	<![endif]-->
        
<?php if ($cookieExist || isset($_REQUEST['UID'])) { ?>
            <script type="text/javascript" src="js/jquery-1.8.2.js"></script>
            <script type="text/javascript" src="js/html5media.min.js"></script>
            <script>
                var autorefresh = null;
                var virtualTimer = null;
            </script>
<?php if (($CookieObj->Last_Status < 21 && $CookieObj->Last_Status > 1) || $EndExam) { 
            if(!$StartExam) {
?>     
            <script type="text/javascript" src="js/autos.js"></script>
<?php }} ?>
            <script type="text/javascript" src="js/script1.js"></script>
<?php } ?>
        <title><?php echo $GLOBALS['AppTitle']; ?></title>
    </head>
    <body>
        <?php
        if ($cookieExist && !Config::TestwareMode) {
            include_once 'exam.php';
            unset($CookieObj);
        } else {
            $cookieExist = null;
            if(Config::TestwareMode) {
                include_once 'testware.php';
            } else {
                header("Location: settings.php");
            }
        }
        ?>
    </body>
</html>
<?php
ob_end_flush();
?>