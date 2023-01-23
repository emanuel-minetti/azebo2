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
use Service\log\AzeboLog;

class ApiController extends AbstractActionController
{
    /** @var JsonModel */
    protected JsonModel $invalidRequest;
    /** @var Request */
    protected $httpRequest;
    /** @var Response */
    protected $httpResponse;
    /** @var AzeboLog */
    protected $logger;

    public function __construct(AzeboLog $logger)
    {
        $this->logger = $logger;
        $this->invalidRequest = new JsonModel([
            'success' => false,
            'message' => "Invalid request",
        ]);
    }

    protected function prepare()
    {
        $this->httpRequest = $this->request;
        $this->httpResponse = $this->response;
    }

    protected function processResult($result, $userId): JsonModel {
        // refresh jwt ...
        $expire = time() + AuthorizationService::EXPIRE_TIME;
        $jwt = AuthorizationService::getJwt($expire, $userId);
        // ... and return response
        if (isset($result['day_parts']) && is_array($result['day_parts']) && sizeof($result['day_parts']) > 0) {
            $result['day_parts'] = array_map(function ($part) {
                return $part->getArrayCopy();
            }, $result['day_parts']);
        }
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
