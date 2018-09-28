<?php

namespace Api\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function index()
    {

        for ($i = 1; $i < 10; $i++) {
            for ($j = 1; $j < $i + 1; $j++) {
                echo $j . '*' . $i . '=' . $j * $i . '&nbsp;';
            }
            echo '<br/>';
        }
    }
}