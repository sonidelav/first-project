<?php
/**
 * Enryption / Decryption Base Class
 */
class CCrypter {    
    /**
     * Encrypts Given Text into TripleDES with mode CBC
     * @param string $str Text
     * @return string Encrypted Text
     */
    public static function encrypt($str, $key, $iv){
        return mcrypt_encrypt(
                MCRYPT_3DES, $key, $str, 
                MCRYPT_MODE_CBC, $iv
        );
    }
    
    /**
     * Decrypts given TripleDES encrypted text back to normal with mode CBC.
     * @param string $str Encrypted Text
     * @return string Decrypted Text
     */
    public static function decrypt($str, $key, $iv){
        return mcrypt_decrypt(
                MCRYPT_3DES, $key, $str, 
                MCRYPT_MODE_CBC, $iv
        );
    }
}
?>
