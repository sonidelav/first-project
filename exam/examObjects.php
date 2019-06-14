<?php

include_once __DIR__.'/../config.php';

////////////////////////////////////////////////////////////////////////////////
// Objects
//

class QuestionData {

    public $QID;
    public $ModuleID;
    public $CertID;
    public $RemaingTime;
    public $Position;
    public $TotalQuestions;
    public $Revisited;
    public $GotoIgnored;

}

class QuestionObject {

    public $CertID;
    public $DifficultyLevel;
    public $GroupID;
    public $LangID;
    public $ModuleID;
    public $NotValidForSyllabus;
    public $NotValidForVersions;
    public $QID;
    public $QuestionText;
    public $SyllabusID;

}

class QuestionActionObject {

    public $ActionID;
    public $ActionModuleID;
    public $ActionParams;
    public $ActionTime;
    public $CertID;
    public $ModuleID;
    public $OrderNumber;
    public $QID;
    public $Score;

}

class QuestionSyllabusObject {
    public $CertID;
    public $GroupID;
    public $ModuleID;
    public $QID;
    public $SyllabusID;
    public $SyllabusVersion;
}

class ClientStatusObject {

    public $Code;
    public $AutoCheck;
    public $AutoSend;
    public $EncryptionKey;
    public $EncryptionVector;
    public $ApplicationName;
    public $BuilderURL;

    /**
     * Initialize Object from XML String
     * @param string $XMLText
     * @return boolean TRUE on Success FALSE if can't found CODE tag.
     */
    public function GetFromXML($XMLText) {
        $parser = xml_parser_create();
        $values = $index = null;
        xml_parse_into_struct($parser, $XMLText, $values, $index);
        xml_parser_free($parser);

        if (array_key_exists('CODE', $index)) {
            $this->Code = $values[$index['CODE'][0]]['value'];
            $this->AutoCheck = $values[$index['AUTO_CHECK'][0]]['value'];
            $this->AutoSend = $values[$index['AUTO_SEND'][0]]['value'];
            $this->EncryptionKey = $values[$index['ENCRYPTION_KEY'][0]]['value'];
            $this->EncryptionVector = $values[$index['ENCRYPTION_VECTOR'][0]]['value'];
            $this->ApplicationName = $values[$index['APPLICATION_NAME'][0]]['value'];
            $this->BuilderURL = $values[$index['BUILDER_URL'][0]]['value'];
            return true;
        }

        return false;
    }

}

class ExamSessionObject {

    public $TestCenterID;
    public $LabID;
    public $StartDate;
    public $StartTime;
    public $UID;
    public $LastName;
    public $FirstName;
    public $FathersName;
    public $IDCardType;
    public $IDCardNo;
    public $CardNo;
    public $ExamID;
    public $QSet;
    public $Cert;
    public $ExamDuration;
    public $TestSetID;
    public $Flags;

    /**
     * Initialize Object From XML String
     * @param string $XMLText
     * @return boolean TRUE on Success FALSE if can't found EXAMSESSION tag.
     */
    public function GetFromXML($XMLText) {
        $val = $ind = null;
        $parser = xml_parser_create();
        xml_parse_into_struct($parser, $XMLText, $val, $ind);
        xml_parser_free($parser);

        if (array_key_exists('EXAMSESSION', $ind)) {
            // Create EXAM_SESSION Object From XML
            if(!Config::TestwareMode) {
                $this->TestCenterID = $val[$ind['TESTCENTERID'][0]]['value'];
                $this->LabID = $val[$ind['LABID'][0]]['value'];
                $this->FathersName = $val[$ind['FATHERSNAME'][0]]['value'];
                $this->IDCardType = $val[$ind['IDCARDTYPE'][0]]['value'];
                $this->IDCardNo = $val[$ind['IDCARDNO'][0]]['value'];
                $this->CardNo = $val[$ind['CARDNO'][0]]['value'];
                $this->TestSetID = $val[$ind['TESTSETID'][0]]['value'];
                $this->Flags = $val[$ind['FLAGS'][0]]['value'];
            }
            $this->LastName = $val[$ind['LASTNAME'][0]]['value'];
            $this->FirstName = $val[$ind['FIRSTNAME'][0]]['value'];
            $this->UID = $val[$ind['UID'][0]]['value'];
            $this->QSet = $val[$ind['QSET'][0]]['value'];
            $this->Cert = $val[$ind['CERT'][0]]['value'];
            $this->ExamID = $val[$ind['EXAMID'][0]]['value'];
            $this->ExamDuration = $val[$ind['EXAMDURATION'][0]]['value'];
            $this->StartDate = $val[$ind['STARTDATE'][0]]['value'];
            $this->StartTime = $val[$ind['STARTTIME'][0]]['value'];
            return true;
        }

        return false;
    }

}

