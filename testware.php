<?php
/*
 * Testware Mode PHP Script
 */
require_once __DIR__.'/exam/examCore.php';
require_once __DIR__.'/resources/WUI.php';
ini_set('max_execution_time', 60);
/* @var $Session ExamSessionObject */
/* @var $Questions QuestionsList */
/* @var $Settings TestwareSettings */

defined('_E_EXEC') or die();

$ExamCookie = new ExamCookieObject();

if(isset($_REQUEST['UID']) || $ExamCookie->GetFromBrowser()):

    if(isset($_REQUEST['UID'])) {
        $ExamCookie->GUID = $_REQUEST['UID'];
    }

    $TimeCookie = new TimeCookieObject();
    $TimeCookie->GetFromBrowser();

    // Handle Buttons Action
    $ShowAnswers = isset($_POST['showanswers']) ? true : false;
    $SubmitQuestion = isset($_POST['submitQuestion']) ? true : false;
    $IgnoreQuestion = isset($_POST['ignoreQuestion']) ? true : false;
    $ExitExam = isset($_POST['exitExam']) ? true : false;
    $OrdNum = isset($_POST['ord']) ? $_POST['ord'] : null;
    $EndExam = isset($_POST['finishExam']) ? true : false;

    $QuestionObj = null;
    $FeedbackTable = null;
    $Qdata = new QuestionData;
    
    // Send ACK To Yii Service Manager
    SendClient($ExamCookie, $Session, $Status, $Questions, $Settings);
    if(!$Session) {
        header('Location: '.Config::TESTWARE_RETURN_PATH);
    }
