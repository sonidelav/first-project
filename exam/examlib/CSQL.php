<?php
/**
 * Builder SQL Client Class
 */
class CBuilderSQLClient {
    /**
     * @var mysqli MySQLi Link
     */
    protected $_mysqli;
    
    public function __construct($host, $username, $password, $database) {
        $this->_mysqli = new mysqli($host, $username, $password, $database);
        if(mysqli_connect_error()){
            throw new Exception('
                    SQL Connection Error ('.mysqli_connect_errno().') '.mysqli_connect_error(),
                    mysqli_connect_errno()
            );
        }
    }   
    
    /**
     * Get Question From Builder SQL Database
     * @param integer $GroupID
     * @param integer $ModuleID
     * @param integer $CertID
     * @param integer $QID
     * @return QuestionObject <b>null</b> if not question found in database
     */
    public function GetQuestion($GroupID, $ModuleID, $CertID, $QID)
    {        
        $query = 'SELECT * FROM `question` WHERE '.
                "`GroupID`=$GroupID AND ".
                "`ModuleID`=$ModuleID AND ".
                "`CertID`=$CertID AND ".
                "`QID`=$QID ";
        $query = $this->_mysqli->escape_string($query);
        
        /* @var $result mysqli_result */
        $result = $this->_mysqli->query($query);
        
        if($result){
            if($result->num_rows > 0){
                $obj = $result->fetch_object();
            } else {
                $obj = null;
            }
            $result->close();
            return $obj;
        }
        
        $this->ReportError();
        
        return null;
    }
    
    /**
     * Get Question Actions From Bulder SQL Database
     * @param integer $GroupID
     * @param integer $ModuleID
     * @param integer $CertID
     * @param integer $QID
     * @return QuestionActionObject[] <b>NULL</b> or empty <b>array</b> if not actions found!
     */
    public function GetQuestionActions($GroupID, $ModuleID, $CertID, $QID)
    {
        $query = 'SELECT * FROM `questionaction` WHERE '.
                "`GroupID`=$GroupID AND ".
                "`ModuleID`=$ModuleID AND ".
                "`CertID`=$CertID AND ".
                "`QID`=$QID";
        $query = $this->_mysqli->escape_string($query);
        /* @var $result mysqli_result */
        $result = $this->_mysqli->query($query);
        
        if($result){
            $retObjs = array();
            if($result->num_rows > 0){
                while($obj = $result->fetch_object()){
                    $retObjs[] = $obj;
                }
            }
            $result->close();
            return $retObjs;
        }
        
        $this->ReportError();
        
        return null;
    }
    
    /**
     * Get Question Syllabus from builder SQL Database
     * @param integer $GroupID
     * @param integer $ModuleID
     * @param integer $CertID
     * @return QuestionSyllabusObject[] <b>NULL</b> or empty <b>array</b> if not syllabus found.
     */
    public function GetQuestionSyllabus($GroupID, $ModuleID, $CertID)
    {
        $query = 'SELECT * FROM `questionsyllabus` WHERE '.
                "`GroupID`=$GroupID AND".
                "`ModuleID`=$ModuleID AND".
                "`CertID`=$CertID ";
        $query = $this->_mysqli->escape_string($query);
        /* @var $result mysqli_result */
        $result = $this->_mysqli->query($query);
        
        if($result){
            $retObjs = array();
            if($result->num_rows > 0){
                while($obj = $result->fetch_object()){
                    $retObjs[] = $obj;
                }
            }
            $result->close();
            return $retObjs;
        }
        
        $this->ReportError();
        
        return null;
    }
    
    /**
     * in any error throws a exception to catch!
     * @throws Exception
     */
    protected function ReportError()
    {
        if($this->_mysqli->error){
            throw new Exception(
                    'SQL Query Error: ('.$this->_mysqli->errno.') '.$this->_mysqli->error,
                    $this->_mysqli->errno
            );
        }
    }
}
?>