class QuestionListObject {

    public $QID;
    public $Grade;
    public $ElapsedTime;
    public $QSet;
    public $Cert;
    public $OrderNumber;
    public $Status;
    
    public $Index;
    /**
     * Compare Object
     * @param QuestionListObject $Question
     * @return TRUE if is Equals
     * @return FALSE otherwise.
     */
    public function Equals($Question) {
        if($Question) {
            return $this == $Question;
        }
        return false;
    }
    
}

class QuestionsList {

    private $list;
    private $lastIgnoredReturn;
    private $lastNotAnsReturn;

    public function __construct() {
        $this->list = array();
        $this->lastIgnoredReturn = null;
        $this->lastNotAnsReturn = null;
    }

    /**
     * Get Question List
     * @return QuestionListObject[]
     */
    public function Items() {
        return $this->list;
    }

    /**
     * Load List From XML String
     * @param string $XMLText
     * @return true on Success 
     * @return false if can't find QUESTION tag.
     */
    public function LoadFromXML($XMLText) {
        $val = $ind = null;
        $parser = xml_parser_create();
        xml_parse_into_struct($parser, $XMLText, $val, $ind);
        xml_parser_free($parser);

        if (array_key_exists('QUESTION', $ind)) {
            $QuestionsCount = count($ind['QID']);

            for ($i = 0; $i < $QuestionsCount; $i++) {
                $NewQuestion = new QuestionListObject();
                $NewQuestion->QID = $val[$ind['QID'][$i]]['value'];
                $NewQuestion->Grade = $val[$ind['GRADE'][$i]]['value'];
                $NewQuestion->ElapsedTime = $val[$ind['ELAPSEDTIME'][$i]]['value'];
                $NewQuestion->QSet = $val[$ind['QSET'][$i]]['value'];
                $NewQuestion->Cert = $val[$ind['CERT'][$i]]['value'];
                $NewQuestion->OrderNumber = $val[$ind['ORDERNUMBER'][$i]]['value'];
                $NewQuestion->Status = $val[$ind['STATUS'][$i]]['value'];
                $NewQuestion->Index = $i+1;
                $this->list[$i] = $NewQuestion;
            }

            return true;
        }

        return false;
    }

    /**
     * Return First Not Answered Question From List
     * @return QuestionListObjet On Success 
     * @return null when not find anything.
     */
    public function GetNextNotAnswered() {
        $rItem = new QuestionListObject();
        foreach ($this->list as $item) {
            if ($item->Status == 0) {
                $rItem = $item;
                return $rItem;
            }
        }

        return null;
    }

    /**
     * Return Next Ignored Question From List
     * @return QuestionListObject On Success 
     * @return null when not find anything.
     */
    public function GetNextIgnored() {
        foreach ($this->list as $item) {
            if ($item->Status == -1) {
                if ($this->lastIgnoredReturn && $item == $this->lastIgnoredReturn) {
                    continue;
                } else {
                    $this->lastIgnoredReturn = $item;
                }

                return $this->lastIgnoredReturn;
            }
        }

        return null;
    }

    /**
     * Sum and return all 'ElapsedTime' Values.
     * @return int Total Elapsed Time
     * @return null On Error.
     */
    public function GetTotalElapsedTime() {
        $rTime = null;
        foreach ($this->list as $item) {
            $rTime += $item->ElapsedTime;
        }
        return $rTime;
    }

    /**
     * Get Total Questions
     * @return int Total Questions
     */
    public function GetTotalQuestions() {
        return count($this->list);
    }

    /**
     * Get Question By OrderNumber
     * @param int $OrderNumber
     * @return QuestionListObject Question
     * @return null If OrderNumber Not Found
     */
    public function GetQuestion($OrderNumber) {
        foreach ($this->list as $Item) {
            if ($Item->OrderNumber == $OrderNumber)
                return $Item;
        }
        return null;
    }
    
    public function GetQuestionByIndex($index)
    {
        return $this->list[$index];
    }

}

