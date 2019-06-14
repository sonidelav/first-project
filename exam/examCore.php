<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once __DIR__.'/../config.php';
include_once __DIR__.'/examObjects.php';
include_once __DIR__.'/../HTMLRenderer.php';
include_once __DIR__.'/examlib/CCrypter.php';

/*
 * Defines
 */

// -----------------------------------------------------------------------------
// Enums
// -----------------------------------------------------------------------------

class QuestionType {

    const DRAG_N_DROP = 891;
    const FILL_COLOR = 890;
    const MULTI_TWOFILES_ONE_CORRECT = 893;
    const STATIC_DISPLAY = 894;
    const FILL_GAPS = 895;
    const MULTIPIC_FILE_ONE_CORRECT = 896;
    const MULTI_FILE_ONE_CORRECT = 897;
    const MATCHING_GROUPS = 898;
    const MULTI_TWOPLUS_CORRECT = 993;
    const MATCHING = 996;                   
    const MULTIPIC_ONE_CORRECT = 997;
    const MULTI_ONE_CORRECT = 998;
    
    const GET_GRADE = 999;
    const SET_TITLE = 990;

}

class Delimiters {
    const PARAMS_WAVE = '~';
    const PARAMS_179 = '│';
    const PARAMS_180 = '┤';
    const PARAMS_182 = '╢';
}

//------------------------------------------------------------------------------
//  Exam Core Methods
//------------------------------------------------------------------------------

/**
 * @param QuestionData $QuestionData
 */
function ShowQuestion(&$QuestionData, &$ExamCookie, &$ShowAnswers) {
    $ret = LoadQuestionData(
            $QuestionData->ModuleID, $QuestionData->CertID, 
            $QuestionData->QID, $ExamCookie, TRUE, $ShowAnswers
    );
    return $ret;
}

/**
 * - Loading Question Data From Builder Server
 * @param int $ModuleID Module ID
 * @param int $CertID Certification ID
 * @param int $QID Question ID
 * @param ExamCookieObject $ExamCookie Examination Cookie
 * @param boolean $HandleActions Handle or not actions from builder.
 * @param boolean $ShowAnswers Show Correct Answers On Rendering
 * @return \QuestionObject|\array|\null Returned Object
 * @desc
 * <pre>
 * This Array returns when $HandleActions is false
 * Array['MaxGrade'] : Maximum Question Grade.
 * Array['Question'] : QuestionObject
 * </pre>
 */
