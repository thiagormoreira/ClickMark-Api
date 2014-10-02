<?php
namespace Application\Model;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Debug\Debug;

class CredentialTable extends AbstractTableGateway
{

    protected $table = 'tb_credential';

    public function __construct (Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Credential());
        $this->initialize();
    }

    public function getCredencialByProvider ($idUser, $idProvider)
    {
        $idUser = gmp_init($idUser);
        
        $rowSet = $this->select(array(
                'iduser' => $idUser,
                ''
        ));
        $row = $rowSet->current();
        
        if (! row) {
            throw new \Exception("Credencial não encontrada");
        }
        
        return $row;
    }

    public function saveCredential (Credential $credential)
    {
        $data = array(
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'status' => $user->status
        );
        
        $idUser = (int) $user->iduser;
        
        if (empty($idUser)) {
            $this->insert($data);
            // Debug::dump($save, $label = null, $echo = true);
        } else {
            if ($this->getUser($idUser)) {
                $this->update($data, array(
                        'iduser' => $idUser
                ));
            } else {
                throw new \Exception('Usuario não encontrado');
            }
        }
    }

    public function associateCredentialToUser ()
    {}

    public function deleteCredential ($idCredential)
    {}

    public function isUniqueEmail ($email)
    {}
}