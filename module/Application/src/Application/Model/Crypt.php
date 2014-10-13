<?php
namespace Application\Model;
use Zend\Crypt\Key\Derivation\Pbkdf2;
use Zend\Math\Rand;

use Zend\Crypt\BlockCipher;

class Crypt{
	
	public function keygen($pass = null){
		
		$uniqueRandonStr = function($lenght){ $bytes = openssl_random_pseudo_bytes($lenght); return bin2hex($bytes); };
		
		if($pass == null){
			$pass = $uniqueRandonStr(32);
		}
		
		$salt = Rand::getBytes(strlen($pass), true);
		$key  = Pbkdf2::calc('sha256', $pass, $salt, 10000, strlen($pass)*2);
		
		return $key;
	}
	
	public function encrypt($msg, $key, $algo){
		$blockCipher = BlockCipher::factory('mcrypt', $algo);
		$blockCipher->setKey($key);
		$result = $blockCipher->encrypt($msg);
		
		return $result;
	}
	
	public function decrypt($msg, $key, $algo){
		$blockCipher = BlockCipher::factory('mcrypt', $algo);
		$blockCipher->setKey($key);
		$result = $blockCipher->decrypt($msg);
		
		return $result;
	}
	
	public function encryptArrayResponse($arrayResponse){
	    //$crypt = new Crypt();
	    //$key = $crypt->keygen();
	    $key  = '468e44ddbbba83f7cdb88fa04dc29aca00d6be3ffc3648e4fe';
	    $key .= '78702c36cfdca0ec58eef6df07f1f86bebcf91694f4e432a4c';
	    $key .= '88449785e427c44d1339de628a1b6bb7dc050464b314a202bd';
	    $key .= '3ea554f535fe9431c079ed1115f9838e92b9729f41f73a7df4';
	    $key .= '6841a802c5319a66ff7ab90fbf9778b5c251530824225da63b';
	    $key .= '82eaf5';
	    $key = hex2bin($key);
	    
	    $responseArray = array (
	    		//'token' => $secureToken(),
	    		'timestamp' => time(),
	    		'response' => $arrayResponse,
	    		//'key' => bin2hex($key)
	    );
	    
	    $responseJson = json_encode($responseArray);
	    $responseEncrypt = $this->encrypt($responseJson, $key, array ( 'algo' => 'aes'));
	    
	    return base64_encode($responseEncrypt);
	}
	
	public function decryptArrayResponse($responseEncrypt){
	    //$crypt = new Crypt();
	    //$key = $crypt->keygen();
	    $key  = '468e44ddbbba83f7cdb88fa04dc29aca00d6be3ffc3648e4fe';
	    $key .= '78702c36cfdca0ec58eef6df07f1f86bebcf91694f4e432a4c';
	    $key .= '88449785e427c44d1339de628a1b6bb7dc050464b314a202bd';
	    $key .= '3ea554f535fe9431c079ed1115f9838e92b9729f41f73a7df4';
	    $key .= '6841a802c5319a66ff7ab90fbf9778b5c251530824225da63b';
	    $key .= '82eaf5';
	    $key = hex2bin($key);

	    $responseDecrypt = $this->decrypt(base64_decode($responseEncrypt), $key, array ( 'algo' => 'aes'));
	    var_dump($responseDecrypt);
	    $responseArray = json_decode($responseDecrypt);
	    
	    return $responseArray;
	}
}