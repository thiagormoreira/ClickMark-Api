<?php
namespace Application\Model;

use Zend\Crypt\BlockCipher;

class AES {

	/**
	 * Constantes para geração de chaves criptograficas.
	 *
	 */
	const KEYGEN_128_BITS = 16;
	const KEYGEN_192_BITS = 24;
	const KEYGEN_256_BITS = 32;
	
	private $keyUniq;
	
	// Array plano unidimencional para array unidimencional criptografado
	function encryptAesArray($plainArray){
		 
	
		$uniqueRandonStr = function($lenght){ $bytes = openssl_random_pseudo_bytes($lenght); return bin2hex($bytes); };
		 
		$uniqueKey = function($lenght){ 
			$bytes = openssl_random_pseudo_bytes($lenght);
			$uniqueRandonStr = bin2hex($bytes);
			return AES::keygen( AES::KEYGEN_256_BITS, $uniqueRandonStr ); 
		}; //dinamico


		echo $this->keyUniq = $uniqueKey(32);
		
		foreach ( $plainArray as $key => $value ){
			
			
			$encodedKey = base64_encode( mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->keyUniq, $key, MCRYPT_MODE_ECB) );
			$encodedValue = base64_encode(  mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->keyUniq, $value, MCRYPT_MODE_ECB) );
			
			$encryptedArray [ $encodedKey ] = $encodedValue ;
			
		}
		
		return $encryptedArray;
	}
	 
	////////////////////////////////////////////
	
	function decryptAesArray($encryptedArray){
		 
		foreach ( $encryptedArray as $key => $value ){
			
			$decodedKey = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->keyUniq, base64_decode ( $key ), MCRYPT_MODE_ECB);
			$decodedValue = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->keyUniq, base64_decode ( $value ), MCRYPT_MODE_ECB);
			
			$decryptedArray [ $decodedKey ] = $decodedValue ;
		}
		
		return $decryptedArray;
	}
	
	/**
	 * Gera uma chave criptografica com o tamanho especificado
	 * pelo parametro $key_size Ex.: AES::KEYGEN_128_BITS,
	 *                               AES::KEYGEN_192_BITS,
	 *                               AES::KEYGEN_256_BITS.
	 *
	 * @method keygen()
	 * @param integer $key_size
	 * @param string $sal       Incremento para geração da chave criptográfica.
	 * @return string
	 * @since 1.0.0
	 *
	 */
	public function keygen($key_size, $sal = null)
	{
		$key = "";
	
	
		if($key_size == AES::KEYGEN_192_BITS){
			$x = (! is_null($sal) ? date('YmdHis'.$sal) : date('YmdHms'));
			$key = substr(md5($x), 0, AES::KEYGEN_192_BITS);
	
			//
			// 256 Bits
			//
		}else if($key_size == AES::KEYGEN_256_BITS){
		$x = (! is_null($sal) ? date('YmdHis'.$sal) : date('YmdHms'));
		$key = substr(md5($x), 0, AES::KEYGEN_256_BITS);
	
		//
		// 128 Bits
		//
		}else{
		$x = (! is_null($sal) ? date('YmdHis'.$sal) : date('YmdHms'));
		$key = substr(md5($x), 0, AES::KEYGEN_128_BITS);
		}
	
	
		return $key;
		}
	
}