class TestwareSettings {
    public $TotalQuestions;
    public $TotalDuration;
    public $ShowHelp;
    public $ShowScore;
    
    public static function ParseXML($XML) {
        $parser = xml_parser_create();
        xml_parse_into_struct($parser, $XML, $val, $ind);
        xml_parser_free($parser);

        if(array_key_exists('SETTINGS', $ind)) {
            $new = new TestwareSettings();
            
            $new->TotalQuestions = $val[$ind['TOTALQUESTIONS'][0]]['value'];
            $new->TotalDuration = $val[$ind['TOTALDURATION'][0]]['value'];
            $new->ShowHelp = $val[$ind['SHOWHELP'][0]]['value'];
            $new->ShowScore = $val[$ind['SHOWSCORE'][0]]['value'];
            return $new;
        }
        return null;
    }
}

////////////////////////////////////////////////////////////////////////////////
// SOAP Clients
//

class BuilderClient {

    private $ServiceClient;
    private $wsdl;
    private $options;
    private $location;

    public function __construct() {
        $this->options = Config::$BUILDER_CLIENT_OPTIONS;
        $this->wsdl = Config::BUILDER_URL . Config::BUILDER_SERVICE_WSDL;
        $this->location = Config::BUILDER_URL . Config::BUILDER_LOCATION;

        $this->ServiceClient = new SoapClient($this->wsdl, $this->options);
        $this->ServiceClient->__setLocation($this->location);
    }

    /**
     * Get Question From Builder Service.
     * @param int $GroupID Group ID
     * @param int $ModuleID Module or Question Set(QSet) ID
     * @param int $CertID Cert ID
     * @param int $QID Question ID
     * @return QuestionObject
     * @throws Exception
     */
    public function GetQuestion($GroupID, $ModuleID, $CertID, $QID) {
        $params = array(
            'GroupID' => (int) $GroupID, 'ModuleID' => (int) $ModuleID,
            'CertID' => (int) $CertID, 'QID' => (int) $QID
        );

        $res = $this->ServiceClient->GetQuestion($params);

        $res_objects = (array) $res->GetQuestionResult;
        if (count($res_objects) > 0) {
            $QObj = new QuestionObject();
            $QObj = $res->GetQuestionResult->QuestionObject;
            return $QObj;
        }

        throw new Exception("Builder returns NULL question");
        return null;
    }

    /**
     * Get Question Actions From Builder Service.
     * @param int $GroupID Group ID
     * @param int $ModuleID Module or Question Set(QSet) ID
     * @param int $CertID Cert ID
     * @param int $UID User ID
     * @return QuestionActionObject[]
     * @throws Exception
     */
    public function GetQuestionActions($GroupID, $ModuleID, $CertID, $QID) {
        $params = array(
            'GroupID' => (int) $GroupID, 'ModuleID' => (int) $ModuleID,
            'CertID' => (int) $CertID, 'QID' => (int) $QID
        );

        $res = $this->ServiceClient->GetQuestionActions($params);

        //if(DEBUG){ var_dump($res); echo '</br>'; }

        $res_objects = (array) $res->GetQuestionActionsResult;
        if (count($res_objects) > 0) {
            return (array) $res->GetQuestionActionsResult->QuestionActionObject;
        }

        throw new Exception("Builder return NULL actions");
        return null;
    }

    /**
     * Get Active Question GroupID From Builder Service.
     * @return int Current Active Question Group ID
     * @throws Exception
     */
    public function GetActiveQuestionGroup() {
        $res = $this->ServiceClient->GetActiveQuestionGroup();
        //if(DEBUG){ var_dump($res); echo '</br>'; }
        if ($res) {
            return (int) $res->GetActiveQuestionGroupResult;
        }
        throw new Exception("Can't Get Active GroupID from Builder.");
    }
    
    /**
     * Get Questions And Syllabus IDs
     * @param int $GroupID
     * @param int $ModuleID
     * @param int $CertID
     * @return QuestionSyllabusObject[]
     */
    public function GetQuestionSyllabus($GroupID, $ModuleID, $CertID) {
        $params = array('GroupID'=>$GroupID, 'ModuleID'=>$ModuleID, 'CertID'=>$CertID);
        $res = $this->ServiceClient->GetQuestionSyllabus($params);
        
        if(isset($res->GetQuestionSyllabusResult->QuestionSyllabusObject)){
            if(count($res->GetQuestionSyllabusResult->QuestionSyllabusObject) > 1){
                $res_objs = (array) $res->GetQuestionSyllabusResult->QuestionSyllabusObject;
            } else {
                $res_objs[] = $res->GetQuestionSyllabusResult->QuestionSyllabusObject;
            }
            return $res_objs;
        }
        return null;        
    }

