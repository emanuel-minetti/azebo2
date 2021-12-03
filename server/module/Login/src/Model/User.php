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

    public function exchangeArray($array)
    {
        $this->id = !empty($array['id']) ? $array['id'] : null;
        $this->username = !empty($array['username']) ? $array['username'] : null;
        $this->password_hash = !empty($array['password_hash']) ? $array['password_hash'] : null;
        $this->name = !empty($array['name']) ? $array['name'] : null;
        $this->given_name = !empty($array['given_name']) ? $array['given_name'] : null;
    }

    public function getArrayCopy()
    {
        $copy = [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'given_name' => $this->given_name,
        ];
        if (isset($this->password_hash)) {
            $copy['password_hash'] =  $this->password_hash;
        }
        return $copy;
    }

    public function verifyPassword($password) {
        return password_verify($password, $this->password_hash);
    }

    public function getFullName() {
        return $this->given_name. ' ' . $this->name;
    }

}