function LoadQuestionData($ModuleID, $CertID, $QID, $ExamCookie, $HandleActions, $ShowAnswers, $PreviewGroupId = null) {

    try {
        
        $builderClient = new BuilderClient();
        $GroupID = null;
        
        if(!$PreviewGroupId) {
            if(!Config::BUILDER_GROUPID)
                $GroupID = $builderClient->GetActiveQuestionGroup();
            else
                $GroupID = Config::BUILDER_GROUPID;
        } else {
            $GroupID = $PreviewGroupId;
        }
        
        $Question = $builderClient->GetQuestion($GroupID, $ModuleID, $CertID, $QID);
        $Actions = $builderClient->GetQuestionActions($GroupID, $ModuleID, $CertID, $QID);

        if(!$HandleActions) {
            $rArray = array();
            if(Config::TestwareMode) {
                $max = count($Actions);
                for($i=0;$i<$max;$i++) {
                    if($Actions[$i]->ActionID == QuestionType::GET_GRADE) {
                        $rArray['MaxGrade'] = $Actions[$i]->Score;
                        break;
                    }
                }
            }
            $rArray['Question'] = $Question;
            return $rArray;
        }
        
        // Action Handler
        foreach ($Actions as $Action) {
            switch ($Action->ActionID) {
                // FILL THE GAPS
                case QuestionType::FILL_GAPS:
                    // Params[0] GapText
                    // Params[1] Correct Answers
                    // Params[2] File
                    $Params = GetParams($Action->ActionParams);
                    
                    $answers = explode(Delimiters::PARAMS_179, $Params[1]);
                    HTMLRenderer::Render_FillGaps($Params[0], $answers, $Params[2], $Question, $ShowAnswers);
                    
                    if($ExamCookie) {
                        $ExamCookie->CorrectAns = $Params[1];
                    }
                    break;
                    
                // GRADE OF QUESTION
                case QuestionType::GET_GRADE:
                    if($ExamCookie)
                        $ExamCookie->Grade = $Action->Score;
                    break;
                
                // MATCHING
                case QuestionType::MATCHING:
                    // Params[..] Text | Answer
                    $Params = GetParams($Action->ActionParams);
                    $answers = array();
                    $correct = null;
                    // Strip Empty Params
                    for($i=0;$i<count($Params);$i++)
                    {
                        if($Params[$i] != '')
                        {
                            $answers[] = $Params[$i];
                            $splited = explode(Delimiters::PARAMS_179, $Params[$i]);
                            $correct .= $splited[count($splited)-1].Delimiters::PARAMS_179;
                        }
                    }
                    // Render HTML Code
                    HTMLRenderer::Render_Matching($answers, $ShowAnswers);
                    
                    if($ExamCookie) {
                        $ExamCookie->CorrectAns = $correct;
                    }
                    break;
                    
                // MATCHING GROUPS
                case QuestionType::MATCHING_GROUPS:
                    // Params[n] Group
                    // Params[n+1] Answers
                    $Params = explode(Delimiters::PARAMS_179,$Action->ActionParams);
                    HTMLRenderer::Render_MatchingGroup($Params, $ShowAnswers);

                    $patterns = array(Delimiters::PARAMS_179,Delimiters::PARAMS_WAVE);
                    $replace  = array('%u2562','%u2524');
                    
                    $correct = str_replace($patterns, $replace, $Action->ActionParams);
                    
                    if($ExamCookie) {
                        $ExamCookie->CorrectAns = $correct;
                    }
                    break;

                // MULTI PICTURES WITH MEDIA FILE - ONE CORRECT ANSWER
                case QuestionType::MULTIPIC_FILE_ONE_CORRECT:
                    // Params[0-3] Picture Answers
                    // Params[4]   Media File
                    // Params[5]   Correct Answer Index
                    $Params = GetParams($Action->ActionParams);
                    $answers = array();
                    $file = $Params[4];
                    $correct = $Params[5];
                    
                    // Strip Empty Answers
                    for($i=0;$i<4;$i++) {
                        if($Params[$i] != '')
                            $answers[] = $Params[$i];
                    }
                    
                    // Render HTML Code
                    HTMLRenderer::Render_MultiPicFile_OneCorrect($answers, $Question, $file, $ShowAnswers, $correct);
                    
                    if($ExamCookie) {
                        $ExamCookie->CorrectAns = $correct;
                    }
                    break;
                    
                // MULTI PICTURE - ONE CORRECT
                case QuestionType::MULTIPIC_ONE_CORRECT:
                    // Params[0-3] Picture Answers
                    // Params[4] Correct Answer
                    $Params = GetParams($Action->ActionParams);
                    $answers = array();
                    $correct = $Params[4];
                    //Strip Empty Answers
                    for($i=0;$i<4;$i++)
                    {
                        if($Params[$i] != '')
                            $answers[] = $Params[$i];
                    }
                    // Render HTML Code
                    HTMLRenderer::Render_MultiPicOneCorrect($answers, $Question, $ShowAnswers, $correct);
                    
                    if($ExamCookie) {
                        $ExamCookie->CorrectAns = $correct;
                    }
                    break;

                case QuestionType::MULTI_FILE_ONE_CORRECT:
                    // Params[0-4] Answers
                    // Params[5] File
                    // Params[6] Correct Answer
                    $Params = GetParams($Action->ActionParams);
                    $answers = array();
                    $file = $Params[5];
                    $correct = $Params[6];
                    /* See if we have empty string parameter */
                    for ($i = 0; $i < 5; $i++) {
                        if ($Params[$i] != '')
                            $answers[] .= $Params[$i];
                    }
                    /* Render HTML Code */
                    HTMLRenderer::Render_MultiFileOneCorrect($answers, $Question, $file, $ShowAnswers, $correct);

                    if($ExamCookie) {
                        $ExamCookie->CorrectAns = $correct;
                    }
                    break;

                case QuestionType::MULTI_ONE_CORRECT:
                    // Params[0-4] Answers
                    // Params[5] Correct Answer
                    $Params = GetParams($Action->ActionParams);
                    $answers = array();
                    $correct = $Params[5];
                    /* See if we have empty string parameter */
                    for ($i = 0; $i < 5; $i++) {
                        if ($Params[$i] != '')
                            $answers[] = $Params[$i];
                    }
                    /* Render HTML Code */
                    HTMLRenderer::Render_MultiOneCorrect($answers, $ShowAnswers, $correct);

                    if($ExamCookie) {
                        $ExamCookie->CorrectAns = $correct;
                    }
                    break;

                case QuestionType::MULTI_TWOFILES_ONE_CORRECT:
                    // Params[0-4] Answers
                    // Params[5-6] Files
                    // Params[7] Correct Answer
                    $Params = GetParams($Action->ActionParams);
                    $answers = array();
                    $files = array();
                    $correct = $Params[7];
                    // Strip empty answers
                    for($i=0;$i<5;$i++)
                    {
                        if($Params[$i] != '')
                            $answers[] = $Params[$i];
                    }
                    $files[0] = $Params[5];
                    $files[1] = $Params[6];
                    $correct = $Params[7];
                    // Render HTML Code
                    HTMLRenderer::Render_MultiTwoFilesOneCorrect(
                            $answers, $Question, $files, $ShowAnswers, $correct
                    );
                    
                    if($ExamCookie) {
                        $ExamCookie->CorrectAns = $correct;
                    }
                    break;

                case QuestionType::MULTI_TWOPLUS_CORRECT:
                    // Params[0-4] Answers
                    // Params[5] Correct Answer
                    $Params = GetParams($Action->ActionParams);
                    $answers = array();
                    $correct = $Params[5];
                    /* See if we have empty string parameters */
                    for($i=0; $i<5; $i++)
                    {
                        if($Params[$i] != '')
                            $answers[] = $Params[$i];
                    }
                    /* Render HTML Code */
                    HTMLRenderer::Render_MultiTwoPlusCorrect($answers, $ShowAnswers, $correct);
                    if($ExamCookie) {
                        $ExamCookie->CorrectAns = $correct;
                    }
                    break;

                case QuestionType::SET_TITLE:
                    /* Render HTML Code */
                    HTMLRenderer::Render_Title($Action->ActionParams);
                    break;

                case QuestionType::STATIC_DISPLAY:
                    $file = explode(Delimiters::PARAMS_WAVE,$Action->ActionParams);
                    $file = $file[0];
                    /* Render HTML Code */
                    HTMLRenderer::Render_StaticDisplay($file, $Question);
                    break;
                
                // DRAG N DROP
                // Params:
                //          [0] = MEDIA FILE
                //          [1] = BACKGROUND FILE
                //          [2] = CELL SIZE
                //          [3..10] = PICTURE | CORRECT CELL POSITION
                case QuestionType::DRAG_N_DROP:
                    $params = GetParams($Action->ActionParams);
                    //var_dump($params);
                    
                    $file = $params[0];
                    $background = $params[1];
                    $cellsize = $params[2];
                    
                    $length = count($params);
                    $pictures = array();
                    $regex = '/'.Delimiters::PARAMS_179.'/';
                    
                    for($i=3;$i<$length;++$i){
                        if(preg_match($regex, $params[$i])){
                            // Picture | Positions
                            $split = explode(Delimiters::PARAMS_179,$params[$i]);
                            $filename = $split[0];
                            $positions = $split[1];
                            if(!empty($filename)) {
                                $pictures[$filename]=$positions;
                            }
                        }
                    }
                    
                    HTMLRenderer::Render_DragNDrop($Question, $file, $background, $cellsize, $pictures, $ShowAnswers);
                    
                    if($ExamCookie) {
                        $array2str = '';
                        foreach($pictures as $filename => $positions){
                            $array2str .= $filename.Delimiters::PARAMS_179.$positions.'#'; 
                        }
                        //echo 'Saved Answers: '.$array2str.'</br>';
                        $ExamCookie->CorrectAns = $array2str;
                    }
                    break;
                
                // FILL COLOR
                // Params:
                //          [0] = MEDIA FILE
                //          [1] = background image
                //          [2..12] = Color | Pixel Check
                case QuestionType::FILL_COLOR:
                    $params = GetParams($Action->ActionParams);
                    $file = $params[0];
                    $background = $params[1];
                    $length = count($params);
                    $colors = array();
                    $pixels = array();
                    $regex = '/'.Delimiters::PARAMS_179.'/';
                    
                    for($i=2;$i<$length;++$i){ 
                        if(preg_match($regex, $params[$i])){
                            // Color : Pixel(x,y);
                            $split = explode(Delimiters::PARAMS_179,$params[$i]);
                            $name = $split[0];
                            $pixel = $split[1];
                            if(!empty($name)){
                                $colors[] = array('name'=>$name,'position'=>$pixel);
                                $pixels[] = $pixel;
                            }
                        }
                    }
                    
                    //echo '<div style="position:fixed;left:0px;top:0px;width:400px;background:#000000;color:white;z-index:9999;">';
                    //echo '<pre>'.print_r(json_encode($colors),true).'</pre></div>';
                    
                    HTMLRenderer::Render_FillColor($Question, $file, $background, $pixels, $ShowAnswers);
                    
                    if($ExamCookie) {
                        $ExamCookie->CorrectAns = json_encode($colors);
                    }
                    break;
                    
                default:
                    var_dump($Action);
                    HTMLRenderer::Render_NotAvailable();
                    break;
            }
        }
        return $Question;
    } catch (Exception $ex) {
        echo $ex->getMessage();
        return null;
    }
}

