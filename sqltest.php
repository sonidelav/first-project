<?php
require(__DIR__.'/exam/examlib/CSQL.php');
require(__DIR__.'/exam/examObjects.php');

try {
    $BuilderSql = new CBuilderSQLClient('localhost', 'english_builder', '123456', 'english_builder');
    
    $Question = $BuilderSql->GetQuestion(1, 1001, 1, 1);
    
    $Actions = $BuilderSql->GetQuestionActions(
            $Question->GroupID, $Question->ModuleID, 
            $Question->CertID, $Question->QID
    );
    
    $Syllabus = $BuilderSql->GetQuestionSyllabus(1, 1001, 1);    
    
} catch(Exception $ex){
    echo $ex->getMessage();
}
?>
