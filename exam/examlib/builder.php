<?php
/**
 * Exam Library
 * Builder - Objects/Structures
 * This file contains all the structures we take from builder
 */

/**
 * Question Object Struct
 */
class CQuestionObject {
    /** @var integer Cetification ID */
    public $CertID;
    /** @var integer Difficult Level */
    public $DifficultyLevel;
    /** @var integer Group ID */
    public $GroupID;
    /** @var integer Language ID */
    public $LangID;
    /** @var integer Module ID */
    public $ModuleID;
    /** @var string */
    public $NotValidForSyllabus;
    /** @var string */
    public $NotValidForVersions;
    /** @var integer Question ID */
    public $QID;
    /** @var string Question Text */
    public $QuestionText;
    /** @var string Syllabus ID */
    public $SyllabusID;
}
/**
 * Question Action Object Struct
 */
class CActionObject {
    /** @var integer */
    public $ActionID;
    /** @var integer */
    public $ActionModuleID;
    /** @var string Action's Parameters */
    public $ActionParams;
    /** @var integer */
    public $ActionTime;
    /** @var integer */
    public $CertID;
    /** @var integer */
    public $ModuleID;
    /** @var integer */
    public $OrderNumber;
    /** @var integer */
    public $QID;
    /** @var string */
    public $Score;
}
/**
 * Question Syllabus Object Struct
 */
class CSyllabusObject {
    /** @var integer */
    public $CertID;
    /** @var integer */
    public $GroupID;
    /** @var integer */
    public $ModuleID;
    /** @var integer */
    public $QID;
    /** @var string */
    public $SyllabusID;
    /** @var string */
    public $SyllabusVersion;
}
/**
 * TestSet Object Struct
 */
class CTestSetObject {
    /** @var integer Certification ID */
    public $CertID;
    /** @var integer Group ID */
    public $GroupID;
    /** @var integer Module ID */
    public $ModuleID;
    /** @var array Questions IDs List */
    public $QIDs;
    /** @var integer Test Set ID */
    public $TestSetID;
}
?>