/**
 * Split ActionParams String to Array
 * @param string $args Action Parameters
 * @return array Splited Parameters
 */
function GetParams($args) {
    return explode(Delimiters::PARAMS_WAVE, $args);
}

/**
 * Builds Client_ACK XMLText
 * @param string $GUID
 * @param int $TestCenterID
 * @param int $LabID
 * @param string $ComputerName
 * @return string XMLText
 */
function Builder_Client_XML($GUID, $TestCenterID, $LabID, $ComputerName) {
    $Date = date("Ymd");
    $Time = date("H:i:s");

    $XMLText = "
        <CLIENT>
        <COMPUTERNAME>$ComputerName</COMPUTERNAME>
        <OSVERSION></OSVERSION>
        <APPVERSIONS></APPVERSIONS>
        <WEBBROWSERVERSION></WEBBROWSERVERSION>
        <DATE>$Date</DATE>
        <TIME>$Time</TIME>
        <TESTCENTERID>$TestCenterID</TESTCENTERID>
        <LABID>$LabID</LABID>
        <GUID>$GUID</GUID>
        <OSLANG></OSLANG>
        <APPLANG></APPLANG>
        </CLIENT>
    ";

    return $XMLText;
}

/**
 * Builds Client_STR XMLText
 * @param string $ComputerName
 * @param int $TestCenterID
 * @param int $LabID
 * @param int $GUID
 * @param int $ExamID
 * @param int $UID
 * @param int $QSET
 * @param int $TestSetID
 * @return string XMLText
 */
