<?php
require_once __DIR__.'/exam/examCore.php';
require_once __DIR__.'/resources/WUI.php';

$params = getEncryptedParams();

//if($params!==null){
//    $LG = $params['LG'];
//    $GroupID = $params['GID'];
//    $QuestionID = $params['QID'];
//    $ModuleID = $params['MID'];
//    $CertID = $params['CID'];
//    $Pass = $params['PS'];
//    
//    //var_dump($params);
//} else {
//    die('Wrong or Empty Parameters!');
//}

$LG = isset($_REQUEST['LG']) ? $_REQUEST['LG'] : Config::DEFAULT_LANG;
$GroupID = isset($_REQUEST['GID']) ? $_REQUEST['GID'] : null;
$QuestionID = isset($_REQUEST['QID']) ? $_REQUEST['QID'] : null;
$ModuleID = isset($_REQUEST['MID']) ? $_REQUEST['MID'] : null;
$CertID = isset($_REQUEST['CID']) ? $_REQUEST['CID'] : null;
$Pass = isset($_REQUEST['PS']) ? $_REQUEST['PS'] : null;

require_once __DIR__.'/resources/languages/'.$LG.'.php';

if ($QuestionID && $ModuleID && $CertID && $Pass) {

    if (md5(Config::PREVIEW_PASSWORD) == md5($Pass)) {
        ?>
        <!DOCTYPE html>
        <html lang="<?php echo $GLOBALS['Lang'];?>">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <link href="css/style1.css" rel="stylesheet" type="text/css" >
                <link href="css/style2.css" rel="stylesheet" type="text/css" >
                <link href="css/preview.css" rel="stylesheet" type="text/css">
                <!--[if lte IE 9]>
                <link rel="stylesheet" type="text/css" href="css/ie.css" media="screen" />
                <![endif]-->
                
                <script type="text/javascript" src="js/jquery-1.8.2.js"></script>
                <script type="text/javascript" src="js/preview.js"></script>
                <script type="text/javascript" src="js/html5media.min.js"></script>
                <title><?php echo $GLOBALS['AppTitle'];?></title>
            </head>
            <body>
                <div id="qPanel">
                    <?php
                    $QuestionData = new QuestionData;
                    $QuestionData->ModuleID = $ModuleID;
                    $QuestionData->CertID = $CertID;
                    $QuestionData->QID = $QuestionID;
                    $ExamCookie = new ExamCookieObject();
                    
                    $QuestionObj = LoadQuestionData($ModuleID, $CertID, $QuestionID, $ExamCookie, TRUE, TRUE, $GroupID);
                    
//                    echo '<div id="cookieDebug" style="position:relative;z-index:1000;width:800px;padding:20px;background:black;color:white;">ExamCookie:</br><pre>'.
//                            print_r($ExamCookie,true).
//                         '</pre></div>';
                    
                    ?>
                </div>
                <?php
                $TaskBarParams = new TaskBarParams();
                $TaskBarParams->QDescription = $QuestionObj ? $QuestionObj->QuestionText : null;
                $TaskBarParams->CardNo = '0000';
                $TaskBarParams->ComputerName = 'PREVIEW';
                $TaskBarParams->QNumber = '1';
                $TaskBarParams->QTotal = '1';
                $TaskBarParams->FeedbackTable = false;
                $TaskBarParams->QuestionID = $QuestionObj ? $QuestionObj->QID : null;
                
                $TaskBarParams->Showhelp = true;

                ShowTaskbar($TaskBarParams);
            }
        } else {
            echo "<script>location.replace('./');</script>";
        }
        ?>
    </body>
</html>