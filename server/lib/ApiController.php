<?php
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace AzeboLib;

use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Service\AuthorizationService;

class ApiController extends AbstractActionController
{
    /**
     * @var Request
     */
    protected $httpRequest;
    /**
     * @var Response
     */
    protected $httpResponse;

    protected function prepare()
    {
        $this->httpRequest = $this->request;
        $this->httpResponse = $this->response;
    }

    protected function processResult($result, $userId) {
        // refresh jwt ...
        $expire = time() + AuthorizationService::EXPIRE_TIME;
        $jwt = AuthorizationService::getJwt($expire, $userId);
        // ... and return response
        return new JsonModel([
            'success' => true,
            'data' => [
                'jwt' => $jwt,
                'expire' => $expire,
                'result' => $result,
            ],
        ]);
    }
}
