<?php
namespace Application\Model;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

class User implements InputFilterAwareInterface
{

    private $idUser;

    private $email;

    private $firstName;

    private $lastName;

    private $password;

    private $status;

    protected $inputFilter;

    /**
     *
     * @return int
     */
    public function getIdUser ()
    {
        return $this->idUser;
    }

    /**
     *
     * @return string
     */
    public function getEmail ()
    {
        return $this->email;
    }

    /**
     *
     * @return string
     */
    public function getFirstName ()
    {
        return $this->first_name;
    }

    /**
     *
     * @return string
     */
    public function getLastName ()
    {
        return $this->last_name;
    }

    /**
     *
     * @return string
     */
    public function getPassword ()
    {
        return $this->password;
    }

    /**
     *
     * @return string
     */
    public function getStatus ()
    {
        return $this->status;
    }

    public function exchangeArray ($data)
    {
        $this->idUser = (isset($data['iduser'])) ? $data['iduser'] : null;
        $this->email = (isset($data['email'])) ? $data['email'] : null;
        $this->first_name = (isset($data['first_name'])) ? $data['first_name'] : null;
        $this->last_name = (isset($data['last_name'])) ? $data['last_name'] : null;
        $this->password = (isset($data['password'])) ? $data['password'] : null;
        $this->status = (isset($data['status'])) ? $data['status'] : null;
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
                                    'name' => 'first_name',
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
                                                            'messages' => array()
                                                    // 'isEmpty' => 'Campo
                                                    // obrigatório'
                                                                                                        )
                                            )
                                            ,
                                            array(
                                                    'name' => 'StringLength',
                                                    true,
                                                    'options' => array(
                                                            'ecoding' => 'UTF-8',
                                                            'min' => 3,
                                                            'max' => 45
                                                    // 'message' => 'Entre %min%
                                                    // e %max% caracteres'
                                                                                                        )
                                            )
                                    )
                            )));
            
            $inputFilter->add(
                    $factory->createInput(
                            array(
                                    'name' => 'last_name',
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
                                                            'messages' => array()
                                                    // 'isEmpty' => 'Campo
                                                    // obrigatório'
                                                                                                        )
                                            )
                                            ,
                                            array(
                                                    'name' => 'StringLength',
                                                    true,
                                                    'options' => array(
                                                            'ecoding' => 'UTF-8',
                                                            'min' => 3,
                                                            'max' => 45
                                                    // 'message' => 'Entre %min%
                                                    // e %max% caracteres'
                                                                                                        )
                                            )
                                    )
                            )));
            
            $inputFilter->add(
                    $factory->createInput(
                            array(
                                    'name' => 'email',
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
                                                            'messages' => array()
                                                    // 'isEmpty' => 'Campo
                                                    // obrigatório'
                                                                                                        )
                                            )
                                            ,
                                            array(
                                                    'name' => 'StringLength',
                                                    true,
                                                    'options' => array(
                                                            'ecoding' => 'UTF-8',
                                                            'max' => 60
                                                    // 'message' => 'No máximo
                                                    // %max% caracteres'
                                                                                                        )
                                            ),
                                            array(
                                                    'name' => 'EmailAddress'
                                            )
                                    )
                            )));
            
            $inputFilter->add(
                    $factory->createInput(
                            array(
                                    'name' => 'password',
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
                                                            'messages' => array()
                                                    // 'isEmpty' => 'Campo
                                                    // obrigatório'
                                                                                                        )
                                            )
                                            ,
                                            array(
                                                    'name' => 'StringLength',
                                                    true,
                                                    'options' => array(
                                                            'ecoding' => 'UTF-8',
                                                            'min' => 8
                                                    // 'message' => 'No mínomo
                                                    // %min% caracteres'
                                                                                                        )
                                            )
                                    )
                            )));
            
            $inputFilter->add(
                    $factory->createInput(
                            array(
                                    'name' => 'password_confirmation',
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
                                                            'messages' => array()
                                                    // 'isEmpty' => 'Campo
                                                    // obrigatório'
                                                                                                        )
                                            )
                                            ,
                                            array(
                                                    'name' => 'StringLength',
                                                    true,
                                                    'options' => array(
                                                            'ecoding' => 'UTF-8',
                                                            'min' => 8
                                                    // 'message' => 'No mínimo
                                                    // %min% caracteres'
                                                                                                        )
                                            ),
                                            array(
                                                    'name' => 'identical',
                                                    'options' => array(
                                                            'token' => 'password'
                                                    )
                                            )
                                    )
                            )));
            
            $this->inputFilter = $inputFilter;
        }
        
        return $this->inputFilter;
    }
}
