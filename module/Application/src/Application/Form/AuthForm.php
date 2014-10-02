<?php
namespace Application\Form;
use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Form\Element\Text;
use Zend\Form\Element\Password;
use Zend\Form\Element\Checkbox;

class AuthForm extends Form
{

    public function __construct ($name = null)
    {
        parent::__construct('auth');
        $this->setAttribute('method', 'post');
        // Creating Fields
        
        $authEmail = new Text('authEmail');
        $authPassword = new Password('authPassword');
        $authAccessToken = new Text('authAccessToken');
        $authRememberMe = new Checkbox('authRememberMe');
        
        // End Creating Fields
        
        // Setting Fields
        
        $this->add($authEmail);
        $this->add($authPassword);
        $this->add($authAccessToken);
        $this->add($authRememberMe);
    }
}