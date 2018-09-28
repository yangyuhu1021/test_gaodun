<?php

namespace Dxadmin\Controller;

use Common\Controller\BaseController;

class LogoutController extends BaseController
{
    public function index()
    {
        cookie('user', null);
        $url = U("login/index");
        echo '<script>window.parent.location.href="' . $url . '"</script>';
        exit(0);
    }
}
