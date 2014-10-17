<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Application\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Form\RegisterForm;
use Application\Model\User;
use Zend\Mail\Message;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;
use Facebook\FacebookSession;
use Application\Model\Crypt;
use Application\Model\SimpleEmailService;
use Application\Model\SimpleEmailServiceMessage;

class RegisterController extends AbstractActionController
{

    protected $userTable;

    public function getUserTable ()
    {
        if (! $this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('UserTable');
        }
        
        return $this->userTable;
    }

    public function indexAction ()
    {
        $form = new RegisterForm();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            
            // Initializing User Model
            $user = new User();
            
            // Getting Data Posted
            $data = $request->getPost();
            
            $form->setInputFilter($user->getInputFilter());
            $form->setData($data);
            
            $sm = $this->getServiceLocator();
                
            $appArray = $sm->get('Config')['app'];
            $appId = base64_decode($data->appId);
            //var_dump($form->isValid());
            if ( array_key_exists($appId, $appArray )){
                if ($form->isValid()) {
	                if ($this->getUserTable()->isUniqueEmail($data->email)) {
	                    
	                    $crypt = new Crypt();
	                    
	                    $output = $crypt->encryptArrayResponse($data->email);
	                    
	                    $from = 'philipebarros@hotmail.com';
		                $email = $data->email;
		                $assunto = 'Ativação Conta MarkSend';
		                $activationLink = $appArray[$appId]['url'].'user/activate/'.urlencode(base64_encode($output));
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
	                    
	                    $user->exchangeArray($data);
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
	                        $response = array( 'success' => false, 'errorCode' => '2', 'message' => 'Email already register');
	                }
	            } else {
	                // Form Invalido
	                $response = array( 'success' => false, 'errorCode' => '3', 'message' => 'Invalid Form');
	            }
                
	            $crypt = new Crypt();
	             
	            $output = $crypt->encryptArrayResponse($response);
	            
            } else {
                //throw new \Exception('Invalid AppId');
		$crypt = new Crypt();
	             
            	$output = $crypt->encryptArrayResponse('deu ruim');
            }   
        }
	            
	            $url = $appArray[$appId]['url']. 'register/?q=' . $output;
	            return $this->redirect()->toUrl($url);
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
    	echo json_encode($status);
    	exit();
    	//return $this->redirect ()->toRoute ( 'home' );
    }
}

