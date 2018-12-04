<?php

namespace  App\Controller;

class IndexController extends AppController
{

    public $uses = array();

    public function phpinfo()
    {
        $this->autoRender = false;

        //var_dump($this->Auth->user());exit;

        echo phpinfo(); exit;
    }


    public function index()
    {

        $user = $this->Auth->user();

        $this->set('user', $user);
    }

}
