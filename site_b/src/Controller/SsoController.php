<?php

namespace App\Controller;

use Firebase\JWT\JWT;

class SsoController extends AppController
{

    protected $domain = 'http://cakephp3.local';

    protected $ssoUri = 'http://laravel-sso.local';

    /*
    1       admin           10
    1408    huangchong      15
    1446    wangmingxing    20
    */
    protected $users = [
        [
            'id' => 10,
            'username' => 'admin',
            'password' => '123456',
            'mapping_id' => 1,
        ],
        [
            'id' => 15,
            'username' => 'huangchong',
            'password' => '123456',
            'mapping_id' => 1408,
        ],
        [
            'id' => 20,
            'username' => 'wangmingxing',
            'password' => '123456',
            'mapping_id' => 1446,
        ],
    ];

    protected $jwt = [
        'key' => 'handy1234',
        'alg' => 'HS256',
    ];

    protected $token_exp = 10;

    public function getUserInfo()
    {
        $username = $this->request->getData('username');

        $password = $this->request->getData('password');

        foreach ($this->users as $user){
            if ($username == $user['username'] && $password == $user['password']) {
                $data = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'token' => $this->generateToken($user['id']),
                ];

                return $this->success($data);

            }
        }

        return $this->fail('incorrect name or password');

    }

    public function getUserToken()
    {
        $token = $this->request->getData('token');

        try {

            $decode = JWT::decode($token, $this->jwt['key'], array($this->jwt['alg']));

            $key_id = 'id';
            if ($decode->domain != $this->domain) {
                $key_id = 'mapping_id';
            }

            foreach ($this->users as $user){
                if ($decode->id == $user[$key_id]) {
                    $token = $this->generateToken($user['id']);

                    return $this->success(['token' => $token]);
                }
            }

        } catch (\Exception $e) {
            return $this->fail('invalid token');
        }

        return $this->fail('invalid token');
    }

    public function logout()
    {
        $this->Auth->logout();

        $query = [
            'domain' => $this->domain,
        ];

        $url = $this->ssoUri . '/logout?' . http_build_query($query);

        return $this->redirect($url);
    }

    public function batchlogout()
    {
        header("Access-Control-Allow-Origin:{$this->ssoUri}");
        header("Access-Control-Allow-Credentials:true");

        $this->Auth->logout();
        exit;
        //return $this->redirect($this->ssoUri);
    }

    public function loginByToken()
    {
        try {

            $token = $this->request->getQuery('token');

            $decode = JWT::decode($token, $this->jwt['key'], array($this->jwt['alg']));

            //var_dump($decode);exit;

            if (!empty($decode)) {
                foreach ($this->users as $user){
                    if ($decode->id == $user['id']) {
                        $this->Auth->setUser($user);
                        return $this->redirect($this->Auth->redirectUrl());
                    }
                }
            }

        }
        /*
        catch(\Firebase\JWT\SignatureInvalidException $e) {  //签名不正确
             echo $e->getMessage();
        }
        catch(\Firebase\JWT\BeforeValidException $e) {  // 签名在某个时间点之后才能用
            echo $e->getMessage();
        }
        catch(\Firebase\JWT\ExpiredException $e) {  // token过期
            echo $e->getMessage();
        }
        */
        catch(\Exception $e) {
                $this->Flash->error('username or password is incorrect');
        }

        return $this->fail('invalid token');
    }

    public function loginRedirect()
    {
        $redirect = $this->request->getQuery('redirect');

        $query = [
            'domain' => $this->domain,
            'redirect' => $redirect,
        ];

        $url = $this->ssoUri .'?' . http_build_query($query);

        return $this->redirect($url);
    }

    protected function generateToken($userId)
    {
        $time = time();
        $payload = [
            /*
            'iss' => $this->domain, //Issuer
            'aud' => $this->domain, //Receiver
            'iat' => $time, //Issue TIme
            'exp' => $time + $this->token_exp,
            'data' => [
                'id' => $userId,
            ],
            */
            'id' => $userId,
        ];

        $token = JWT::encode($payload, $this->jwt['key'], $this->jwt['alg']);

        return $token;
    }

    protected function success($data = [], $msg = '')
    {
        $response = $this->response->withType('json');

        $content = json_encode([
            'status' => 1,
            'msg' => $msg,
            'data' => $data,
        ], JSON_FORCE_OBJECT);

        $response = $response->withStringBody($content);

        return $response;
    }

    protected function fail($msg)
    {
        $response = $this->response->withType('json');

        $content = json_encode([
            'status' => -1,
            'msg' => $msg,
        ]);

        $response = $response->withStringBody($content);

        return $response;
    }

}