?>
<div id="qPanel">
    <?php    

    $ExamCookie->ExamDuration = $Session->ExamDuration;
    $ExamCookie->UserAns = null;
    $LastQuestion = null;
    
    // Finish Exam
    if($EndExam) {
        $ElapsedTime = time() - $TimeCookie->StartTime;
        $Question = $Questions->GetQuestionByIndex(0); //$Questions->GetQuestion(1);
        $Question->ElapsedTime += $ElapsedTime;
        SendFinish($ExamCookie, $Session, $Question);
        
        // Delete ExamCookie
        setcookie($ExamCookie->Name, null, time() - 3600);
        $TimeCookie->Delete();
        if(isset($_COOKIE['ans']))
            setcookie ('ans', null, time()-3600);
        
        header('Location: '.Config::TESTWARE_RETURN_PATH);
    }
    
    
    
    // Show Question When Called From FeedBack Table
    if($OrdNum || $ExamCookie->FeedQuestion) {
        if($OrdNum && $OrdNum > 0) {
            $ExamCookie->FeedQuestion = $OrdNum - 1;
        }
        //$LastQuestion = $Questions->GetQuestion($ExamCookie->FeedQuestion);
        $LastQuestion = $Questions->GetQuestionByIndex($ExamCookie->FeedQuestion);

        if($OrdNum) {
            $ElapsedTime = time() - $TimeCookie->StartTime;
            $LastQuestion->ElapsedTime += $ElapsedTime;

            SendResults($ExamCookie, $Session, $LastQuestion);
            
            // Update Questions
            SendClient($ExamCookie, $Session, $Status, $Questions, $Settings);
        }
        //$LastQuestion = $Questions->GetQuestion($ExamCookie->FeedQuestion);
    } else {
        $LastQuestion = $Questions->GetNextNotAnswered();
    }

    if($ExitExam) {
        $LastQuestion = null;
    }
    
    // If we have an answer the Cookie 'ans' has be set
    if (isset($_COOKIE['ans'])) {
                
        // Set ElapsedTime
        $ElapsedTime = time() - $TimeCookie->StartTime;
        // The Grade
        $Grade = '0.00';
        
        
        if(isJSON($_COOKIE['ans'])) {
            $jobj = json_decode($_COOKIE['ans'],true);
            // User Answers for save to DB.
            $uArray = array();
            
            // Drag n Drop
            if($jobj['type'] == 'DND')
            {
                $grid = $jobj['data'];
                $lastCell = $jobj['lastCell'];
                $correct = 0;
                
                // Get Correct Answers
                // Image Name │ Correct Positions
                $correctAnswers = explode('#',$ExamCookie->CorrectAns);
                $onlyCorrectWithPos = array();
                
                foreach($correctAnswers as $correctAnswer)
                {
                    if(empty($correctAnswer)) continue;
                    // Get Correct Array
                    // [0] Image Name
                    // [1] Correct Positions
                    $cArray = explode(Delimiters::PARAMS_179,$correctAnswer);
                    
                    
                    // if positions exists
                    if(isset($cArray[1]) && !empty($cArray[1]))
                    {
                        $cPositions = explode(',',$cArray[1]);

                        foreach($cPositions as $cPos)
                        {
                            $imgName = $grid[$cPos-1];
                            if($imgName == $cArray[0])
                            {
                                $correct++; // Mark a Correct Answer
                                break;
                            }
                        }
                        $onlyCorrectWithPos[] = $correctAnswer;
                    }
                    
                    // Create UAnswers Array
                    $indexOf = array_search($cArray[0],$grid);
                    if(is_int($indexOf)){
                        if($indexOf < $lastCell){
                            $uArray[] = $cArray[0].Delimiters::PARAMS_179.$indexOf;
                        } else {
                            $uArray[] = $cArray[0].Delimiters::PARAMS_179;
                        }
                    }
                }
                
                // Calculate Grade
                $GradePerAnswer = round($ExamCookie->Grade / count($onlyCorrectWithPos),2);
                if($correct < count($onlyCorrectWithPos)) {
                    $Grade = $GradePerAnswer * $correct;
                } else {
                    $Grade = $ExamCookie->Grade;
                }
                // Save UAnswers
                $ExamCookie->UserAns = implode('#',$uArray);
            } 
            // Fill the Color
            elseif($jobj['type'] == 'FTC')
            {
                // Data From Submit Request
                $answers = $jobj['data'];
                
                // Colors From Builder
                $colors = json_decode($ExamCookie->CorrectAns,true);
                
                // Correct Colors
                $correct = 0;
                
                
                $maxInt = count($answers);
                for($i=0;$i<$maxInt;++$i)
                {
                    // Pixel = array('r'=>'0-255','g'=>'0-255','b'=>'0-255');
                    $answer_pixel = $answers[$i];
                    
                    // Color = array([name]=>'string',[position]=>'x,y')
                    $color = $colors[$i];
                    $color_name = $color['name'];
                    
                    if(FTC::isCorrectColor($color_name, $answer_pixel))
                    {
                        $correct++;
                    }
                    
                    // Greate UAnswers
                    $uArray[] = array('color'=>$answer_pixel,'position'=>$color['position']);
                }
                
                // Calculate Grade
                $GradePerAnswer = round($ExamCookie->Grade / $maxInt,2);
                if($correct < $maxInt)
                {
                    $Grade = $GradePerAnswer * $correct;
                } else
                {
                    $Grade = $ExamCookie->Grade;
                }
                
                // Save UAsnwers
                $ExamCookie->UserAns = json_encode($uArray);
            }
            
        } else {
            //ShowAlert('COOKIE ans IS NOT A JSON OBJECT...');
            
            // Check if we got Multi Answers
            $isMulti = preg_match('/~/', $_COOKIE['ans']);
            $isMatching = preg_match('/%u2524/', $_COOKIE['ans']);
            $isFillGaps = preg_match('/%u2502/', $_COOKIE['ans']);
            $isStaticDisplay = preg_match('/static-display/', $_COOKIE['ans']);


            /* Two Plus Correct Answers */
            if ($isMulti) {                            
                $client_answers = preg_split('/~/', $_COOKIE['ans'], null, PREG_SPLIT_NO_EMPTY);
                $correctAnswers = preg_split("/\\\\/", $ExamCookie->CorrectAns, null, PREG_SPLIT_NO_EMPTY);
                $correct = 0;        

                // Keep User Answers into ExamCookie for send them to manager
                $ExamCookie->UserAns = implode('\\', $client_answers);

                // More Answers of correct answers, wrong is inside so bypass for perfomance!
                if(count($client_answers) <= count($correctAnswers)) {
                    foreach($correctAnswers as $CAnswer) {
                        foreach($client_answers as $Answer) {
                            if($CAnswer == $Answer) {
                                $correct++;
                                break;
                            }
                        }
                    }

                    // If have at last one wrong answer
                    // Grade = 0.00
                    if($correct == count($client_answers)){
                        $GradePerAnswer = round($ExamCookie->Grade / count($correctAnswers),2);
                        if($correct <= count($correctAnswers)) {
                            $Grade = $GradePerAnswer * $correct;
                        } else {
                            $Grade = $ExamCookie->Grade;
                        }
                    }
                }
            } 
            /* Fill The Gaps */
            elseif ($isFillGaps) {

                $correctAnswers = preg_split('/│/', $ExamCookie->CorrectAns, null, PREG_SPLIT_NO_EMPTY);
                $client_answers = preg_split('/%u2502/', $_COOKIE['ans'], count($correctAnswers));

                // remove any delimiter from last substring.
                $client_answers[count($client_answers)-1] = 
                    preg_replace('/%u2502/', '', $client_answers[count($client_answers)-1]);

                // Translate Answers
                $length=count($client_answers);
                for($i=0;$i<$length;++$i){
                    $temp = preg_replace('/%/', '\\', $client_answers[$i]);
                    $temp = '{"value":"'.$temp.'"}';
                    $temp = json_decode($temp,true);
                    $client_answers[$i]= mb_strtoupper($temp['value']);
                }


                // Keep User Answers into ExamCookie for send them into manager!
                $ExamCookie->UserAns = implode('│',$client_answers);

                $correct=0;
                for($i=0;$i<count($correctAnswers);$i++) {
                    $clientAnswer = null;
                    // Split Multiple Client Answers
                    if(preg_match('/\,/', $client_answers[$i])) {
                        $splited = preg_split('/\,/', $client_answers[$i]);
                        $clientAnswer = $splited[0];
                    } else {
                        $clientAnswer = $client_answers[$i];
                    }
                    // Check For Correct Answers
                    if(preg_match('/┼/', $correctAnswers[$i])) {
                        $splited = preg_split('/┼/', $correctAnswers[$i]);
                        foreach($splited as $correctAnswer) {
                            if($correctAnswer == $clientAnswer) {
                                $correct++;
                                break;
                            }
                        }
                    } else {
                        if(strtoupper($correctAnswers[$i]) == strtoupper($clientAnswer)) {
                            $correct++;
                        }
                    }
                }                            
                // Callculate Grade
                $GradePerAnswer = round($ExamCookie->Grade / count($correctAnswers),2);
                if($correct < count($correctAnswers))
                {
                    $Grade = $GradePerAnswer * $correct;
                } else {
                    $Grade = $ExamCookie->Grade;
                }
            }
            elseif($isMatching) {
                $hasGroups = preg_match('/%u2562/', $_COOKIE['ans']);
                if($hasGroups) {
                    // Matching Groups

                    $correctAnswers = preg_split('/%u2562/', $ExamCookie->CorrectAns, null, PREG_SPLIT_NO_EMPTY);
                    $client_answers = preg_split('/%u2562/', $_COOKIE['ans'], count($correctAnswers));

                    $client_answers[count($client_answers)-1] =
                        preg_replace('/%u2562/', '', $client_answers[count($client_answers)-1]);

                    // Keep User Answers into ExamCookie for send them to manager
                    $ExamCookie->UserAns = implode('╢', $client_answers);

                    $GradePerGroup = round($ExamCookie->Grade / (count($correctAnswers)/2), 2);
                    $correct = null;

                    for($i=0;$i<count($correctAnswers);$i+=2) {
                        // Group Name = $i
                        // Group Answers = $i+1
                        $correct[$correctAnswers[$i]] = 0;
                        $splited = preg_split('/%u2524/',$correctAnswers[$i+1], null, PREG_SPLIT_NO_EMPTY);
                        $clientAnswer  = $client_answers[$i+1];

                        foreach($splited as $correctAnswer) {
                            if(preg_match("/$correctAnswer/",$clientAnswer)) {
                                $correct[$correctAnswers[$i]]++;
                            }
                        }

                        // Calculate Grade
                        if($correct[$correctAnswers[$i]] < count($splited)) {
                            $GradePerAnswer = round($GradePerGroup / count($splited), 2);
                            $Grade += round($GradePerAnswer * $correct[$correctAnswers[$i]], 2);
                        } else {
                            $Grade += $GradePerGroup;
                        }
                    }                                
                } else {
                    // Matching
                    $correctAnswers = preg_split('/│/', $ExamCookie->CorrectAns, null, PREG_SPLIT_NO_EMPTY);
                    $client_answers = preg_split('/%u2524/', $_COOKIE['ans'], count($correctAnswers));

                    $client_answers[count($client_answers)-1] = 
                        preg_replace('/%u2524/', '', $client_answers[count($client_answers)-1]);

                    // Keep User Answers into ExamCookie for send them to manager.
                    $ExamCookie->UserAns = implode('┤',$client_answers);

                    $correct = 0;

                    for($i=0;$i<count($correctAnswers);$i++) {
                        if($correctAnswers[$i] == $client_answers[$i]) {
                            $correct++;
                        }
                    }

                    // Calculate Grade
                    $GradePerAnswer = round($ExamCookie->Grade / count($correctAnswers), 2);
                    if($correct < count($correctAnswers)) {
                        $Grade = $GradePerAnswer * $correct;
                    } else {
                        $Grade = $ExamCookie->Grade;
                    }
                }
            }
            // Static Display
            elseif($isStaticDisplay) {
                $Grade = 0;
            }
            // One Correct Answer
            else {
                $client_answer = $_COOKIE['ans'];
                $Grade = ($client_answer == $ExamCookie->CorrectAns) ? $ExamCookie->Grade : 0;
                // Keep User Answer into ExamCookie for send them to manager.
                $ExamCookie->UserAns = $client_answer;
            }
        }
        
        // Set Question Data
        $LastQuestion->Grade = $Grade;
        $LastQuestion->ElapsedTime += $ElapsedTime;
        $LastQuestion->Status = 1;

        // Send Results to Manager.
        SendResults($ExamCookie, $Session, $LastQuestion);

        // Update Questions
        SendClient($ExamCookie, $Session, $Status, $Questions);

        // Clear Feedback Question
        $ExamCookie->FeedQuestion = null;
        
        // Get Next Question
        $LastQuestion = $Questions->GetNextNotAnswered();

        // Unset Ans Cookie.
        setcookie('ans', null, time() - 3600);   
    } // Submit Question END

    // Ignore Question
    if ($IgnoreQuestion && $LastQuestion) {
        // Set Question
        $ElapsedTime = time() - $TimeCookie->StartTime;
        $LastQuestion->ElapsedTime += $ElapsedTime;
        $LastQuestion->Status = -1;

        // Send Results to Manager.
        SendResults($ExamCookie, $Session, $LastQuestion);

        // Update Questions
        SendClient($ExamCookie, $Session, $Status, $Questions);
        
        // Clear Feedback Question
        $ExamCookie->FeedQuestion = null;
        
        // Get Next Question
        $LastQuestion = $Questions->GetNextNotAnswered();
    } // Ignore Question END

    if ($LastQuestion) {                      
        // Question Data
        $Qdata->QID = $LastQuestion->QID;
        $Qdata->ModuleID = $LastQuestion->QSet;
        $Qdata->CertID = $LastQuestion->Cert;

        // Save Order Number to Cookie
        $ExamCookie->LastQuestionOrder = $LastQuestion->OrderNumber;

        // Get Question From Builder & Show It
        $QuestionObj = ShowQuestion($Qdata, $ExamCookie, $ShowAnswers);

        $TimeCookie->StartTime = time();
    } else {
        // Clear Feedback Question
        $ExamCookie->FeedQuestion = null;
        
        // Show Feedback
        ShowFeedback($Questions, $Settings);

        $TimeCookie->StartTime = time();
        $FeedbackTable = true;
    }

    // Set TimeCookie to Browser
    $TimeCookie->SetToBrowser();
    ?>