function Builder_Start_XML($ComputerName, $TestCenterID, $LabID, $GUID, $ExamID, $UID, $QSET, $TestSetID) {
    $Date = date("Ymd");
    $Time = date("H:i:s");

    $XMLText = "
        <START>
        <COMPUTERNAME>$ComputerName</COMPUTERNAME>
        <DATE>$Date</DATE>
        <TIME>$Time</TIME>
        <TESTCENTERID>$TestCenterID</TESTCENTERID>
        <LABID>$LabID</LABID>
        <GUID>$GUID</GUID>
        <SESSIONID>$ExamID</SESSIONID>
        <CANDIDATEID>$UID</CANDIDATEID>
        <MODULEID>$QSET</MODULEID>
        <TESTSETID>$TestSetID</TESTSETID>
        </START>
        ";

    return $XMLText;
}

/**
 * Builds Client_END XML Text
 * @param string $ComputerName
 * @param int $TestCenterID
 * @param int $LabID
 * @param int $GUID
 * @return string XMLText
 */
function Builder_End_XML($ComputerName, $TestCenterID, $LabID, $GUID) {
    $Date = date("Ymd");
    $Time = date("H:i:s");

    $XMLTest = "
        <END>
        <COMPUTERNAME>$ComputerName</COMPUTERNAME>
        <DATE>$Date</DATE>
        <TIME>$Time</TIME>
        <TESTCENTERID>$TestCenterID</TESTCENTERID>
        <LABID>$LabID</LABID>
        <GUID>$GUID</GUID>
        </END>
        ";

    return $XMLTest;
}

