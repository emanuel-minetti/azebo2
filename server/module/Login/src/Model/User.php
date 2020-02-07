<?php /** @noinspection PhpUnused */
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */


namespace Login\Model;


use ArrayObject;

class User extends ArrayObject
{
    public $id;
    public $username;
    public $password_hash;
    public $name;
    public $given_name;

    public function exchangeArray($data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->username = !empty($data['username']) ? $data['username'] : null;
        $this->password_hash = !empty($data['password_hash']) ? $data['password_hash'] : null;
        $this->name = !empty($data['name']) ? $data['name'] : null;
        $this->given_name = !empty($data['given_name']) ? $data['given_name'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'password_hash' => $this->password_hash,
            'name' => $this->name,
            'given_name' => $this->given_name,
        ];
    }

    public function verifyPassword($password) {
        return password_verify($password, $this->password_hash);
    }

    public function getFullName() {
        return $this->given_name. ' ' . $this->name;
    }

}