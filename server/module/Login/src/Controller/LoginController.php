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

use AzeboLib\ApiController;
use Carry\Model\CarryTable;
use Laminas\Config\Factory;
use Laminas\View\Model\JsonModel;
use Login\Model\User;
use Login\Model\UserTable;
use RuntimeException;
use Service\log\AzeboLog;
use WorkingRule\Model\WorkingRuleTable;

class LoginController extends ApiController
{
    private $userTable;
    private $carryTable;
    private $ruleTable;

    public function __construct(AzeboLog $logger, UserTable $userTable, CarryTable $carryTable, WorkingRuleTable $ruleTable)
    {
        parent::__construct($logger);
        $this->userTable = $userTable;
        $this->carryTable = $carryTable;
        $this->ruleTable = $ruleTable;
    }

    /** @noinspection PhpUnused */
    public function loginAction()
    {
        $this->prepare();
        $content = $this->httpRequest->getContent();
        $requestData = json_decode($content);
        $declineRequest = new JsonModel([
            'success' => false
        ]);
        // validate request method
        if ($this->httpRequest->getMethod() !== 'POST') {
            return $declineRequest;
        }

        // filter request data
        $username = trim($requestData->username);
        $password = trim($requestData->password);

        // validate request data
        if (mb_strlen($username) > 30 || mb_strlen($password) > 30) {
            return $declineRequest;
        }

        $username = mb_strtolower($username);

        $config = Factory::fromFile('./../server/config/ldap.config.php', true);
        $useLdap = isset($config->useLdap) ? $config->useLdap : false;

        if ($useLdap) {
            // authenticate via LDAP
            /** @noinspection PhpUndefinedFieldInspection */
            $options = $config->ldap->toArray();
            $baseDn = $options['baseDn'];
            $host = $options['host'];
            $internBaseDn = "ou=intra,$baseDn";
            $externBaseDn = "ou=people,$baseDn";
            $internDn = "uid=$username,$internBaseDn";
            $externDn = "uid=$username,$externBaseDn";
            exec(
                "ldapsearch -h $host -D '$internDn' -w '$password' -Z -b '$internDn'",
                $ldif,
                $val
            );
            if ($val !== 0) {
                exec(
                    "ldapsearch -h $host -D '$externDn' -w '$password's -Z -b '$externDn'",
                    $ldif,
                    $val
                );
            }
            // DEBUG
            //$this->logger->debug("Intern: " . "ldapsearch -h $host -D '$internDn' -w $password -Z -b '$internDn'");
            //$this->logger->debug("Extern: " . "ldapsearch -h $host -D '$externDn' -w $password -Z -b '$externDn'");
            if ($val === 0) {
                $result['username'] = $username;
                foreach ($ldif as $line) {
                    if (substr($line, 0, 4) === "sn: ") {
                        $result['name'] = substr($line, 4);
                    }
                    if (substr($line, 0, 11) === 'givenName: ') {
                        $result['given_name'] = substr($line, 11);
                    }
                    if (substr($line, 0, 17) === 'udkDfnAaiStatus: ') {
                        $result['status'] = substr($line, 17);
                    }
                }
            } else {
                $result = false;
            }
            // DEBUG
//            foreach ($ldif as $line) {
//                if (substr($line, 0, 4) === "sn: ") {
//                    $this->logger->debug("Name: " . substr($line, 4));
//                }
//                if (substr($line, 0, 11) === 'givenName: ') {
//                    $this->logger->debug("Vorname: " . substr($line, 11));
//                }
//                if (substr($line, 0, 17) === 'udkDfnAaiStatus: ') {
//                    $this->logger->debug("Status: " . substr($line, 17));
//                }
//            }
            if (isset($result['status']) && strtolower($result['status']) === "false") {
                $result = false;
            }

            if (!$result) {
                return $declineRequest;
            }

            // get user from DB ...
            try {
                $user = $this->userTable->getUserByUsername($username);
            } catch (RuntimeException $e) {
                // ... or insert new user in tables `user`, `carry` and 'rule'
                $user = new User();
                $user->exchangeArray($result);
                $this->userTable->insert($user);
                $user = $this->userTable->getUserByUsername($username);
                $this->carryTable->insert($user);
                $this->ruleTable->insert($user);
                $user->new = true;
            }
        } else {
            // authenticate via DB table
            // get user from DB
            try {
                $user = $this->userTable->getUserByUsername($username);
            } catch (RuntimeException $e) {
                return $declineRequest;
            }

            // authenticate
            if (!$user->verifyPassword($password)) {
                // not authenticated
                return $declineRequest;
            }
            unset($user->password_hash);
            $this->logger->info("Logged in: username: $user->username");
        }
        // return response
        return $this->processResult($user->getArrayCopy(), $user->id);
    }
}
