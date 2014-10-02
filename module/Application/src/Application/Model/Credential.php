<?php
namespace Application\Model;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

class Credential implements InputFilterAwareInterface
{

    private $idCredential;

    private $providerName;

    private $accessToken;

    protected $inputFilter;

    public function exchangeArray ($data)
    {
        $this->idCrendtial = (isset($data['idCrendtial'])) ? $data['idCrendtial'] : null;
        $this->providerName = (isset($data['providerName'])) ? $data['providerName'] : null;
        $this->accessToken = (isset($data['accessToken'])) ? $data['accessToken'] : null;
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

                                                    
                                            ),
                                            array(
                                                    'name' => 'StringLength',
                                                    true,
                                                    'options' => array(
                                                            'ecoding' => 'UTF-8',
                                                            'min' => 8
                                                    // 'message' => 'No mínimo
                                                    // %min% caracteres'
                                                                                                        )
                                            )
                                    )
                            )));
            
            $this->inputFilter = $inputFilter;
        }
        
        return $this->inputFilter;
    }
}