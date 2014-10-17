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
        
        $crypt5 = new Crypt();
        $output5 = $crypt5->encryptArrayResponse('teste');
        var_dump($output5);
        
        if ($request->isPost()) {

        	$this->loginAction();
        } else {
            $url = $appArray = $this->getServiceLocator()->get('Config')['app']['79216']['url'];
            //return $this->redirect()->toUrl($url);
        }
    }

    public function loginAction ()
    {
        $form = new AuthForm();
        
        $request = $this->getRequest();
        
        $sm = $this->getServiceLocator();
        $appArray = $sm->get('Config')['app'];
        $appId = base64_decode($this->params()->fromPost('appId'));
        
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
					        
							return $this->redirect()->toUrl($url);

                	} else {
                		
                		$response = array( 'success' => false, 'errorCode' => '1', 'message' => 'Credentials not found');
                		$crypt = new Crypt();
                		$output = $crypt->encryptArrayResponse($response);
                	    $url = $appArray[$appId]['url']. 'user/login/' . $output;
                	    return $this->redirect()->toUrl($url);
                	}
               	}
            } else {
                throw new \Exception('Invalid AppId');
            }  
        } else {
            $url = $appArray = $this->getServiceLocator()->get('Config')['app']['79216']['url'];
            return $this->redirect()->toUrl($url);
        }
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