    /**
     * Show All Methods From Builder Service
     */
    public function ShowMethods() {
        $res = $this->ServiceClient->__getFunctions();
        var_dump($res);
    }

    /**
     * Get All Modules From Builder
     * @param int $GroupID
     */
    public function GetAllModules($GroupID)
    {
        $params = array('GroupID' => (int)$GroupID);
        $res = $this->ServiceClient->GetAllModules($params);
        
        var_dump($res);
    }
}

class ManagerClient {
    /** @var SoapClient */
    protected $ServiceClient;
    private $wsdl;
    private $options;
    private $location;
    private $password;
    private $username;

    public function __construct($username, $password) {
        $this->wsdl = Config::MANAGER_URL . Config::MANAGER_SERVICE_WSDL;
        $this->options = Config::$MANAGER_CLIENT_OPTIONS;
        $this->location = Config::MANAGER_URL . Config::MANAGER_LOCATION;

        $this->username = (string) $username;
        $this->password = (string) $password;

        $this->ServiceClient = new SoapClient($this->wsdl, $this->options);
        if(!Config::TestwareMode) {
            $this->ServiceClient->__setLocation($this->location);
        }
    }

    /**
     * Call Client_ACK function from Manager Service.
     * @param string $XMLText XML Text to Send
     * @return array ACK Responce
     * <pre>
     * Array['Session']   <b>Object: </b> ExamSessionObject  (EXAM_SESSION)
     * Array['Status']    <b>Object: </b> ClientStatusObject (CLIENT_STATUS)
     * Array['Questions'] <b>Object: </b> QuestionList       (QUESTION)
     * </pre>
     * @see ManagerClient::GetLastResponse()
     * @throws Exception
     */
    public function Client_ACK($XMLText) {
        $Params = array(
            'UserName' => $this->username,
            'Password' => $this->password,
            'XMLText' => $XMLText
        );
        
        if(!Config::TestwareMode) {
            $res = $this->ServiceClient->Client_ACK($Params);
        } else {
            $res = $this->ServiceClient->ACK($Params['UserName'],$Params['Password'],$Params['XMLText']);
        }
        
        if ($res) {
            if(Config::TestwareMode) {
                return $this->GetLastResponse($res);
            }
            return $this->GetLastResponse();
        } else {
            // Nothing Returned, Request Failed.
            throw new Exception('Request Client_ACK Failed.');
        }
    }

    public function Client_END($XMLText) {
        $Params = array(
            'UserName' => $this->username,
            'Password' => $this->password,
            'XMLText' => $XMLText
        );

        if(!Config::TestwareMode){
            $res = $this->ServiceClient->Client_END($Params);
        } else {
            //$res = $this->ServiceClient->END($Params['UserName'],$Params['Password'],$Params['XMLText']);
        }
        
        if($res) return $res;
    }
    /**
     * Call Client_STR frunction from Manager Service.
     * @param string $XMLText
     * @return int STR Responce
     * <pre>
     * <b>Status</b>: <b>Description</b>
     * <b>0</b>     : <i>Not Valid XML</i>
     * <b>3</b>     : <i>Session Error</i>
     * <b>4</b>     : <i>Data Not Found</i>
     * <b>5</b>     : <i>Workstation Not Found</i>
     * <b>10</b>    : <i>Stop</i>
     * <b>21</b>    : <i>Start</i>
     * </pre>
     * @see ManagerClient::LastResponse()
     */
    public function Client_STR($XMLText) {
        $Params = array(
            'UserName' => $this->username,
            'Password' => $this->password,
            'XMLText' => $XMLText
        );

        $res = $this->ServiceClient->Client_STR($Params);
        if ($res) {
            return $this->GetLastResponse();
        }
    }

