<?php
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti < e . minetti@posteo . de >
 * @link      https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright(c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */


namespace Login\Controller;

use Laminas\Config\Factory;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Login\Model\UserTable;
use Service\AuthorizationService;

class LoginController extends AbstractActionController
{
    private $table;

    public function __construct(UserTable $table)
    {
        $this->table = $table;
    }

    /** @noinspection PhpUnused */
    public function loginAction()
    {
        $request = $this->getRequest();
        $content = $request->getContent();
        $requestData = json_decode($content);
        $declineRequest = new JsonModel([
            'success' => false
        ]);
        // validate request method
        if ($request->getMethod() !== 'POST') {
            return $declineRequest;
        }

        // filter request data
        $username = trim($requestData->username);
        $password = trim($requestData->password);

        // validate request data
        if (mb_strlen($username) > 30 || mb_strlen($username) > 30) {
            return $declineRequest;
        }

//        // get user from DB
//        try {
//            $user = $this->table->getUserByUsername($username);
//        } catch (RuntimeException $e) {
//            return $declineRequest;
//        }

        $config = Factory::fromFile('./../server/config/ldap.config.php', true);
        $options = $config->ldap->toArray();
        $baseDn = $options['baseDn'];
        $host = $options['host'];
        $internBaseDn = "ou=intra,$baseDn";
        $externBaseDn = "ou=people,$baseDn";
        $internDn = "uid=$username,$internBaseDn";
        $externDn = "uid=$username,$externBaseDn";
        exec("ldapsearch -h $host -D '$internDn' -w $password -Z -b '$internDn'", $ldif, $val);
        if ($val !== 0) {
            exec("ldapsearch -h $host -D '$externDn' -w $password -Z -b '$externDn'", $ldif, $val);
        }
        if ($val === 0) {
            foreach ($ldif as $line) {
                if (substr($line, 0, 4) === "sn: ") {
                    $test['nachname'] = substr($line, 4);
                }
                if (substr($line, 0, 11) === 'givenName: ') {
                    $test['vorname'] = substr($line, 11);
                }
            }
        } else {
            $test = "Nicht authentifiziert";

        }
        var_dump($test);
        var_dump($val);
        die();

        // authenticate
        if ($user->verifyPassword($password)) {
            unset($user->password_hash);
            $expire = time() + AuthorizationService::EXPIRE_TIME;
            $jwt = AuthorizationService::getJwt($expire, $user->id);
            return new JsonModel([
                'success' => true,
                'data' => [
                    'jwt' => $jwt,
                    'user' => $user->getArrayCopy(),
                    'expire' => $expire,
                ],
            ]);
        } else { // not authenticated
            return $declineRequest;
        }
    }

}
