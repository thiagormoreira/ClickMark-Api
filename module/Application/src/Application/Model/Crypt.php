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
}