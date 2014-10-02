<?php
namespace Application\Model;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Debug\Debug;
use Zend\Validator\Db\NoRecordExists;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\TableGateway\TableGateway;

class UserTable extends AbstractTableGateway
{

    protected $table = 'tb_user';

    public function __construct (Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new User());
        $this->initialize();
    }

    public function fetchAll ($pageNumber = 1, $itemCountPerPage = 10)
    {
        $select = new Select();
        $select->from($this->table)->order("first_name");
        
        $adapter = new DbSelect($select, 
                $this->adapter . $this->resultSetPrototype);
        $paginator = new Paginator($adapter);
        $paginator->setCurrentPageNumber($pageNumber);
        $paginator->setItemCountPerPage($itemCountPerPage);
        
        return $paginator;
    }

    public function getUser ($idUser)
    {
        //$idUser = gmp_init($idUser);
        
        $rowSet = $this->select(
                array(
                        'iduser' => $idUser
                ));
        $row = $rowSet->current();
        
        if (! $row) {
            throw new \Exception("Usuario não encontrado");
        }
        
        return $row;
    }

    public function getUserByEmail ($email)
    {
        $rowSet = $this->select(
                array(
                        'email' => $email
                ));
        $row = $rowSet->current();
        
        if (! $row) {
            throw new \Exception("Usuario não encontrado");
        }
        
        return $row;
    }

    public function saveUser (User $user)
    {
        $bcrypt = new Bcrypt();
        $securePass = $bcrypt->create($user->getPassword());
        
        $data = array(
                'email' => $user->getEmail(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'password' => $securePass
        // 'status' => $user->getStatus()
                );
        
        $idUser = (int) $user->getIdUser();
        
        if (empty($idUser)) {
            $this->insert($data);
        } else {
            if ($this->getUser($idUser)) {
                $this->update($data, 
                        array(
                                'iduser' => $idUser
                        ));
            } else {
                throw new \Exception('Usuario não encontrado');
            }
        }
    }

    public function getPasswordByEmail ($data)
    {
        $userTable = new TableGateway($this->table, $this->adapter);
        
        $select = new Select();
        $select->from($this->table);
        $select->where(
                array(
                        'email' => $data['authEmail']
                ));
        $select->columns(array(
                'password'
        ));
        $resultSet = $userTable->selectWith($select);
        $resultRow = $resultSet->current();
        return $resultRow['password'];
    }

    public function isUniqueEmail ($email)
    {
        $userTable = new TableGateway($this->table, $this->adapter);
        
        $select = new Select();
        $select->from($this->table);
        $select->where(array(
                'email' => $email
        ));
        $select->columns(array(
                'iduser'
        ));
        $resultSet = $userTable->selectWith($select);
        $resultRow = $resultSet->current();
        
        if (! $resultRow) {
            return true;
        } else {
            return false;
        }
    }

    public function activateUserByEmail ($email)
    {
        $data = array(
                'status' => 1
        );
        
        if ($this->update($data, 
                array(
                        'email' => $email
                ))) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteUser ($iduser)
    {}
}