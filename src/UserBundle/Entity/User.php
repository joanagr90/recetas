<?php
namespace UserBundle\Entity;

use Symfony\Components\Security\Core\User\UserInterface;

class User implements UserInterface, \Serializable
{
    private $id;
    private $email;
    private $username;
    private $password;

    //para permitir una gestión más potente de roles
    private $roles;

    public function getId()
    {
        return $this->id;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    #se añaden los métodos heredados para implementar la interfaz UserInterface
    public function getSalt()
    {
        return null;
    }

    #para permitir una gestión de roles más potente.
    public function getRoles()
    {
        #return array('ROLE_USER');
        return explode(" ", $this->roles);
    }

    public function eraseCredentials(){

    }

    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->username,
            $this->password
        ));
    }

    public function unserialize($serialized)
    {   
        list(
            $this->id,
            $this->email,
            $this->username,
            $this->password 
        ) = unserialize($serialized);
    }



}