</div>
    <?php
    // Show Taskbar
    $TaskBarParams = new TaskBarParams();
    $TaskBarParams->QDescription = $QuestionObj ? $QuestionObj->QuestionText : null;
    $TaskBarParams->CardNo = $Session->LastName;
    $TaskBarParams->ComputerName = $Session->FirstName;
    $TaskBarParams->QNumber = $LastQuestion ? $LastQuestion->Index /* $LastQuestion->OrderNumber */ : null;
    $TaskBarParams->QTotal = $Questions->GetTotalQuestions();
    $TaskBarParams->FeedbackTable = $FeedbackTable;
    $TaskBarParams->QuestionID = $QuestionObj ? $QuestionObj->QID : null;
    // Testware Features
    $TaskBarParams->Showhelp = $Settings->ShowHelp;    
    
    ShowTaskbar($TaskBarParams);
    
    // Virtual Timer
    if($ExamCookie->ExamDuration)
    {
        $timeRemain = $ExamCookie->ExamDuration * 60;
	$timeRemain -= (time() - strtotime($Session->StartDate.' '.$Session->StartTime));
        //$timeRemain = ($ExamCookie->ExamDuration * 60) - $Questions->GetTotalElapsedTime();
        echo "
                <script>
                $(document).ready(function(){
                    var timeRemain = $timeRemain;
                    var minutes = null;
                    var seconds = null;
                    var element = document.getElementById('timeText');

                    virtualTimer = setInterval(function(){
                        hours   = Math.floor((timeRemain / (60*60)));
                        minutes = Math.floor((timeRemain / 60) - (hours*60));
                        seconds = timeRemain - (minutes*60) - (hours*(60*60));

                        if(seconds<10 && minutes<10) {
                         element.innerHTML = '0' + hours + ':' + '0' + minutes + ':' + '0' + seconds;
                        }
                        else if(minutes<10)
                        {
                         element.innerHTML = '0' + hours + ':' + '0' + minutes + ':' + seconds;
                        }
                        else if(seconds < 10)
                        {
                         element.innerHTML = '0' + hours + ':' + minutes + ':' + '0' + seconds;
                        }
                        else 
                        {
                         element.innerHTML = '0' + hours + ':' + minutes + ':' + seconds;
                        }

                        if(timeRemain <= 0) {
                            clearInterval(virtualTimer);
                            finishExam();
                        }
                        timeRemain--;
                    }, 1000);
                });
                </script>
            ";
    }
    
    $ExamCookie->SetToBrowser(0);
    ?>
<?php endif?>
<?php // Render Index Page ?>
