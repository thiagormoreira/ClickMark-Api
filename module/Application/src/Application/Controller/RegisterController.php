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
            
            if ($form->isValid()) {
                if ($this->getUserTable()->isUniqueEmail($data->email)) {
                    $transport = $this->getServiceLocator()->get(
                            'mail.transport');
                    $message = new Message();
                    $message->addTo($data->email, 
                            $data->first_name . $data->last_name)
                        ->addFrom('loganguns@gmail.com', 'ClickMark Digital')
                        ->setSubject('Ativação Registro ClickMark Digital')
                        ->setBody( 'http://' . $_SERVER['HTTP_HOST'] . '/user/activate/' . urlencode(base64_encode($data->email)));
                    $transport->send($message);
                    
                    $user->exchangeArray($data);
                    $this->getUserTable()->saveUser($user);
                    
                    $this->flashMessenger()->addSuccessMessage(
                            'Conta criada com sucesso!');
                    return $this->redirect()->toRoute('home');
                } else {
                    $this->flashMessenger()->addErrorMessage(
                            'Email já cadastrado!');
                }
            }
        }
        
        $this->layout('layout/layout2.phtml');
        return new ViewModel(array(
                'form' => $form
        ));
    }

    public function providerAction ()
    {
        $provider = $this->params('provider');
        
        switch ($provider) {
            
            case "facebook":
                // echo "facebook";
                $accessToken = 'CAAUszHZB3ukgBADgxONWMzZAZA63T8qk3bCVlZARcT4s9XJZCGsNsiLBaQ27fok9w5hlSfrdXdyVkDIp4UalxTWNscYjZAoevZAVHBJQjmdLHgZAbakngBCzjlW3hJ8ZBZCDC8ZBzUDMLIg2ebDlNUFbiynwIITpXXxmNliGgEYZA1v4yowLndOgLVqvwVqZAhyAN8KcfhZAYfqb9tb6z1aiFgCNF7xnowcfdAQ5AZD';
                $appSecret = '23bfe766525796d594d383aba0b4f65d';
                FacebookSession::setDefaultApplication($accessToken, $appSecret);
                $session = new FacebookSession($accessToken);
                if ($session) {
                    
                    try {
                        
                        $user_profile = (new FacebookRequest($session, 'GET', 
                                '/me'))->execute()->getGraphObject(
                                GraphUser::className());
                        
                        echo "Name: " . $user_profile->getName();
                    } catch (FacebookRequestException $e) {
                        
                        echo "Exception occured, code: " . $e->getCode();
                        echo " with message: " . $e->getMessage();
                    }
                }
                
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

