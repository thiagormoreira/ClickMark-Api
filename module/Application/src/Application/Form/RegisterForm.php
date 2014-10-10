<?php
namespace Application\Form;
use Zend\Form\Form;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Text;
use Zend\Form\Element\Password;
use Zend\Form\Element\Button;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element;

class RegisterForm extends Form
{

    public function __construct ($name = null)
    {
        parent::__construct('register');
        
        // Creating Fields
        
        $first_name = new Text('first_name');
        $first_name->setAttributes(
                array(
                        'class' => 'form-control input-lg',
                        'placeholder' => 'Nome',
                        'tabindex' => '1'
                ));
        
        $last_name = new Text('last_name');
        $last_name->setAttributes(
                array(
                        'class' => 'form-control input-lg',
                        'placeholder' => 'Sobrenome',
                        'tabindex' => '2'
                ));
        
        $email = new Text('email');
        $email->setAttributes(
                array(
                        'class' => 'form-control input-lg',
                        'placeholder' => 'Email',
                        'tabindex' => '3'
                ));
        
        $password = new Password('password');
        $password->setAttributes(
                array(
                        'class' => 'form-control input-lg',
                        'placeholder' => 'Senha',
                        'tabindex' => '4'
                ));
        
        $password_confirmation = new Password('password_confirmation');
        $password_confirmation->setAttributes(
                array(
                        'class' => 'form-control input-lg',
                        'placeholder' => 'Senha',
                        'tabindex' => '5'
                ));
        
        $t_and_c = new Checkbox('t_and_c');
        $t_and_c->setAttributes(array(
                'class' => 'hidden'
        ))->setValue('1');
        
        $agree = new Element('agree');
        $agree->setAttributes(
                array(
                        'class' => 'btn',
                        'data-color' => 'info',
                        'type' => 'button',
                        'tabindex' => '6'
                ))->setValue('Eu concordo');
        
        $submit = new Element('submit');
        $submit->setAttributes(
                array(
                        'class' => 'btn btn-success btn-block btn-lg',
                        'type' => 'submit',
                        'tabindex' => '7'
                ))->setValue('Cadastrar');
        
        // End Creating Fields
        
        // Setting Fields
        
        $this->add($first_name);
        $this->add($last_name);
        $this->add($email);
        $this->add($password);
        $this->add($password_confirmation);
        $this->add($t_and_c);
        $this->add($agree, array(
                'priority' => - 100
        ));
        $this->add($submit, array(
                'priority' => - 100
        ));
    }
}