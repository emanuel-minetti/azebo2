<?php
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace Service;

use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Laminas\Config\Factory;
use Laminas\Stdlib\RequestInterface;
use Laminas\Stdlib\ResponseInterface;

class AuthorizationService
{

    public const EXPIRE_TIME = 1800; // is half an hour

    public static function authorize(
        RequestInterface $request,
        ResponseInterface $response,
        $acceptedMethods = ["GET"])
    {
        // test http method
        if (array_search($request->getMethod(), $acceptedMethods) !== false) {
            // test for authentication header
            $authHeader = $request->getHeader('Authorization');
            if ($authHeader) {
                // test for JWT
                /** @noinspection PhpUndefinedMethodInspection */
                list($jwt) = sscanf($authHeader->toString(), 'Authorization: Bearer %s');
                if ($jwt) {
                    $config = Factory::fromFile('./../server/config/jwt.config.php', true);
                    $secretKey = base64_decode($config->get('jwtKey'));
                    try {
                        $token = JWT::decode($jwt, $secretKey, ['HS512']);
                        // authentication success
                        // inject user_id into request
                        $request->getQuery()->user_id = $token->data->user_id;
                        return true;
                    } catch (ExpiredException $e) {
                        // the token is expired
                        $response->setStatusCode(401);   // Unauthorized
                        $response->setContent("Token expired");
                    } catch (Exception $e) {
                        // the token is invalid
                        $response->setStatusCode(401);  // Unauthorized

                    }
                } else {
                    // no token could be extracted
                    $response->setStatusCode(400);  // Bad Request
                }
            } else {
                // no auth-header was sent
                $response->setStatusCode(400);  // Bad Request
            }
        } else {
            // method not allowed
            $response->setStatusCode(405);  // Method Not Allowed
        }
        return false;
    }

    public static function getJwt($expire, $userId)
    {
        $config = Factory::fromFile('./../server/config/jwt.config.php', true);

        $issuedAt = time();
        $serverName = $config->get('serverName');

        $resData = [
            'iat' => $issuedAt,
            'iss' => $serverName,
            'exp' => $expire,
            'data' => [
                'user_id' => $userId,
            ],
        ];

        $secretKey = base64_decode($config->get('jwtKey'));
        return JWT::encode($resData, $secretKey, 'HS512');
    }
}