/**
 * Builds Client_RES XML Text
 * @param ExamCookieObject $ExamCookie
 * @param ExamSessionObject $Session
 * @param QuestionListObject $QuestionListItem
 */
function Builder_Result_XML(&$ExamCookie, &$Session, &$QuestionListItem, $Status) {

    $Date = date("Ymd");
    $Time = date("H:i:s");

    $EXAM_SESSION_XML = "
        <RESULTS>
        <EXAMSESSION>
            <COMPUTERNAME>$ExamCookie->ComputerName</COMPUTERNAME>
            <GUID>$ExamCookie->GUID</GUID>
            <TESTCENTERID>$Session->TestCenterID</TESTCENTERID>
            <LABID>$Session->LabID</LABID>
            <STARTDATE>$Session->StartDate</STARTDATE>
            <EXAMTIME>$Session->StartTime</EXAMTIME>
            <STARTTIME>$Time</STARTTIME>
            <ENDTIME>$Time</ENDTIME>
            <ACTUALDURATION></ACTUALDURATION>
            <STATUS>$Status</STATUS>
            <EXAMID>$Session->ExamID</EXAMID>
            <UID>$Session->UID</UID>
            <QSET>$Session->QSet</QSET>
            <CERT>$Session->Cert</CERT>
            <TESTSETID>$Session->TestSetID</TESTSETID>
            <MARK></MARK>
            <TOTALQUESTIONS></TOTALQUESTIONS>
        </EXAMSESSION>
        <QUESTION>
            <QID>$QuestionListItem->QID</QID>
            <GRADE>$QuestionListItem->Grade</GRADE>
            <ELAPSEDTIME>$QuestionListItem->ElapsedTime</ELAPSEDTIME>
            <STATUS>$QuestionListItem->Status</STATUS>
            <CERT>$QuestionListItem->Cert</CERT>
            <UANSWER>$ExamCookie->UserAns</UANSWER>
            <QANSWER>$ExamCookie->CorrectAns</QANSWER>
        </QUESTION>
        </RESULTS>
        ";

    return $EXAM_SESSION_XML;
}

