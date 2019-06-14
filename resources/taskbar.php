<?php
class TaskBarParams {
    public $QDescription;
    public $CardNo;
    public $ComputerName;
    public $QNumber;
    public $QTotal;
    public $FeedbackTable;
    public $QuestionID;
    public $Showhelp = false;
    public $ShowScore = false;
}

/**
 * Show Taskbar
 * @param TaskBarParams $Params
 * @param Language $Lang
 */
function ShowTaskbar($Params) {
?>
<div id="taskbar" class="box-shadow box-shadow-up-ie task_bottom">
    
    <div id="tooltip">
<?php
    $tooltipText = null;
    if(!$Params->FeedbackTable) { 
        $tooltipText = htmlspecialchars_decode($Params->QDescription);
    } else {
        $tooltipText = '<p>'.$GLOBALS['FeedbackMsgTxt'].'</p>';
    }
    echo $tooltipText;
?>  
    </div>
    
    <div id="taskbarContainer">
        <div id="topPanel">
<?php if(!$Params->FeedbackTable) { ?>
            <span><?php echo $Params->QNumber;?> / <?php echo $Params->QTotal;?></span>
            <span>#<?php echo $Params->QuestionID;?></span>
            <span><?php echo $Params->CardNo;?></span>
            <span id="computerName"><?php echo $Params->ComputerName; ?></span>                

            <span class="fadeLine"></span>
<?php } ?>
        </div>
        <div id="midPanel">
            <div id="buttonsContainer">
<?php   if($Params->FeedbackTable) { ?>
                <table>
                    <tr>
                        <td><span id="timeText">00:00:00</span></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            <form id="endForm" method="post" action="index.php">
                                <button id="finishBtn">
                                    <span></span><?php echo $GLOBALS['FinishButtonTxt'];?>
                                </button>
                                <input type="hidden" value="1" name="finishExam">
                            </form>
                        </td>
                    </tr>
                </table>
<?php
        }
        else
        {
?>
                <table>
                    <tr>
                        <td>
                            <span id="timeText">00:00:00</span>
                        </td>
<?php
            if($Params->Showhelp && Config::TestwareMode) {
?>           
                        <td>
                            <form id="helpForm" method="post">
                                <button id="helpBtn">
                                    <span></span><?php echo $GLOBALS['testware_HelpButton'];?>
                                </button>
                                <input type="hidden" value="1" name="showanswers" />
                            </form>
                        </td>
<?php
            }
?>                                                
                        <td>
                            <form id="extForm" method="post" action="index.php">
                                <button id="exitBtn">
                                    <span></span><?php echo $GLOBALS['ExitButtonTxt'];?>
                                </button>
                                <input type="hidden" value="1" name="exitExam" />
                            </form>
                        </td>
                        <td><form id="clearForm" method="post">
                            <button id="clearBtn">
                                <span></span><?php echo $GLOBALS['RefreshButtonTxt'];?>
                            </button>
                            </form>
                        </td>
                        <td>
                            <form id="ignForm" method="post" action="index.php">
                                <button id="ignoreBtn">
                                    <span></span><?php echo $GLOBALS['IngoreButtonTxt'];?>
                                </button>
                                <input type="hidden" value="1" name="ignoreQuestion" />
                            </form>
                        </td>
                        <td>
                            <form id="subForm" method="post" action="index.php">
                                <button id="submitBtn">
                                    <span></span><?php echo $GLOBALS['SubmitButtonTxt'];?>
                                </button>
                                <input type="hidden" value="1" name="submitQuestion" />
                            </form>
                        </td>
                    </tr>
                </table>
<?php   } ?>
            </div>
        </div>
    </div>
</div>
<?php } ?>

