<?php
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

namespace Service;

use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Zend\Config\Factory;
use Zend\Http\Request;
use Zend\Http\Response;

class AuthorizationService {

    public const EXPIRE_TIME = 1800; // is half an hour

    public static function authorize(
        Request $request,
        Response $response,
        $acceptedMethods = ["GET"]) {
        if (array_search($request->getMethod(), $acceptedMethods) !== false) {
            $authHeader = $request->getHeader('Authorization');
            if ($authHeader) {
                list($jwt) = sscanf($authHeader->toString(), 'Authorization: Bearer %s');
                if ($jwt) {
                    $config = Factory::fromFile('./../server/config/autoload/jwt.config.php', true);
                    $secretKey = base64_decode($config->get('jwtKey'));
                    try {
                        $token = JWT::decode($jwt, $secretKey, ['HS512']);
                        // inject user_id into request
                        $request->getQuery()->user_id = $token->data->user_id;
                        return true;
                    }
                    catch (ExpiredException $e) {
                        // the token is expired
                        $response->setStatusCode(401);   // Unauthorized
                        $response->setContent("Token expired");
                    }
                    catch (Exception $e) {
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

    public static function getJwt($expire, $userId) {
        $config = Factory::fromFile('./../server/config/autoload/jwt.config.php', true);

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
