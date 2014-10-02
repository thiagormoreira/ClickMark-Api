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
use Zend\Authentication\AuthenticationService;
use Application\Model\Auth;
use Application\Model\User;
use Application\Form\AuthForm;
use Zend\Session\Container;
use Zend\Http\Header\SetCookie;
use Application\Model\Crypt;
use Zend\Crypt\Password\Bcrypt;

class IndexController extends AbstractActionController
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
        	
        $form = new AuthForm();
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {

        	$this->loginAction();
        } else {
	        return new ViewModel(
	        	array (
	                'form' => $form 
	        				
	        		) 
	        );
        }
    }

    public function loginAction ()
    {
        $form = new AuthForm();
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            // Form Request Access
            
            if (! $this->_adapter) {
                $sm = $this->getServiceLocator();
                $this->_adapter = $sm->get('zend_db_adapter');
            }
            
            $auth = new Auth();
            
            $form->setInputFilter($auth->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                
                $auth->exchangeArray($form->getData());
                // var_dump ( $form->getData () );
                $auth->setStoredHash(
                	$this->getUserTable()
                    ->getPasswordByEmail(
                    	$form->getData()
                	)
                );


                //var_dump($this->params()->fromPost());
                $sm = $this->getServiceLocator();
                $appArray = $sm->get('Config')['app'];
                $appId = base64_decode($this->params()->fromPost('appId'));
                 
                $bcrypt = new Bcrypt();
                foreach($appArray as $key => $app){
                	if($bcrypt->verify($key, "$2y$10$".$appId)){
                		
                		if ($auth->Authenticate($this->_adapter)) {
                			 
                			$secureToken = function() use ($app){
                				$bcrypt = new Bcrypt();
                				$hash = $bcrypt->create($app['token']);
                				return str_replace('$2y$10$', '', $hash);
                			};
                		
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
                		
                			$crypt = new Crypt();
                			//$key = $crypt->keygen();
                			$key  = '468e44ddbbba83f7cdb88fa04dc29aca00d6be3ffc3648e4fe';
                			$key .= '78702c36cfdca0ec58eef6df07f1f86bebcf91694f4e432a4c';
                			$key .= '88449785e427c44d1339de628a1b6bb7dc050464b314a202bd';
                			$key .= '3ea554f535fe9431c079ed1115f9838e92b9729f41f73a7df4';
                			$key .= '6841a802c5319a66ff7ab90fbf9778b5c251530824225da63b';
                			$key .= '82eaf5';
                			$key = hex2bin($key);
                		
                			$encrypt = function($data, $key){
                				$crypt = new Crypt();
                				$encrypt = $crypt->encrypt($data, $key, array ( 'algo' => 'aes'));
                				return $encrypt;
                			};
                		
                			$response = array (
                					't' => $secureToken(),
                					'r' => $encrypt(
                							json_encode($user), $key
                					),
                					's' => time() + 12080511543790138,
                					//'key' => bin2hex($key)
                			);
                		
                			$output = urlencode(base64_encode(json_encode($response)));
                			$url = $app['url']. 'auth/login/' . $output;

                			//var_dump($app());
                			
                			/*
                			 * $cookieFirstName = new SetCookie ( 'first_name',
                			 * $user->getFirstName (), time () + 365 * 60 * 60 * 24 );
                			 * // Zend\Http\Header\SetCookie instance $cookieLastName =
                			 * new SetCookie ( 'last_name', $user->getLastName (), time
                			 * () + 365 * 60 * 60 * 24 ); // Zend\Http\Header\SetCookie
                			 * instance $response = $this->getResponse ()->getHeaders
                			 * (); $response->addHeader ( $cookieFirstName );
                			 * $response->addHeader ( $cookieLastName );
                			 */
                			
                			return $this->redirect()->toUrl($url);
                			
                	} else {
                		
                		$this->flashMessenger()->addErrorMessage('Email ou senha não estão corretos');
                		return $this->redirect()->toRoute('home');
                	}
                } else {
                    
                    $this->flashMessenger()->addErrorMessage('Erro!');
                            return $this->redirect()->toRoute('home');
                	} 
               	}
            } else {
                
                $this->flashMessenger()->addErrorMessage('Preencha o formulário corretamente');
                        return $this->redirect()->toRoute('home');
            }
        } else {
            $this->flashMessenger()->addWarningMessage('Acesso direto à página não permitido');
                    return $this->redirect()->toRoute('home');
        }
        
        //return $this->redirect()->toRoute('home');
    }

    public function providerAction ()
    {
        $provider = $this->params('provider');
        
        switch ($provider) {
            
            case "facebook":
                // echo "facebook";
                
                break;
            
            case "twitter":
                // echo "twitter";
                
                break;
            
            case "google":
                // echo "google";
                
                break;
            
            default:
                throw new \Exception('Invalid provider');
        }
        
        return false;
    }

    public function logoutAction ()
    {
        $auth = $this->getServiceLocator()->get('auth_service');
        $auth->clearIdentity();
        
        $sessionUser = new Container('user');
        $sessionUser->getManager()
            ->getStorage()
            ->clear();
        
        $this->flashMessenger()->addSuccessMessage('Deslogado');
        
        return $this->redirect()->toRoute('home');
    }
}