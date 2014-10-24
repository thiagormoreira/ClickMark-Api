<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Application\Controller;
use Zend\Authentication\AuthenticationService;
use Application\Model\Auth;
use Application\Model\User;
use Zend\Session\Container;
use Zend\Http\Header\SetCookie;
use Application\Model\Crypt;
use Zend\Crypt\Password\Bcrypt;
use Zend\View\Model\JsonModel;
use Zend\Mvc\Controller\AbstractRestfulController;

class IndexController extends AbstractRestfulController
{

    protected $_userTable;

    protected $_adapter;
    
    public function getUserTable ()
    {
        if (! $this->_userTable) {
            $sm = $this->getServiceLocator();
            $this->_userTable = $sm->get('UserTable');
        }
        
        return $this->_userTable;
    }

    public function indexAction ()
    {
        $url = $appArray = $this->getServiceLocator()->get('Config')['app']['79216']['url'];
       	return $this->redirect()->toUrl($url);
    }

    public function loginAction ()
    {
    	$encrypted = $this->params()->fromQuery('q');
    	$crypt = new Crypt();
    	
    	if($crypt->decryptArrayResponse($encrypted) != false){
    		$credentials = $crypt->decryptArrayResponse(json_encode($encrypted));
    		//var_dump($credentials);
    		
    		$auth = new Auth();
    		$auth->setAuthEmail($credentials->response->login);
    		$auth->setAuthPassword($credentials->response->password);
    		$auth->setStoredHash(
    				$this->getUserTable()
    				->getPasswordByEmail(
    						$credentials->response->login
    				)
    		);
    		//var_dump($auth);
    		
    		$sm = $this->getServiceLocator();
    		$this->_adapter = $sm->get('zend_db_adapter');
    		
    		if ($auth->authenticate($this->_adapter)) {
    			//// SENHA CORRETA ///////////
    			$getUser = $this->getUserTable()->getUserByEmail(
    					$auth->getAuthEmail()
    			);
    			 
    			$user = array (
    					'login' => true,
    					'firstName' => $getUser->getFirstName(),
    					'lastName' => $getUser->getLastName(),
    					'email' => $getUser->getEmail(),
    					'id' => $getUser->getIdUser()
    			);
    			 
    			$response = array( 'success' => true, 'user' => $user);
    			//var_dump($output);
    			//var_dump($response);
    			//echo 'certo';
    		} else {
    			//// SENHA INCORRETA ///////////
    			$response = array( 'success' => false, 'errorCode' => '1');
    		}
    			 
    		$crypt = new Crypt();
    		$output = $crypt->encryptArrayResponse($response);
    		
    		$result = new JsonModel(array(
    				'return' => $output
    		));
    		
    		return $result;
    	}
    }

    public function registerAction ()
    {
    	$encrypted = $this->params()->fromQuery('q');
    	$crypt = new Crypt();
    	 
    	if($crypt->decryptArrayResponse($encrypted) != false){
    		$decryptedArray = $crypt->decryptArrayResponse(json_encode($encrypted));
			
    		if ($this->getUserTable()->isUniqueEmail($decryptedArray->response->email)) {
    			 
    			$output = $crypt->encryptArrayResponse($decryptedArray->response->email);
    			//var_dump($decryptedArray);
    			$appId = base64_decode($decryptedArray->response->appId);
    			$from = 'philipebarros@hotmail.com';
    			$email = $decryptedArray->response->email;
    			$assunto = 'Ativação Conta MarkSend';
    			$activationLink = $this->getServiceLocator()->get('Config')['app'][$appId]['url'].'user/activate/'.urlencode(base64_encode($output));
    			$mensagem = <<<EOD
                            <a href='{$activationLink}'>{$activationLink}</a>
EOD;
    			
    			$ses = new SimpleEmailService('AKIAIX32JUETXGGVTYGA', '1/D6IFvP6VAs3yKsqTsh7l179nj7m5PBogwAYc23');
    			//cria uma nova instancia
    			
    			$m = new SimpleEmailServiceMessage();
    			//seta valores definidos nas variaveis acima
    			$m->addTo($email);
    			$m->setFrom($from);
    			$m->setSubjectCharset('ISO-8859-1');
    			$m->setMessageCharset('ISO-8859-1');
    			$m->setSubject('=?UTF-8?B?'.base64_encode($assunto).'?= ');
    			$m->setMessageFromString(NULL,$mensagem);
    			
    			//envia email
    			$ses->sendEmail($m);
    			
    			// New User
	    		//var_dump($decryptedArray);
	    		$user = new User();
	    		$user->setEmail($decryptedArray->response->email);
	    		$user->setFirstName($decryptedArray->response->name);
	    		$user->setLastName($decryptedArray->response->surname);
	    		$user->setPassword($decryptedArray->response->pass);
	    		//var_dump($user);
	    		$saveUser = $this->getUserTable()->saveUser($user);
	    		 
	    		if ( $saveUser > 0){
	    			//Salvou
	    			$response = array( 'success' => true);
	    		} else {
	    			//Deu algo errado e nao salvou =/
	    			$response = array( 'success' => false, 'errorCode' => '1', 'message' => 'Not saved');
	    		}
    		} else {
              	//Email ja Cadastrado
            	$response = array( 'success' => false, 'errorCode' => '2', 'message' => 'Email already registered');
            }
    		$sm = $this->getServiceLocator();
    		$this->_adapter = $sm->get('zend_db_adapter');
    		
    	
    		$crypt = new Crypt();
    		$output = $crypt->encryptArrayResponse($response);
    	
    		$result = new JsonModel(array(
    				'return' => $output
    		));
    	
    		return $result;
    	}
    }
    
    public function activateAction ()
    {
    	if($this->params('code') != null){
    		$crypt = new Crypt();
    		$code = base64_decode(urldecode($this->params('code')));
    		$email = $crypt->decryptArrayResponse($code);
    
    		if ($this->getUserTable()->activateUserByEmail($email->response)) {
    			$status = array (
    					'success' => true,
    			);
    			//echo 'Conta ativada com sucesso!';
    		} else {
    			$status = array (
    					'success' => false,
    			);
    
    			//echo 'Código não encontrado ou inválido! ';
    		}
    	}
    	//echo json_encode($status);
    	
    	$result = new JsonModel(array(
    				'return' => $status
    		));
    	
    		return $result;
    }
}