    /**
     * Call Client_RES function from Manager Service.
     * @param string $XMLText XML text to send
     * @return array RES Responce
     * <pre>
     * array['CODE'] Results Code
     *     
     *              <b>Code</b>  : <b>Description</b>
     *              <b>2</b>     : <i>Student Found 0, Status IDLE</i>
     *              <b>3</b>     : <i>Exam Stop/Pause</i>
     *              <b>1</b>     : <i>OK</i>
     * 
     * array['ADDTIME'] Minutes To Add
     * </pre>
     * @see ManagerClient::GetLastResponse()
     */
    public function Client_RES($XMLText) {
        $Params = array(
            'UserName' => $this->username,
            'Password' => $this->password,
            'XMLText' => $XMLText
        );

        if(!Config::TestwareMode) {
            $res = $this->ServiceClient->Client_RES($Params);
        } else {
            $res = $this->ServiceClient->RES(
                    $Params['UserName'],$Params['Password'],$Params['XMLText']
                   );
        }
        if ($res) {
            return $this->GetLastResponse();
        }
    }

    
    /**
     * Get The Last Response.
     * @return array When ACK Called
     * 
     * Array['Session']   <b>Object: </b> ExamSessionObject  (EXAM_SESSION)<br />
     * Array['Status']    <b>Object: </b> ClientStatusObject (CLIENT_STATUS)<br />
     * Array['Questions'] <b>Object: </b> QuestionList       (QUESTION)<br />
     * 
     * @return int When STR Called<br/>
     * <pre>
     * <b>Status</b>: <b>Description</b>
     * <b>0</b>     : <i>Not Valid XML</i>
     * <b>3</b>     : <i>Session Error</i>
     * <b>4</b>     : <i>Data Not Found</i>
     * <b>5</b>     : <i>Workstation Not Found</i>
     * <b>10</b>    : <i>Stop</i>
     * <b>21</b>    : <i>Start</i>
     * </pre>
     * 
     * @return array When RES Called<br/>
     * <pre>
     * array['CODE'] Results Code
     *     
     *              <b>Code</b>  : <b>Description</b>
     *              <b>2</b>     : <i>Student Found 0, Status IDLE</i>
     *              <b>3</b>     : <i>Exam Stop/Pause</i>
     *              <b>1</b>     : <i>OK</i>
     * 
     * array['ADDTIME'] Minutes To Add
     * </pre>
     */
    public function GetLastResponse($res=null) {

        $XMLResponse = $this->ServiceClient->__getLastResponse();
        
        if($res) $XMLResponse = $res;
        
        if ($XMLResponse) {
            $parser = xml_parser_create();
            $val = $ind = null;

            xml_parse_into_struct($parser, $XMLResponse, $val, $ind);
            xml_parser_free($parser);
            
            if (array_key_exists('CLIENT_ACKRESULT', $ind)) {
                $XMLText = $val[$ind['CLIENT_ACKRESULT'][0]]['value'];

                if(Config::TestwareMode) $XMLText = $XMLResponse;
                
                $rObjects = array();
                $SessionObj = new ExamSessionObject();
                $StatusObj = new ClientStatusObject();
                $QuestionList = new QuestionsList();
                $SettingsObj = TestwareSettings::ParseXML($XMLText);
                
                $Session = $SessionObj->GetFromXML($XMLText);
                $Status = $StatusObj->GetFromXML($XMLText);
                $QList = $QuestionList->LoadFromXML($XMLText);

                if($SettingsObj) {
                    $rObjects['Settings'] = $SettingsObj;
                }
                
                if($Session) {
                    $rObjects['Session'] = $SessionObj;
                }
                
                if($Status) {
                    $rObjects['Status'] = $StatusObj;
                }
                
                if($QList) {
                    $rObjects['Questions'] = $QuestionList;
                }

                return $rObjects;
            } elseif (array_key_exists('CLIENT_STRRESULT', $ind)) {

                $XMLText = $val[$ind['CLIENT_STRRESULT'][0]]['value'];
                $parser = xml_parser_create();

                xml_parse_into_struct($parser, $XMLText, $val, $ind);
                xml_parser_free($parser);

                return (int) $val[$ind['CODE'][0]]['value'];
            } elseif (array_key_exists('CLIENT_RESRESULT', $ind)) {
                $XMLText = $val[$ind['CLIENT_RESRESULT'][0]]['value'];
                $parser = xml_parser_create();

                xml_parse_into_struct($parser, $XMLText, $val, $ind);
                xml_parser_free($parser);

                $rArray = array();
                $rArray['CODE'] = $val[$ind['CODE'][0]]['value'];
                $rArray['ADDTIME'] = $val[$ind['ADD_TIME'][0]]['value'];

                return $rArray;
            } else {
                // Unhandled Responce
                //var_dump($XMLResponse);
                var_dump($val);
                var_dump($ind);
            }
        } else {
            // No Responce From Manager.
            echo 'No Responce';
        }
        return null;
    }
    
