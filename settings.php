<?php ob_start(); 
require_once __DIR__.'/exam/examCore.php';
require_once __DIR__.'/resources/langMan.php';
$ExamCookie = new ExamCookieObject();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link href="css/style1.css" rel="stylesheet" type="text/css">
        <link href="css/style2.css" rel="stylesheet" type="text/css">
        <!--[if lte IE 9]>
	<link rel="stylesheet" type="text/css" href="css/ie.css" media="screen" />
	<![endif]-->
        <title><?php echo $GLOBALS['AppTitle'].' - '.$GLOBALS['SettingsTitle']; ?></title>
    </head>
    <body>

        <?php
        
        $TestCenterID = isset($_POST['tcid']) ? $_POST['tcid'] : null;
        $LabID = isset($_POST['lid']) ? $_POST['lid'] : null;
        $ComputerName = isset($_POST['cname']) ? $_POST['cname'] : null;


        if (!empty($TestCenterID) && !empty($LabID) && !empty($ComputerName)) {
            // Create Unique GUID
            $GUID = uniqid();

            // Create Client Cookie        
            $ExamCookie->TestCenterID = $TestCenterID;
            $ExamCookie->LabID = $LabID;
            $ExamCookie->ComputerName = $ComputerName;
            $ExamCookie->GUID = $GUID;
            $ExamCookie->Last_Status = 10;

            // Pass Cookie to Client Browser
            $ExamCookie->SetToBrowser(time() + 60 * 60 * 24 * 30);
            
            header("Location: index.php");
        } else {
            
?>
        <div class="dialog box-shadow">
            <form id="settingsForm" method="post">
                    <h1><?php echo $GLOBALS['SettingsTitle'];?></h1>
                    <table>
                        <tr>
                            <th><?php echo $GLOBALS['TestCenterIDTxt'];?></th>
                            <td><input id="tcid" type="text" name="tcid" value="" /></td>
                        </tr>
                        <tr>
                            <th><?php echo $GLOBALS['LabIDTxt'];?></th>
                            <td><input id="lid" type="text" name="lid" value="" /></td>
                        </tr>
                        <tr>
                            <th><?php echo $GLOBALS['ComputerNameTxt'];?></th>
                            <td><input id="cname" type="text" name="cname" value="" /></td>
                        </tr>
                    </table>
                    <div id="okbtn"><button><span></span><?php echo $GLOBALS['SaveButtonTxt'];?></button></div>
            </form>
        </div>
        <div id="welcomeFooter">
            <div id="version"><?php echo $GLOBALS['VersionTxt'].': '.Config::VERSION;?></div>
            <div id="copyrights"><?php echo Config::COPYRIGHTS;?></div>
            <div id="langOption">
                    <form method="get" action="index.php">
                        <label for="Lang"><?php echo $GLOBALS['LanguageTxt']; ?></label>
                        <select name="Lang" onchange="this.form.submit();">
<?php
                        foreach(Config::$LANGS as $_entry)
                        {
                            $Name = $_entry['Name'];
                            $File = $_entry['Filename'];
                            if($File == $GLOBALS['Lang'])
                            {
                                echo "<option value='$File' selected>$Name</option>";
                            } else {
                                echo "<option value='$File'>$Name</option>";
                            }
                        }
?>
                        </select>
                    </form>
                </div>
        </div>
<?php  } ?>
    </body>
</html>
<?php ob_end_flush(); ?>