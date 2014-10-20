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
use Zend\Session\Container;
use Zend\Http\Header\SetCookie;
use Application\Model\Crypt;
use Zend\Crypt\Password\Bcrypt;
use Zend\View\Model\JsonModel;

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
        $url = $appArray = $this->getServiceLocator()->get('Config')['app']['79216']['url'];
       	//return $this->redirect()->toUrl($url);
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
    		
    		//echo json_encode($output);
    	}
    	
    	/*
        $request = $this->getRequest();
        
        $sm = $this->getServiceLocator();
        $appArray = $sm->get('Config')['app'];
        //$appId = base64_decode($this->params()->fromPost('appId'));
        echo 'g';
        var_dump($this->params('q'));
        
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
                $auth->setStoredHash(
                	$this->getUserTable()
                    ->getPasswordByEmail(
                    	$form->getData()
                	)
                );
                
                if(array_key_exists($appId, $appArray )){
                	
                	if ($auth->Authenticate($this->_adapter)) {
                		
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

					        $crypt = new Crypt();
					        $output = $crypt->encryptArrayResponse($response);
					        $url = $appArray[$appId]['url']. 'auth/login/' . $output;
					        
							//return $this->redirect()->toUrl($url);

                	} else {
                		
                		$response = array( 'success' => false, 'errorCode' => '1', 'message' => 'Credentials not found');
                		$crypt = new Crypt();
                		$output = $crypt->encryptArrayResponse($response);
                	    $url = $appArray[$appId]['url']. 'user/login/' . $output;
                	    //return $this->redirect()->toUrl($url);
                	}
               	}
            } else {
                //throw new \Exception('Invalid AppId');
            }  
        } else {
            //$url = $appArray = $this->getServiceLocator()->get('Config')['app']['79216']['url'];
            //return $this->redirect()->toUrl($url);
        }
        */
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
}