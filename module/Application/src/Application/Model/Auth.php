<?php
namespace Application\Model;
use Zend\Authentication\Result;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\Crypt\Password\Bcrypt;
use Application\Model\UserTable;
use Zend\Db\Adapter\Adapter;

/**
 *
 * @author loganguns
 *        
 */
class Auth implements InputFilterAwareInterface
{

    private $authEmail;

    private $authPassword;

    private $authAccessToken;

    private $authRememberMe;

    private $storedHash;

    protected $inputFilter;

    public function getAuthEmail ()
    {
        return $this->authEmail;
    }

    public function getAuthPassword ()
    {
        return $this->authPassword;
    }

    public function getAuthAccessToken ()
    {
        return $this->authAccessToken;
    }

    public function getAuthRememberMe ()
    {
        return $this->authRememberMe;
    }

    public function getHash ()
    {
        return $this->hash;
    }

    public function setStoredHash ($hash)
    {
        $this->storedHash = $hash;
        return $this;
    }

    public function exchangeArray ($credentials)
    {
        $this->authEmail = (isset($credentials['authEmail']) ? $credentials['authEmail'] : null);
        $this->authPassword = (isset($credentials['authPassword']) ? $credentials['authPassword'] : null);
        $this->authAccessToken = (isset($credentials['authAccessToken']) ? $credentials['authAccessToken'] : null);
        $this->authRememberMe = (isset($credentials['authRememberMe']) ? $credentials['authRememberMe'] : null);
    }

    public function getArrayCopy ()
    {
        return get_object_vars($this);
    }

    public function setInputFilter (InputFilterInterface $inputFilter)
    {
        throw new \Exception('Not used');
    }

    public function getInputFilter ()
    {
        if (! $this->inputFilter) {
            $inputFilter = new InputFilter();
            
            $factory = new InputFactory();
            
            $inputFilter->add(
                    $factory->createInput(
                            array(
                                    'name' => 'authEmail',
                                    'required' => true,
                                    'filters' => array(
                                            array(
                                                    'name' => 'StripTags'
                                            ),
                                            array(
                                                    'name' => 'StringTrim'
                                            )
                                    ),
                                    'validators' => array(
                                            array(
                                                    'name' => 'NotEmpty',
                                                    'options' => array(
                                                            'messages' => array(
                                                                    'isEmpty' => 'Campo obrigatório'
                                                            )
                                                    )
                                                    ,
                                                    array(
                                                            'name' => 'StringLength',
                                                            true,
                                                            'options' => array(
                                                                    'ecoding' => 'UTF-8',
                                                                    'max' => 60,
                                                                    'message' => 'No máximo %max% caracteres'
                                                            )
                                                    ),
                                                    array(
                                                            'name' => 'EmailAddress'
                                                    )
                                            )
                                    )
                            )));
            
            $inputFilter->add(
                    $factory->createInput(
                            array(
                                    'name' => 'authPassword',
                                    'required' => true,
                                    'filters' => array(
                                            array(
                                                    'name' => 'StripTags'
                                            ),
                                            array(
                                                    'name' => 'StringTrim'
                                            )
                                    ),
                                    'validators' => array(
                                            array(
                                                    'name' => 'NotEmpty',
                                                    'options' => array(
                                                            'messages' => array(
                                                                    'isEmpty' => 'Campo obrigatório'
                                                            )
                                                    )
                                                    ,
                                                    array(
                                                            'name' => 'StringLength',
                                                            true,
                                                            'options' => array(
                                                                    'ecoding' => 'UTF-8',
                                                                    'min' => 8,
                                                                    'message' => 'No mínomo %min% caracteres'
                                                            )
                                                    )
                                            )
                                    )
                            )));
            $inputFilter->add(
                    $factory->createInput(
                            array(
                                    'name' => 'authAccessToken',
                                    'required' => false,
                                    'filters' => array(
                                            array(
                                                    'name' => 'StripTags'
                                            ),
                                            array(
                                                    'name' => 'StringTrim'
                                            )
                                    )
                            )));
            $inputFilter->add(
                    $factory->createInput(
                            array(
                                    'name' => 'authRememberMe',
                                    'required' => false
                            )));
            
            $this->inputFilter = $inputFilter;
        }
        
        return $this->inputFilter;
    }

    function Authenticate (Adapter $adapter)
    {
        /*
         * Criando o auth adapter:&nbsp; passando o primeiro parâmetro o
         * adaptador do banco de dados $zendDb segundo parâmetro a tabela de
         * usuarios terceiro parâmetro a coluna da tabela aonde está o login
         * quarto parâmetro a coluna da tabela aonde está a senha
         */
        $bcrypt = new Bcrypt();
        
        if ($bcrypt->verify($this->authPassword, $this->storedHash)) {
            
            $authAdapter = new DbTable($adapter, 'tb_user', 'email', 'password');
            
            /*
             * Seta o credential tratment:&nbsp; tratamento da senha para ser
             * criptografada em md5 passado um parâmetro status para logar o
             * usuario que esteja ativo no sistema no caso dos parâmetros você
             * pode passar quantos forem necessários usando o AND na sequência
             * seta o Identity que é o login e Credential que é a senha
             */
            $authAdapter->setCredentialTreatment('? AND status = 1');
            $authAdapter->setIdentity($this->authEmail);
            $authAdapter->setCredential($this->storedHash);
            
            // Instanciando o AutenticationService para fazer a altenticação com
            // os dados passados para o authAdapter
            $authService = new AuthenticationService();
            
            // Autenticando o passando para a variável result o resultado da
            // autenticação
            $result = $authService->authenticate($authAdapter);
            
            // Validando a autenticação
            if ($result->isValid()) {
                
                // Se validou damos um get nos dados autenticados usando o
                // $result->getIdentity()
                $identity = $result->getIdentity();
                
                /*
                 * Imprimindo os dados na tela para confirmar os dados
                 * autenticados pronto, se aparecer os dados isso quer dizer que
                 * o usuario está autenticado no sistema
                 */
                // var_dump ( $identity );
                
                return true;
            } else {
                /*
                 * Caso falhe a autenticação, será gerado o log abaixo que será
                 * impresso&nbsp; na tela do computador para você sabe do
                 * problema ocorrido. os erros listados abaixo são os erros mais
                 * comuns que podem ocorrer.
                 */
                switch ($result->getCode()) {
                    case Result::FAILURE_IDENTITY_NOT_FOUND:
                        echo "O email não existe";
                        break;
                    case Result::FAILURE_CREDENTIAL_INVALID:
                        // echo "A senha não confere";
                        break;
                    default:
                        foreach ($result->getMessages() as $message) {
                            echo $message;
                        }
                }
                
                return false;
            }
        } else {
            // echo "A senha não confere";
            return false;
        }
    }
}