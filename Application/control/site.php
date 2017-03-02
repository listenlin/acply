<?php

class Site extends Acp_control
{
    public function indexAction()
    {
        $this->view('index', [
            'message' => 'hello Acply!'
        ]);
    }
}