/**
 * Send Client Acknowledge
 * @param ExamCookieObject $ExamCookie  Examination Cookie
 * @param ExamSessionObject $Session Exam Session *
 * @param ClientStatusObject $Status Client Status *
 * @param QuestionList $Questions QSET Questions *
 * @param TestwareSettings $Settings Examination Settings *
 * @desc Note: Params with (*) ReInitialized After Call this Function!.
 */
function SendClient(&$ExamCookie,&$Session,&$Status,&$Questions,&$Settings=null)
{
    try {
    $XMLText = Builder_Client_XML(
            $ExamCookie->GUID, 
            $ExamCookie->TestCenterID, 
            $ExamCookie->LabID, 
            $ExamCookie->ComputerName
     );  
    
    $client = new ManagerClient(Config::MANAGER_USERNAME, Config::MANAGER_PASSWORD);
    $response = $client->Client_ACK($XMLText);
    
    $Session = isset($response['Session']) ? $response['Session'] : null;
    $Status = isset($response['Status']) ? $response['Status'] : null;
    $Questions = isset($response['Questions']) ? $response['Questions'] : null;
    $Settings = isset($response['Settings']) ? $response['Settings'] : null;
    } catch (Exception $ex)
    {
        ShowAlert("Error: ".$ex->getMessage());
    }
}

/**
 * Send Client Start
 * @param ExamCookieObject $ExamCookie  Examination Cookie
 * @param ExamSessionObject $Session    Examination Session
 * @param TimeCookieObject $TimeCookie  Time Keeper Cookie
 */
function SendStart(&$ExamCookie, &$Session)
{
    try {
        $XMLText = Builder_Start_XML(
                $ExamCookie->ComputerName,
                $ExamCookie->TestCenterID, 
                $ExamCookie->LabID, 
                $ExamCookie->GUID, 
                $Session->ExamID, 
                $Session->UID, 
                $Session->QSet, 
                $Session->TestSetID
        );

        $client = new ManagerClient(Config::MANAGER_USERNAME, Config::MANAGER_PASSWORD);

        $response = $client->Client_STR($XMLText);
    
        //ShowAlert("Debug: Last_Status = ".$ExamCookie->Last_Status.", STR_Response = ".$response);
        
        switch($response)
        {
            case 0:
                $ExamCookie->Last_Status = -1;
                throw new Exception("Not Valid XML",0); 
                break;
            case 4:
                $ExamCookie->Last_Status = 4;
                throw new Exception("Data Not Found",4); 
                break;
            case 5:
                $ExamCookie->Last_Status = 5;
                throw new Exception("Workstation Not Found",5); 
                break;
            
            case 10: // Exam Stoped
                $ExamCookie->Last_Status = 10;
                $ExamCookie->STR_Called = false;
                break;
            case 3: // Session Error - Wait For Start from Manager                
                if(($ExamCookie->Last_Status < 21 && $ExamCookie->Last_Status > 1) || $ExamCookie->Last_Status == 0) {
                    $ExamCookie->Last_Status = 3;
                    $ExamCookie->STR_Called = false;
                    break; // Stop Here
                }
            case 21: // Exam Started - Continue
                $ExamCookie->STR_Called = true;
                $ExamCookie->Last_Status = 21;
                break;
        }
    } catch (Exception $ex) {
        ShowAlert("Error: ".$ex->getMessage());
    }
}

/**
 * Send Client Results
 * @param ExamCookieObject $ExamCookie Examination Cookie.
 * @param ExamSessionObject $Session   Studient Session.
 * @param QuestionListObject $Question Question to Update.
 */