    /**
     * Show All Methods From Manager Service
     */
    public function ShowMethods() {
        $res = $this->ServiceClient->__getFunctions();
        var_dump($res);
    }
}

////////////////////////////////////////////////////////////////////////////////
// Cookies
//

class ExamCookieObject {

    public $TestCenterID;
    public $LabID;
    public $ComputerName;
    public $GUID;
    public $ExamDate;
    public $ExamTime;
    public $CorrectAns;
    public $Grade;
    public $UID;
    public $Name;
    public $Last_Status;
    public $STR_Called;
    public $FeedQuestion;
    public $ExamDuration;
    public $LastQuestionOrder;
    public $UserAns;

    public function __construct() {
        $this->Name = 'exam';
        $this->Last_Status = null;
        $this->CorrectAns = null;
        $this->Grade = null;
        $this->STR_Called = false;
        $this->FeedQuestion = null;
        $this->ExamDuration = null;
        $this->LastQuestionOrder = null;
    }

    public function __toString() {
        return (string)
                $this->TestCenterID . '~' .
                $this->LabID . '~' .
                $this->ComputerName . '~' .
                $this->GUID . '~' .
                $this->Last_Status . '~' .
                $this->ExamDate . '~' .
                $this->ExamTime . '~' .
                $this->CorrectAns . '~' .
                $this->Grade . '~' .
                $this->STR_Called . '~' .
                $this->UID . '~' .
                $this->FeedQuestion.'~'.
                $this->ExamDuration.'~'.
                $this->LastQuestionOrder.'~'.
                $this->UserAns;
    }

    /**
     * Get Cookie Object From Client Browser
     * @param string $CookieName
     * @return TRUE On Success 
     * @return FALSE When cookie doesn't exist.
     */
    public function GetFromBrowser() {
        // Get Info From Cookie in to Array
        if (isset($_COOKIE[$this->Name])) {
            $arrCookie = preg_split('/~/', $_COOKIE[$this->Name]);

            $this->TestCenterID = $arrCookie[0];
            $this->LabID = $arrCookie[1];
            $this->ComputerName = $arrCookie[2];
            $this->GUID = $arrCookie[3];
            $this->Last_Status = $arrCookie[4];
            $this->ExamDate = $arrCookie[5];
            $this->ExamTime = $arrCookie[6];
            $this->CorrectAns = $arrCookie[7];
            $this->Grade = $arrCookie[8];
            $this->STR_Called = $arrCookie[9];
            $this->UID = $arrCookie[10];
            $this->FeedQuestion = $arrCookie[11];
            $this->ExamDuration = $arrCookie[12];
            $this->LastQuestionOrder = $arrCookie[13];
            $this->UserAns = $arrCookie[14];
            return true;
        }
        return false;
    }

    /**
     * Save Cookie Object to Client Browser
     * @param int $ExpireTime The Expire Time of Cookie
     * @return TRUE On Success 
     * @return FALSE On Failed
     */
    public function SetToBrowser($ExpireTime) {
//        if(isset($_COOKIE[$this->Name]))
//        {
//            $_COOKIE[$this->Name] = (string)$this;
//            return true;
//        } else {
        return setcookie($this->Name, $this, $ExpireTime);
//        }
    }

}

class TimeCookieObject {

    public $StartTime;
    public $Name;

    public function __construct() {
        $this->StartTime = null;
        $this->Name = 'time';
    }

    public function GetFromBrowser() {
        if (isset($_COOKIE['time'])) {
            $arrCookie = preg_split('/~/', $_COOKIE['time']);

            $this->StartTime = $arrCookie[0];

            return true;
        }
        return false;
    }

    public function __toString() {
        return (string) $this->StartTime . '~';
    }

    public function SetToBrowser() {
//        if($this->Exist())
//        {
//          $_COOKIE[$this->Name] = (string) $this;
//          return true;
//        } else
        return setcookie($this->Name, (string) $this, 0);
    }

    public function Delete() {
        if (isset($_COOKIE[$this->Name])) {
            setcookie($this->Name, "", time() - 3600);
            return true;
        }
        return false;
    }

    public function Exist() {
        return isset($_COOKIE[$this->Name]);
    }

}

?>
