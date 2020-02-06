<?php /** @noinspection PhpUnused */
/**
 * azebo2 is an application to print working time tables
 * Copyright (C) 2019  Emanuel Minetti
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2019 Emanuel Minetti
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