function SendResults(&$ExamCookie, &$Session, &$Question)
{
    try {
        $XMLText = Builder_Result_XML(
                $ExamCookie, $Session, $Question, 5
        );
        
        $client = new ManagerClient(Config::MANAGER_USERNAME, Config::MANAGER_PASSWORD);
        $response = $client->Client_RES($XMLText);
        
        //ShowAlert("Debug: Last_Status = ".$ExamCookie->Last_Status.", RES_Res = ".$response['CODE']);
        if(isset($response['CODE'])){
            switch($response['CODE']) {
                case 3: // Exam Stop-Pause
                    $ExamCookie->Last_Status = 3;
                    break;
                case 2: // Status IDLE
                    $ExamCookie->Last_Status = 2;
                    break;
                case 1: // Continue
                    $ExamCookie->Last_Status = 1;
                    break;
            }
        }
        
    } catch(Exception $ex) {
        ShowAlert("Error: ".$ex->getMessage());
    }
}

/**
 * Send Client Finish Results
 * @param ExamCookieObject $ExamCookie Examination Settings Cookie.
 * @param ExamSessionObject $Session   Student Session.
 * @param QuestionListObject $Question Question to Update. *
 * @desc Note*: Question is just to fill the XML for avoid errors.
 */
function SendFinish(&$ExamCookie, &$Session, &$Question)
{
    try {
        $XMLText = Builder_Result_XML(
                $ExamCookie, $Session, $Question, 6
        );
        $client = new ManagerClient(Config::MANAGER_USERNAME, Config::MANAGER_PASSWORD);
        $responce = $client->Client_RES($XMLText);

        if($responce['CODE']) {
            switch($responce['CODE']) {
                case 3: // Exam Stop-Pause
                    $ExamCookie->Last_Status = 3;
                    break;
                case 2: // Status IDLE
                    $ExamCookie->Last_Status = 2;
                    break;
                case 1: // Exam Finished - Continue
                    $ExamCookie->Last_Status = 1;
                    break;
            }
        }

        $XMLText = Builder_End_XML(
                $ExamCookie->ComputerName, 
                $ExamCookie->TestCenterID,
                $ExamCookie->LabID, 
                $ExamCookie->GUID
        );
        $client->Client_END($XMLText);
    } 
    catch(Exception $ex)
    {
        ShowAlert("Error: ".$ex->getMessage());
    }
}

/**
 * Exit from exam
 * @param ExamCookieObject $ExamCookie
 * @param TimeCookieObject $TimeCookie
 */
function TerminateExam(&$ExamCookie, &$TimeCookie) {
    $ExamCookie->CorrectAns = null;
    $ExamCookie->Grade = null;
    //$ExamCookie->Last_Status = 10;
    $ExamCookie->ExamDuration = null;
    
    $ExamCookie->SetToBrowser(time() + 60 * 60 * 24 * 30);
    $TimeCookie->Delete();
    if(isset($_COOKIE['ans'])) {setcookie('ans',null, time() - 3600);}
    
    header("Location: index.php");
}

function ShowAlert($message)
{
    echo "<script>
                    alert('".$message."');
          </script>";
}

/**
 * Get Encrypted Preview Params
 * @return array Preview Params
 */
function getEncryptedParams(){
    if(isset($_REQUEST['code'])){
        
        $hash_str = urldecode($_REQUEST['code']);
        
        $str = CCrypter::decrypt($hash_str, Config::CRYPT_KEY, Config::CRYPT_IV);
        
        if(preg_match('/(?=LG\=.*)(?=.*QID\=.*)(?=.*MID\=.*)(?=.*CID\=.*)(?=.*GID\=.*)(?=.*PS\=.*)/', $str)) {
            
            $splited = explode('&',$str);
            $length = count($splited);
            $params=null;
            for($i=0;$i<$length;++$i){
                $line = $splited[$i];
                $split = explode('=',$line);
                $name = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $split[0]);
                $value = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $split[1]);
                
                if($name == 'PS') { $value = substr($value,0,strlen(Config::PREVIEW_PASSWORD)); }
                
                $params[$name]=$value;
            }
            
            return $params;
        }
    }
    return null;
}

/**
 * Check if string is a json object
 * @param string $str
 * @return boolean
 */
function isJSON($str){
    json_decode($str);
    return (json_last_error() == JSON_ERROR_NONE);
}
?>
