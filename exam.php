<?php

defined('_E_EXEC') or die();

require_once __DIR__.'/exam/examCore.php';
require_once __DIR__.'/resources/WUI.php';

$ExamCookie = new ExamCookieObject();
$TimeCookie = new TimeCookieObject();

if ($ExamCookie->GetFromBrowser()) {
    
    $TimeCookie->GetFromBrowser();
    
    $Qdata = new QuestionData();
    $Session = new ExamSessionObject();
    $Status = new ClientStatusObject();
    $Questions = new QuestionListObject();
    
    // When EXAM STARTED Skip - Welcome Assigned Start Step

    $ShowAnswers = isset($_POST['showanswers']) ? true : false;
    $StartExam = isset($_POST['startexam']) ? true : false;
    $SubmitQuestion = isset($_POST['submitQuestion']) ? true : false;
    $IgnoreQuestion = isset($_POST['ignoreQuestion']) ? true : false;
    $ExitExam = isset($_POST['exitExam']) ? true : false;
    $OrdNum = isset($_POST['ord']) ? $_POST['ord'] : null;
    $EndExam = isset($_POST['finishExam']) ? true : false;
    
    if (!($StartExam || $SubmitQuestion || $IgnoreQuestion || $OrdNum || $ShowAnswers)) {
        
        echo "<div id='qPanel'>";

        // Send ACK to Manager
        SendClient($ExamCookie, $Session, $Status, $Questions);
        
        
        if ($ExitExam && ($Status->Code == 21)) {
            $ExamCookie->FeedQuestion = null;
            
            $ExamCookie->CorrectAns = null;
            $ExamCookie->Grade = null;
            $ExamCookie->ExamDuration = null;
            
            $ExamCookie->SetToBrowser(time() + 60 * 60 * 24 * 30);
            $TimeCookie->Delete();
            if(isset($_COOKIE['ans'])) {setcookie('ans',null, time() - 3600);}
        }
        
        if ($EndExam && ($Status->Code == 21)) {
            $ElapsedTime = time() - $TimeCookie->StartTime;
            
            $Question = $Questions->GetQuestion(1); // Get this for fill XML only porpuses.

            $Question->ElapsedTime += $ElapsedTime;

            SendFinish($ExamCookie, $Session, $Question);
            
            $ExamCookie->FeedQuestion = null;
            $ExamCookie->CorrectAns = null;
            $ExamCookie->Grade = null;
            $ExamCookie->ExamDuration = null;
            
            $ExamCookie->Last_Status = 10;

            $ExamCookie->SetToBrowser(time() + 60 * 60 * 24 * 30);
            $TimeCookie->Delete();
            if(isset($_COOKIE['ans'])) {setcookie('ans',null, time() - 3600);}
            
            SendClient($ExamCookie, $Session, $Status, $Questions);
        }
        
        switch ($Status->Code) {
            case 0:
                //echo "NOTVALID";
                break;

            case 10:
                //echo "STOP";
                break;

            case 11:
                // Status 11 Demo Enabled
                ShowWelcome();
                break;

            case 21:
                // Status 21 Exam Enabled
                ShowWelcome();

                // Wait for Assigned - Session Open...
                if ($Session) {
                    $ExamCookie->UID = $Session->UID;
                    $ExamCookie->ExamDate = $Session->StartDate;
                    $ExamCookie->ExamTime = $Session->StartTime;

                    // Show User Information Table
                    ShowUserInfo($Session);                   
                    
                    // Wait for Start...
                    SendStart($ExamCookie, $Session);
                    
                    if($ExamCookie->Last_Status == 21)
                    {
                        ShowStartButton();
                        // This is for not disable auto refresh from index page
                        $ExamCookie->Last_Status = 11; 
                    }
                    
                } // End of Session Assigned Handler
                break; // Status 21
        } // End Switch ACK Response Status Handle
        echo "</div>";
    } // } End Of - Welcome Assigned Start Step
    else 
    {
        /*
         ***********************************************************************
         *                    Start Examination Section
         ***********************************************************************
         */

        $QuestionObj = null;
        $FeedbackTable = null;
        if($StartExam) {
            $ExamCookie->Last_Status = -1;
        }
        
        ?> 
        <div id='qPanel'> 
            <?php
            
            // Send ACK to Manager            
            SendClient($ExamCookie, $Session, $Status, $Questions);
            
            if(!$Session) TerminateExam ($ExamCookie, $TimeCookie);
            
            // Send STR To Manager            
            SendStart($ExamCookie, $Session);
            
            if($ExamCookie->Last_Status == 21)
            {
                    $ExamCookie->ExamDuration = $Session->ExamDuration;
                    $LastQuestion = null;
                    
                    // Get ElapsedTime in Seconds
                    //$ElapsedTime = time() - $TimeCookie->StartTime;
                    
                    // Show Question When Called From FeedBack Table Screen!
                    if ($OrdNum || $ExamCookie->FeedQuestion) {
                        if ($OrdNum && $OrdNum > 0) {
                            $ExamCookie->FeedQuestion = $OrdNum;
                        }
                        $LastQuestion = $Questions->GetQuestion($ExamCookie->FeedQuestion);
                        
                        if($OrdNum) {
                            $ElapsedTime = time() - $TimeCookie->StartTime;
                            $LastQuestion->ElapsedTime += $ElapsedTime;
                        
                            // Update Manager For Elapsed Time
                            SendResults($ExamCookie, $Session, $LastQuestion);
                        }
                        // Update Questions
                        SendClient($ExamCookie, $Session, $Status, $Questions);
                                                
                        $LastQuestion = $Questions->GetQuestion($ExamCookie->FeedQuestion);
                    } else {
                        $LastQuestion = $Questions->GetNextNotAnswered();
                    }
                    
                    // If we have an answer the Cookie 'ans' has be set
                    if (isset($_COOKIE['ans'])) {
                        
                        $ElapsedTime = time() - $TimeCookie->StartTime;
                        // Check if we got Multi Answers
                        $isMulti = preg_match('/~/', $_COOKIE['ans']);
                        $isMatching = preg_match('/%u2524/', $_COOKIE['ans']);
                        $isFillGaps = preg_match('/%u2502/', $_COOKIE['ans']);
                        $isStaticDisplay = preg_match('/static-display/', $_COOKIE['ans']);
                        // The Grade
                        $Grade = '0.00';
                        
                        /* Two Plus Correct Answers */
                        if ($isMulti) {                            
                            $client_answers = preg_split('/~/', $_COOKIE['ans'], null, PREG_SPLIT_NO_EMPTY);
                            $correctAnswers = preg_split("/\\\\/", $ExamCookie->CorrectAns, null, PREG_SPLIT_NO_EMPTY);
                            $correct = 0;

//                            if(count($client_answers) <= count($correctAnswers)) {
//                                foreach($correctAnswers as $CAnswer) {
//                                    foreach($client_answers as $Answer) {
//                                        if($CAnswer == $Answer) {
//                                            $correct++;
//                                            break;
//                                        }
//                                    }
//                                }
//
//                                if($correct == count($client_answers)){
//                                    $GradePerAnswer = round($ExamCookie->Grade / count($correctAnswers),2);
//                                    if($correct <= count($correctAnswers)) {
//                                        $Grade = $GradePerAnswer * $correct;
//                                    } else {
//                                        $Grade = $ExamCookie->Grade;
//                                    }
//                                }
//                            }
                            
                            // WGI MARK RULES
                            /* 
                             * -> Questions With Tow Correct Replies:
                             *      For Every wrong answer, student looses 50% of his already score.
                             * -> Questions With Three Correct Replies:
                             *      For every wrong answer, student looses 33% of his already score.
                             */
                            // Check Correct Answers
                            $totalCorrectAnswers = count($correctAnswers);
                            $totalStudentAnswers = count($client_answers);
                            
                            for($i=0;$i<$totalCorrectAnswers;++$i)
                            {
                                $correctAnswer = $correctAnswers[$i];
                                for($j=0;$j<$totalStudentAnswers;++$j)
                                {
                                    $studentAnswer = $client_answers[$i];
                                    if($studentAnswer == $correctAnswer)
                                    {
                                        $correct++;
                                        break;
                                    }
                                }
                            }
                            // Calculate Mark
                            $GradePerAnswer = round($ExamCookie->Grade / $totalCorrectAnswers,2);
                            $wrong = $totalStudentAnswers - $correct;
                            // Only Correct Answers
                            if($wrong == 0)
                            {
                                $Grade = $ExamCookie->Grade;
                            }
                            // Correct And Wrong Answers
                            elseif($correct > 0 && $wrong > 0)
                            {
                                $Mark = $GradePerAnswer * $correct;
                                $Grade = $Mark - (($Mark / $totalCorrectAnswers) * $wrong);
                            }
                            // Wrong only answers just continue, grade has already be set to 0.00
                            
                        } 
                        /* Fill The Gaps */
                        elseif ($isFillGaps) {
                            
                            $correctAnswers = preg_split('/│/', $ExamCookie->CorrectAns, null, PREG_SPLIT_NO_EMPTY);
                            $client_answers = preg_split('/%u2502/', $_COOKIE['ans'], count($correctAnswers));
                            
                            // remove any delimiter from last substring.
                            $client_answers[count($client_answers)-1] = 
                                preg_replace('/%u2502/', '', $client_answers[count($client_answers)-1]);
                            
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
                                    if($correctAnswers[$i] == $clientAnswer) {
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
                        }
                        
                        // Set Question Data
                        $LastQuestion->Grade = $Grade;
                        $LastQuestion->ElapsedTime += $ElapsedTime;
                        $LastQuestion->Status = 1;

                        // Send Results to Manager.
                        SendResults($ExamCookie, $Session, $LastQuestion);
                                                
                        // Update Questions
                        SendClient($ExamCookie, $Session, $Status, $Questions);

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
                        // Show Feedback
                        $ExamCookie->FeedQuestion = null;
                        
                        // Set Current Time to Cookie
                        
                        ShowFeedback($Questions);
                        
                        $TimeCookie->StartTime = time();
                        $FeedbackTable = true;
                    }

                    // Set TimeCookie to Browser
                    $TimeCookie->SetToBrowser();
            } else {
                TerminateExam($ExamCookie, $TimeCookie);
            }
            ?>
        </div>
            <?php
            
            // Show Taskbar
            $TaskBarParams = new TaskBarParams();
            $TaskBarParams->QDescription = $QuestionObj ? $QuestionObj->QuestionText : null;
            $TaskBarParams->CardNo = $Session->CardNo;
            $TaskBarParams->ComputerName = $ExamCookie->ComputerName;
            $TaskBarParams->QNumber = $LastQuestion ? $LastQuestion->OrderNumber : null;
            $TaskBarParams->QTotal = $Questions->GetTotalQuestions();
            $TaskBarParams->FeedbackTable = $FeedbackTable;
            $TaskBarParams->QuestionID = $QuestionObj ? $QuestionObj->QID : null;
            // Testware Features
            $TaskBarParams->Showhelp = true;
            
            ShowTaskbar($TaskBarParams);
            
            // Virtual Timer
            if($ExamCookie->ExamDuration)
            {
                $timeRemain = ($ExamCookie->ExamDuration * 60) - $Questions->GetTotalElapsedTime();
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
    }

    // Set Cookie Expire After One Month from Now In Every Request
    $ExamCookie->SetToBrowser(time() + 60 * 60 * 24 * 30);
} else {
    header('Location: settings.php');
}
?>
