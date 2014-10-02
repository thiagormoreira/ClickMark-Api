<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Application;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Application\Model\UserTable;
use Application\Model\ProvTable;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Session\Validator;
use Zend\Session\Validator\HttpUserAgent;
use Zend\Session\Validator\RemoteAddr;
use Bugsnag;
use Application\Form\AuthForm;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Authentication\AuthenticationService;

class Module
{

    protected $blacklistController = array(
            'Application\Controller\User',
            'Application\Controller\Admin'
    );

    protected $whitelistAction = array(
            'login',
            'activate'
    );

    public function onBootstrap (MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $notAllowedController = $this->blacklistController;
        $allowedAction = $this->whitelistAction;
        
        $sm = $e->getApplication()->getServiceManager();
        $sm->get('translator')
            ->setLocale(
                \Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']))
            ->setFallbackLocale('en_US');
        
        $auth = $sm->get('auth_service');
        
        $this->bootstrapSession($e);
        
        $app = $e->getParam('application');
        $app->getEventManager()->attach(MvcEvent::EVENT_RENDER, 
                array(
                        $this,
                        'setFormToView'
                ), 100);
        $app->getEventManager()->attach(\Zend\Mvc\MvcEvent::EVENT_ROUTE, 
                function  ($e) use( $auth, $notAllowedController, $allowedAction)
                {
                    $app = $e->getApplication();
                    $routeMatch = $e->getRouteMatch();
                    $controllerName = $routeMatch->getParam('controller', 'NA');
                    $actionName = $routeMatch->getParam('action', 'NA');
                    if (! $auth->hasIdentity() &&
                             in_array($controllerName, $notAllowedController)) {
                        if (! in_array($actionName, $allowedAction)) {
                            die("acesso negado");
                            /*
                             * $response = $e->getResponse();
                             * $response->getHeaders()->addHeaderLine(
                             * 'Location', $e->getRouter()->assemble( array(),
                             * array('name' => 'user/login') ) );
                             * $response->setStatusCode(302); return $response;
                             */
                        }
                    } else {
                        return;
                    }
                }, - 100);
    }

    public function getConfig ()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig ()
    {
        return array(
                'Zend\Loader\StandardAutoloader' => array(
                        'namespaces' => array(
                                __NAMESPACE__ => __DIR__ . '/src/' .
                                         __NAMESPACE__
                        )
                )
        );
    }

    public function getServiceConfig ()
    {
        return array(
                'factories' => array(
                        'UserTable' => function  ($sm)
                        {
                            $adapter = $sm->get('zend_db_adapter');
                            $table = new UserTable($adapter);
                            return $table;
                        },
                        'ProvTable' => function  ($sm)
                        {
                            $adapter = $sm->get('zend_db_adapter');
                            $table = new ProvTable($adapter);
                            return $table;
                        },
                        'AuthTable' => function  ($sm)
                        {
                            $adapter = $sm->get('zend_db_adapter');
                            $table = new AuthTable($adapter);
                            return $table;
                        },
                        'UserCredential' => function  ($sm)
                        {
                            $adapter = $sm->get('zend_db_adapter');
                            $table = new CredentialTable($adapter);
                            return $table;
                        },
                        'Zend\Session\SessionManager' => function  ($sm)
                        {
                            $config = $sm->get('config');
                            if (isset($config['session'])) {
                                $session = $config['session'];
                                
                                $sessionConfig = null;
                                if (isset($session['config'])) {
                                    $class = isset($session['config']['class']) ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
                                    $options = isset(
                                            $session['config']['options']) ? $session['config']['options'] : array();
                                    $sessionConfig = new $class();
                                    $sessionConfig->setOptions($options);
                                }
                                
                                $sessionStorage = null;
                                if (isset($session['storage'])) {
                                    $class = $session['storage'];
                                    $sessionStorage = new $class();
                                }
                                
                                $sessionSaveHandler = null;
                                if (isset($session['save_handler'])) {
                                    // class should be fetched from service
                                    // manager since it will require constructor
                                    // arguments
                                    $sessionSaveHandler = $sm->get(
                                            $session['save_handler']);
                                }
                                
                                $sessionManager = new SessionManager(
                                        $sessionConfig, $sessionStorage, 
                                        $sessionSaveHandler);
                                
                                if (isset($session['validators'])) {
                                    $chain = $sessionManager->getValidatorChain();
                                    foreach ($session['validators'] as $validator) {
                                        foreach ($validator as $validatorCurrent) {
                                            $validator = new $validatorCurrent();
                                            $chain->attach('session.validate', 
                                                    array(
                                                            $validator,
                                                            'isValid'
                                                    ));
                                        }
                                    }
                                }
                            } else {
                                $sessionManager = new SessionManager();
                            }
                            Container::setDefaultManager($sessionManager);
                            return $sessionManager;
                        },
                        'mail.transport' => function  ($sm)
                        {
                            $config = $sm->get('Config');
                            $transport = new Smtp();
                            $transport->setOptions(
                                    new SmtpOptions(
                                            $config['mail']['transport']['options']));
                            
                            return $transport;
                        },
                        'mail.transport2' => function  ($sm)
                        {
                            $config = $sm->get('Config');
                            $transport = new Smtp();
                            $transport->setOptions(
                                    new SmtpOptions(
                                            $config['mail2']['transport']['options']));
                            
                            return $transport;
                        },
                        'auth_service' => function  ($sm)
                        {
                            $auth = new AuthenticationService();
                            return $auth;
                        }
                )
        );
    }

    public function bootstrapSession (MvcEvent $e)
    {
        
    	$session = $e->getApplication()
            ->getServiceManager()
            ->get('Zend\Session\SessionManager');
        $session->start();
        
        $container = new Container('initialized');
        if (! isset($container->init)) {
            $session->regenerateId(true);
            $container->init = 1;
        }
    }

    public function setFormToView ($event)
    {
        $form = new AuthForm();
        $viewModel = $event->getViewModel();
        $viewModel->setVariables(
                array(
                        'form' => $form
                ));
    }
}
