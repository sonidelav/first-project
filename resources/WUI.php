<?php
/*
 * Web User Interface - WUI
 */
require_once __DIR__.'/../exam/examCore.php';
require_once __DIR__.'/taskbar.php';

/**
 * Show Welcome HTML
 * @param Language $Lang
 */
function ShowWelcome()
{
?>
<div id="welcomeContainer" class="box-shadow box-shadow-down-ie">
    <div id="welcomePage">
<?php if(Config::CUSTOM_LOGO) {?>
        <div id="logoContainer">
            <span id="welcomeLogo">
                <img src="css/logo/<?php echo Config::CUSTOM_LOGO;?>" />
            </span>
            <?php if(Config::CUSTOM_LOGO_TWO):?>
            <span id="welcomeSecondLogo">
                <img src="css/logo/<?php echo Config::CUSTOM_LOGO_TWO;?>" />
            </span>
            <?php endif;?>
        </div>
<?php } ?>
        <h1><?php echo $GLOBALS['WelcomeTxt']; ?></h1>
    </div>
    <div id="welcomePageMsg">
        <p><?php echo $GLOBALS['WelcomeMsg']; ?></p>
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
</div>
<?php
}

/**
 * Show User Information
 * @param ExamSessionObject $Session
 * @param Language $Lang
 */
function ShowUserInfo(&$Session)
{
    $Date = new DateTime($Session->StartDate);
    $Exam['Date'] = date("m/d/Y", $Date->getTimestamp());
    $Exam['Time'] = $Session->StartTime;
    $Exam['Duration'] = $Session->ExamDuration." Minutes";
    
    $User['ID'] = $Session->UID;
    $User['Father'] = $Session->FathersName;
    
    $Card['IDNo'] = $Session->IDCardNo;
    $Card['IDType'] = $Session->IDCardType;
    $Card['No'] = $Session->CardNo;
    
    $FullName = $Session->LastName." ".$Session->FirstName;
?>
<div id="userInfoContainer" class="box-shadow">
    <table id="userinfoPanel">
        <tr>
            <td rowspan="2" id="userPhoto"></td>
            <td colspan="2" id="userFullName">
                <p><?php echo $FullName;?></p>
            </td>
        </tr>
        <tr>
            <td id="infoCreds">
                    <table>
                        <tr>
                            <td rowspan="2" id="userCreds">
                                <p><?php echo $GLOBALS['UserInfoTxt'];?></p>
                                <span class="fadeLine"></span>
                                <table>
                                    <tr><th><?php echo $GLOBALS['UserIDTxt'];?></th>       <td><?php echo $User['ID'];?></td></tr>
                                    <tr><th><?php echo $GLOBALS['FathersNameTxt'];?></th>  <td><?php echo $User['Father'];?></td></tr>
                                </table>
                            </td>
                            <td id="cardInfo">
                                <p><?php echo $GLOBALS['CardInfoTxt'];?></p>
                                <span class="fadeLine"></span>
                                <table>
                                    <tr><th><?php echo $GLOBALS['CardIDNoTxt'];?></th>     <td><?php echo $Card['IDNo'];?></td></tr>
                                    <tr><th><?php echo $GLOBALS['CardIDTypeTxt'];?></th>   <td><?php echo $Card['IDType'];?></td></tr>
                                    <tr><th><?php echo $GLOBALS['CardNoTxt'];?></th>        <td><?php echo $Card['No'];?></td></tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td id="examCreds">
                                <p><?php echo $GLOBALS['ExamInfoTxt'];?></p>
                                <span class="fadeLine"></span>
                                <table>
                                    <tr><th><?php echo $GLOBALS['ExamDateTxt'];?></th>      <td><?php echo $Exam['Date'];?></td></tr>
                                    <tr><th><?php echo $GLOBALS['ExamTimeTxt'];?></th>      <td><?php echo $Exam['Time'];?></td></tr>
                                    <tr><th><?php echo $GLOBALS['ExamDurationTxt'];?></th>  <td><?php echo $Exam['Duration'];?></td></tr>
                                </table>
                            </td>
                        </tr>
                    </table>
            </td>
        </tr>
    </table>
</div>
<?php
}

/**
 * Show Start Button HTML
 * @param Language $Lang
 */
function ShowStartButton()
{
?>
<div id="startBtnDiv">
    <p><?php echo $GLOBALS['StartMsg'];?></p>
    <form id="startexamform" method="post" action="./">
        <button id="startBtn">
            <span></span><?php echo $GLOBALS['StartButtonTxt'];?>
        </button>
        <input type="hidden" name="startexam" value="1" />
    </form>
</div>
<?php
}

/**
 * Show Feedback HTML
 * @param QuestionList $Questions
 * @param TestwareSettings $Settings
 */
function ShowFeedback(&$Questions, &$Settings=null)
{
?>
<div id="FeedTitle" class="box-shadow box-shadow-down-ie">
    <table>
        <tr>
            <th class="QFeedNo"><?php echo $GLOBALS['Head_QuestionNoTxt'];?></th>
            <th class="QFeedText"><?php echo $GLOBALS['Head_QuestionTxt'];?></th>
            <th class="QFeedStatus"><?php echo $GLOBALS['Head_StatusTxt'];?></th>
            <th class="QFeedID"><?php echo $GLOBALS['Head_QIDTxt'];?></th>
<?php
if(Config::TestwareMode && $Settings->ShowScore) {
?>
            <th class="QFeedScore"><?php echo $GLOBALS['Head_Score'];?></th>
<?php   
}
?>
        </tr>
    </table>
</div>
<div id="FeedDiv">
    <table id="FeedTable">
        <?php
            $questionsList = $Questions->Items();
            foreach($questionsList as $QItem)
            {
                $Data = LoadQuestionData(
                        $QItem->QSet,
                        $QItem->Cert,
                        $QItem->QID, NULL, false, false
                );
                
                $QData = $Data['Question'];
                $Text = htmlspecialchars_decode($QData->QuestionText);
                
                $QNum = $QItem->Index; //$QItem->OrderNumber;
                $ID   = $QItem->QID;
                
                $StatusText = $StatusClass = null;
                
                switch($QItem->Status)
                {
                    case 1:
                        $StatusText = $GLOBALS['StatusesTxt'][0];
                        $StatusClass = "answered";
                        break;
                    case -1:
                        $StatusText = $GLOBALS['StatusesTxt'][1];
                        $StatusClass = "ignored";
                        break;
                    case 0:
                        $StatusText = $GLOBALS['StatusesTxt'][2];
                        $StatusClass = "notanswered";
                        break;
                }
                
       ?>
        <tr>
            <td class="QFeedNo"><?php echo $QNum;?></td>
            <td class="QFeedText">
                <form method="post">
                    <button id="feedButton"><?php echo $Text;?></button>
                    <input type="hidden" value="<?php echo $QNum;?>" name="ord"/> 
                </form>
            </td>
            <td class="QFeedStatus <?php echo $StatusClass;?>"><?php echo $StatusText;?></td>
            <td class="QFeedID"><?php echo $ID;?></td>
       
<?php if(Config::TestwareMode && ($Settings!==null && $Settings->ShowScore)) { ?>
            <?php if($QItem->Grade == $Data['MaxGrade']) :?>
                <td class="QFeedScore correct"><?php echo "$QItem->Grade / ".$Data['MaxGrade']; ?></td>
            <?php else: ?>
                <td class="QFeedScore wrong"><?php echo "$QItem->Grade / ".$Data['MaxGrade']; ?></td>
            <?php endif;?>
<?php }?>
        </tr>
       <?php
            }
        ?>
    </table>
</div>
<?php
}
?>
