<?php

namespace App\Controller;

use Firebase\JWT\JWT;

class ApiController extends RestAppController
{

    protected $user = [
        'id' => 20,
        'name' => 'cake3',
        'password' => '123456',
        'mapping_id' => 10,
    ];

    protected $jwt = [
        'key' => 'handy1234',
        'alg' => 'HS256',
    ];

    protected $domain = 'cakephp3.local';

    public $uses = array();

    public function initialize()
    {
        parent::initialize();

        $this->getEventManager()->off($this->Security);

        if($this->request->is('post')) {
            $this->getEventManager()->off($this->Csrf);
        }
    }

    public function getUserInfo()
    {
        $this->autoRender = false;

        $info = $this->user;

        return $this->success($info);
    }

    public function getToken()
    {
        $token = $this->request->getData('token');

        try {

            $decode = JWT::decode($token);

            if (!empty($decode) && $decode->id == $this->user['id']) {

                if ($decode['domain'] == $this->domain) {
                    $token = ['id' => $this->user['id']];
                } else {
                    $token = ['id' => $this->user['mapping_id']];
                }

                JWT::encode($token, $this->jwt['key']);

                return $this->success(['token' => $token]);

            } else {
                return $this->fail('invalid token');
            }

        } catch (Exception $e) {
            return $this->fail('invalid token');
        }

    }

    public function checkUser($name, $password)
    {

        if ($name == $this->user['name'] && $password == $this->user['password']) {

            return $this->success(['token' => $this->generateToken()]);

        } else {

            return $this->fail('incorrect name or password');
        }
    }

    public function loginByToken()
    {
        try {

            $token = $this->request->getQuery('token');

            $decode = JWT::decode($token);

            if (!empty($decode) && $decode->id == $this->user['id']) {
                $this->Auth->setUser($this->user);
                return $this->redirect($this->Auth->redirectUrl());
            }

        } catch (Exception $e) {
            $this->Flash->error('username or password is incorrect');
        }
    }

    public function login()
    {
        $redirect = $this->request->getQuery('redirect');

        $this->redirect('http://laravel-sso.local/user/login?redirect=' . $redirect);
    }

    protected function generateToken()
    {
        $user = [
            'id' => $this->user['id'],
            'name' => $this->user['name'],
        ];

        $token = JWT::encode($user, $this->jwt['key'], $this->jwt['alg']);

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
