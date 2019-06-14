<?php
/**
 * Exam Library
 * Client - Objects/Structures
 * This file contains all the manager objects/structures
 */

/**
 * Manager Object Base Class
 */
class CManagerObject {
    
    public static function createFromXML($xml,$object){
        $properties = get_class_vars($object);
        if($properties){
            $properties = array_keys($properties);
            $length = count($properties);
            $parser = xml_parser_create();
            xml_parse_into_struct($parser, $xml, $values, $indexes);
            xml_parser_free($parser);
            
            $xmlObjKey = strtoupper(substr($object,0,-strlen('Object')));
            
            echo 'XML Key: '.$xmlObjKey;
            
            if(array_key_exists($xmlObjKey, $indexes)){
                // Object Exist in XML so parse it
                $return = new $object();
                // Get Indexies for current object
                // First is Tag Open, Last is Tag Close
                $obj_indx = $indexes[$xmlObjKey];
                // Slice From Values current object section to take values
                $obj_section = array_slice($values, $obj_indx[0],$obj_indx[count($obj_indx)-1],true);
                
                var_dump($obj_indx);
                var_dump($obj_section);
                
                $obj_indx_length = count($obj_indx);
                // Load Values from xml
                for($i=0;$i<$length;++$i){
                    $property = $properties[$i];
                    
                }
                
                return $return;
            }
        }
        return null;
    }
}
/**
 * Client Status Object Class
 */
class StatusObject extends CManagerObject {
    public $Code;
    public $AutoCheck;
    public $AutoSend;
    public $EncryptionKey;
    public $EncryptionVector;
    public $ApplicationName;
    public $BuilderURL;
    
    public static function createFromXML($xml,$object=__CLASS__) {
        return parent::createFromXML($xml, $object);
    }
}
?>
