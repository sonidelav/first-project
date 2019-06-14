<?php
require_once __DIR__.'/exam/examCore.php';
include_once(__DIR__.'/exam/examlib/manager.php');
class myClient extends ManagerClient {
    
    public function PrintDebugResponse(){
        // Get Responce
        echo 'RESPONSE: <br>'."\n".print_r($this->ServiceClient->__getLastResponse(),true);
    }
    
    public function Client_ACK($XMLText) {
        $Params = array(
            'UserName' => 'system',
            'Password' => '123456',
            'XMLText' => $XMLText
        );
        return $this->ServiceClient->Client_ACK($Params);
    }
    
}

$client = new myClient(Config::MANAGER_USERNAME, Config::MANAGER_PASSWORD);

if($client){
    try{
        // SEND ACK XML
        echo "Send ACK XML :";
        $xml = Builder_Client_XML('1234567890', 1, 1, 'DEBUG-PC');
        echo $xml;
        
        // Send ACK
        $res = $client->Client_ACK($xml);
        $xml = $res->Client_ACKResult;
        
        $Status = StatusObject::createFromXML($xml);
        
        var_dump($Status);
        
        // Print Response from manager
        //$client->PrintDebugResponse();
//        
//        // Send STR
//        echo "Send STR XML :";
//        $xml = Builder_Start_XML('DEBUG-PC', 1, 1, '1234567890', 1, 1, 2, 1);
//        echo $xml;
//        
//        $client->Client_STR($xml);
//        $client->PrintDebugResponse();
        
        // Send RES
//        echo "Send RES XML :";
//        $xml = '
//                    <RESULTS>
//                    <EXAMSESSION>
//                        <COMPUTERNAME>DEBUG-PC</COMPUTERNAME>
//                        <GUID>1234567890</GUID>
//                        <TESTCENTERID>1</TESTCENTERID>
//                        <LABID>1</LABID>
//                        <STARTDATE>20121025</STARTDATE>
//                        <EXAMTIME>12:00</EXAMTIME>
//                        <STARTTIME>12:18</STARTTIME>
//                        <ENDTIME>12:18</ENDTIME>
//                        <ACTUALDURATION></ACTUALDURATION>
//                        <STATUS>5</STATUS>
//                        <EXAMID>1</EXAMID>
//                        <UID>1</UID>
//                        <QSET>2</QSET>
//                        <CERT>1</CERT>
//                        <TESTSETID>1</TESTSETID>
//                        <MARK></MARK>
//                        <TOTALQUESTIONS></TOTALQUESTIONS>
//                    </EXAMSESSION>
//                    <QUESTION>
//                        <QID>4</QID>
//                        <GRADE>0.00</GRADE>
//                        <ELAPSEDTIME>100</ELAPSEDTIME>
//                        <STATUS>0</STATUS>
//                        <CERT>1</CERT>
//                    </QUESTION>
//                    </RESULTS>
//        ';
//        echo $xml;
//        
//        $client->Client_RES($xml);
//        $client->PrintDebugResponse();
        
//        $xml = Builder_End_XML('DEBUG-PC', 1, 1, '1234567890');
//        echo "Send END Xml : \n".$xml;
//        
//        $client->Client_END($xml);
//        $client->PrintDebugResponse();
        
    } catch(SoapFault $sf){
      echo $sf->getMessage();  
    }
}
?>
