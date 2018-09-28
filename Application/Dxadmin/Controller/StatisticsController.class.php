<?php

namespace Dxadmin\Controller;

class StatisticsController extends ComController
{
    function __construct()
    {
        parent::__construct();
        if (IS_POST) {
            if (!empty($_POST['code'])) {
                S("key", $_POST['code'], 3600);
                S('type', null);
                S('id', null);
                S('auth', false);
            }
            if (!empty($_POST['type'])) {
                S("type", $_POST['type'], 3600);
                S("id", $_POST['id'], 3600);
                S('key', null);
            }
            if (!empty($_POST['kong'])) {
                S('type', null);
                S('id', null);
                S('key', null);
                S('auth', false);
            }

        } elseif (IS_GET) {
            if (!empty($_GET['type'])) {
                S("type", $_GET['type'], 3600);
                S("id", $_GET['id'], 3600);
                $this->assign('get', $_GET);
            }
            if (!empty($_GET['auth'])) {
                if($_GET['auth'] == '1'){
                    S('auth', true);
                }else{
                    S('auth',false);
                }

            }
        }
    }

    function index()
    {
        if (IS_GET) {
            $model = new \Think\Model();
            $code['one'] = $model->query('SELECT * FROM qw_one_code');
            $code['ones'] = $model->query('SELECT * FROM qw_ones_code');
            $this->assign('code', $code);
            $this->display();
        } elseif (IS_POST) {
            $model = new \Think\Model();
            if (S('auth')) {
                if ($_POST['key'] == '1') {
                    if(S('type') == '1'){
                        $group=$model->query('SELECT * FROM qw_one_code where group_id="' .S('id'). '"');
                    }else{
                        $group=$model->query('SELECT * FROM qw_ones_code where group_id="' .S('id'). '"');
                    }
                    foreach ($group as $key) {
                        $time = strtotime(date('Y-m-d', time())) - (3600 * 24);
                        for ($i = 0; $i < 24; $i++) {
                            $data['time'][$i] = date("H:i", $time);
                            $data['sao'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                            $data['on'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                            $data['off'][$i] = $data['sao'][$i] - $data['on'][$i];
                            $time += 3600;
                        }
                    }
                    $data['title'] = '昨日扫码统计';

                } elseif ($_POST['key'] == '2') {

                    if(S('type') == '1'){
                        $group=$model->query('SELECT * FROM qw_one_code where group_id="' .S('id'). '"');
                    }else{
                        $group=$model->query('SELECT * FROM qw_ones_code where group_id="' .S('id'). '"');
                    }
                    foreach ($group as $key) {
                        $time = strtotime(date('Y-m-d', time())) - (3600 * 24 * 6);
                        for ($i = 0; $i < 7; $i++) {
                            $data['time'][$i] = date("m-d", $time);
                            $data['sao'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + (3600*24)) . '" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                            $data['on'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + (3600*24)) . '" and is_on="1" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                            $data['off'][$i] = $data['sao'][$i] - $data['on'][$i];
                            $time += (3600 * 24);
                        }
                    }
                    $data['title'] = '7日扫码统计';
                } elseif ($_POST['key'] == '3') {

                    if(S('type') == '1'){
                        $group=$model->query('SELECT * FROM qw_one_code where group_id="' .S('id'). '"');
                    }else{
                        $group=$model->query('SELECT * FROM qw_ones_code where group_id="' .S('id'). '"');
                    }
                    foreach ($group as $key) {
                        $time = strtotime(date('Y-m-d', time())) - (3600 * 24 * 29);
                        for ($i = 0; $i < 30; $i++) {
                            $data['time'][$i] = date("m-d", $time);
                            $data['sao'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + (3600*24)) . '" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                            $data['on'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + (3600*24)) . '" and is_on="1" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                            $data['off'][$i] = $data['sao'][$i] - $data['on'][$i];
                            $time += (3600 * 24);
                        }
                    }
                    $data['title'] = '30日扫码统计';
                } else {
                    $time = strtotime(date('Y-m-d', time()));
                    for ($i = 0; $i < 24; $i++) {
                        $data['time'][$i] = date("H:i", $time);
                        $time += 3600;
                    }
                    $model = new \Think\Model();
                    if(S('type') == '1'){
                        $group=$model->query('SELECT * FROM qw_one_code  where group_id="' .S('id'). '"');
                    }else{
                        $group=$model->query('SELECT * FROM qw_ones_code where group_id="' .S('id'). '"');
                    }
                    foreach ($group as $key) {
                        $time = strtotime(date('Y-m-d', time()));
                        for ($i = 0; $i < 24; $i++) {
                            $data['time'][$i] = date("H:i", $time);
                            $data['sao'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                            $data['on'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                            $data['off'][$i] = $data['sao'][$i] - $data['on'][$i];
                            $time += 3600;
                            if ($time > time()) {
                                $i = 24;
                            }
                        }
                    }
                    $data['title'] = '今日扫码统计';
                }
            } else {
                if ($_POST['key'] == '1') {
                    $time = strtotime(date('Y-m-d', time())) - (3600 * 24);

                    for ($i = 0; $i < 24; $i++) {
                        $data['time'][$i] = date("H:i", $time);
                        if (S('type')) {
                            $data['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and code_id="' . S('id') . '" and type="' . S('type') . '"'));
                            $data['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1" and code_id="' . S('id') . '" and type="' . S('type') . '"'));
                        } elseif (S('key')) {
                            $data['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and type="' . S('key') . '"'));
                            $data['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1" and type="' . S('key') . '"'));
                        } else {
                            $data['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '"'));
                            $data['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1"'));
                        }
                        $data['off'][$i] = $data['sao'][$i] - $data['on'][$i];
                        $time += 3600;
                    }
                    $data['title'] = '昨日扫码统计';

                } elseif ($_POST['key'] == '2') {
                    $time = strtotime(date('Y-m-d', time())) - (3600 * 24 * 6);
                    $data = $this->charts($time, 7, '7日扫码统计');
                } elseif ($_POST['key'] == '3') {
                    $time = strtotime(date('Y-m-d', time())) - (3600 * 24 * 29);
                    $data = $this->charts($time, 30, '30日扫码统计');
                } else {
                    $time = strtotime(date('Y-m-d', time()));
                    for ($i = 0; $i < 24; $i++) {
                        $data['time'][$i] = date("H:i", $time);
                        $time += 3600;
                    }
                    $time = strtotime(date('Y-m-d', time()));
                    $model = new \Think\Model();
                    for ($i = 0; $i < 24; $i++) {
                        if (S('type')) {
                            $data['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and code_id="' . S('id') . '" and type="' . S('type') . '"'));
                            $data['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1" and code_id="' . S('id') . '" and type="' . S('type') . '"'));
                        } elseif (S('key')) {
                            $data['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and type="' . S('key') . '"'));
                            $data['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1" and type="' . S('key') . '"'));
                        } else {
                            $data['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '"'));
                            $data['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1"'));
                        }
                        $data['off'][$i] = $data['sao'][$i] - $data['on'][$i];
                        $time += 3600;
                        if ($time > time()) {
                            $i = 24;
                        }
                    }
                    $data['title'] = '今日扫码统计';
                }
            }


            $this->ajaxReturn($data);
        }

    }

    function position()
    {
        if (IS_GET) {
            $model = new \Think\Model();
            $code['one'] = $model->query('SELECT * FROM qw_one_code');
            $code['ones'] = $model->query('SELECT * FROM qw_ones_code');
            $this->assign('code', $code);
            $this->display();
        } elseif (IS_POST) {
            $model = new \Think\Model();
            if(S('auth')){
                $position = array('北京', '天津', '上海', '重庆', '河北', '山西', '辽宁', '吉林', '黑龙江', '江苏', '浙江', '安徽', '福建', '江西', '山东', '河南', '湖北', '湖南', '广东', '海南', '四川', '贵州', '云南', '陕西', '甘肃', '青海', '台湾', '内蒙古', '广西', '西藏', '宁夏', '新疆', '香港', '澳门');
                if ($_POST['key'] == '1') {

                    if(S('type') == '1'){
                        $group=$model->query('SELECT * FROM qw_one_code  where group_id="' .S('id'). '"');
                    }else{
                        $group=$model->query('SELECT * FROM qw_ones_code where group_id="' .S('id'). '"');
                    }
                    foreach ($group as $key) {
                        $time = strtotime(date('Y-m-d', time())) - (3600 * 24);
                        for ($i = 0; $i < count($position); $i++) {
                            $res['position']['time'][$i] = $position[$i];
                            if (S('type')) {
                                $res['position']['sao'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + (3600 *24)) . '" and code_id="' . $key['id'] . '" and type="' . S('type') . '" and position="' . $position[$i] . '"'));
                                $res['position']['on'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + (3600 *24)) . '" and is_on="1" and code_id="' .$key['id'] . '" and type="' . S('type') . '" and position="' . $position[$i] . '"'));
                            }
                            $res['position']['off'][$i] = $res['position']['sao'][$i] - $res['position']['on'][$i];
                        }
                        for ($i = 0; $i < 24; $i++) {
                            $res['data']['time'][$i] = date("H:i", $time);
                            if (S('type')) {
                                $res['data']['sao'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                                $res['data']['on'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                            }
                            $res['data']['off'][$i] = $res['data']['sao'][$i] - $res['data']['on'][$i];
                            $time += 3600;
                        }
                    }


                    $res['data']['title'] = '昨日扫码统计';

                } elseif ($_POST['key'] == '2') {

                    if(S('type') == '1'){
                        $group=$model->query('SELECT * FROM qw_one_code  where group_id="' .S('id'). '"');
                    }else{
                        $group=$model->query('SELECT * FROM qw_ones_code where group_id="' .S('id'). '"');
                    }
                    foreach ($group as $key) {
                        $time = strtotime(date('Y-m-d', time())) - (3600 * 24 * 6);
                    for ($i = 0; $i < count($position); $i++) {
                        $res['position']['time'][$i] = $position[$i];
                        if (S('type')) {
                            $res['position']['sao'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + (3600 *24*7)) . '" and code_id="' . $key['id'] . '" and type="' . S('type') . '" and position="' . $position[$i] . '"'));
                            $res['position']['on'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + (3600 *24*7)) . '" and is_on="1" and code_id="' .$key['id'] . '" and type="' . S('type') . '" and position="' . $position[$i] . '"'));
                        }
                        $res['position']['off'][$i] = $res['position']['sao'][$i] - $res['position']['on'][$i];
                    }
                    for ($i = 0; $i < 7; $i++) {
                        $res['data']['time'][$i] = date("m-d", $time);
                        if (S('type')) {
                            $res['data']['sao'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + (3600 *24)) . '" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                            $res['data']['on'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + (3600 *24)) . '" and is_on="1" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                        }
                        $res['data']['off'][$i] = $res['data']['sao'][$i] - $res['data']['on'][$i];
                        $time += (3600 *24);
                    }
                }
                $res['data']['title'] ='7日扫码统计';

                } elseif ($_POST['key'] == '3') {
                    if(S('type') == '1'){
                        $group=$model->query('SELECT * FROM qw_one_code  where group_id="' .S('id'). '"');
                    }else{
                        $group=$model->query('SELECT * FROM qw_ones_code where group_id="' .S('id'). '"');
                    }
                    foreach ($group as $key) {
                        $time = strtotime(date('Y-m-d', time())) - (3600 * 24 * 29);
                        for ($i = 0; $i < count($position); $i++) {
                            $res['position']['time'][$i] = $position[$i];
                            if (S('type')) {
                                $res['position']['sao'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + (3600 *24*30)) . '" and code_id="' . $key['id'] . '" and type="' . S('type') . '" and position="' . $position[$i] . '"'));
                                $res['position']['on'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + (3600 *24*30)) . '" and is_on="1" and code_id="' .$key['id'] . '" and type="' . S('type') . '" and position="' . $position[$i] . '"'));
                            }
                            $res['position']['off'][$i] = $res['position']['sao'][$i] - $res['position']['on'][$i];
                        }
                        for ($i = 0; $i < 30; $i++) {
                            $res['data']['time'][$i] = date("m-d", $time);
                            if (S('type')) {
                                $res['data']['sao'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + (3600 *24)) . '" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                                $res['data']['on'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + (3600 *24)) . '" and is_on="1" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                            }
                            $res['data']['off'][$i] = $res['data']['sao'][$i] - $res['data']['on'][$i];
                            $time += (3600 *24);
                        }
                    }
                    $res['data']['title'] ='30日扫码统计';
                } else {
                    $time = strtotime(date('Y-m-d', time()));
                    for ($i = 0; $i < 24; $i++) {
                        $res['data']['time'][$i] = date("H:i", $time);
                        $time += 3600;
                    }

                    if(S('type') == '1'){
                        $group=$model->query('SELECT * FROM qw_one_code  where group_id="' .S('id'). '"');
                    }else{
                        $group=$model->query('SELECT * FROM qw_ones_code where group_id="' .S('id'). '"');
                    }
                    foreach ($group as $key) {
                        $time = strtotime(date('Y-m-d', time()));
                        for ($i = 0; $i < count($position); $i++) {
                            $res['position']['time'][$i] = $position[$i];
                            if (S('type')) {
                                $res['position']['sao'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + (3600 *24)) . '" and code_id="' . $key['id'] . '" and type="' . S('type') . '" and position="' . $position[$i] . '"'));
                                $res['position']['on'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + (3600 *24)) . '" and is_on="1" and code_id="' .$key['id'] . '" and type="' . S('type') . '" and position="' . $position[$i] . '"'));
                            }
                            $res['position']['off'][$i] = $res['position']['sao'][$i] - $res['position']['on'][$i];
                        }
                        for ($i = 0; $i < 24; $i++) {
                            $res['data']['time'][$i] = date("H:i", $time);
                            if (S('type')) {
                                $res['data']['sao'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                                $res['data']['on'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                            }
                            $res['data']['off'][$i] = $res['data']['sao'][$i] - $res['data']['on'][$i];
                            $time += 3600;
                            if ($time > time()) {
                                $i = 24;
                            }
                        }
                    }
                    $res['data']['title'] = '今日扫码统计';
                }
            }else{
                if ($_POST['key'] == '1') {
                    $time = strtotime(date('Y-m-d', time())) - (3600 * 24);
                    $res = $this->address($time);
                    for ($i = 0; $i < 24; $i++) {
                        $res['data']['time'][$i] = date("H:i", $time);
                        if (S('type')) {
                            $res['data']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and code_id="' . S('id') . '" and type="' . S('type') . '"'));
                            $res['data']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1" and code_id="' . S('id') . '" and type="' . S('type') . '"'));
                        } elseif (S('key')) {
                            $res['data']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and type="' . S('key') . '"'));
                            $res['data']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1" and type="' . S('key') . '"'));
                        } else {
                            $res['data']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '"'));
                            $res['data']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1"'));
                        }
                        $res['data']['off'][$i] = $res['data']['sao'][$i] - $res['data']['on'][$i];
                        $time += 3600;
                    }
                    $res['data']['title'] = '昨日扫码统计';

                } elseif ($_POST['key'] == '2') {
                    $time = strtotime(date('Y-m-d', time())) - (3600 * 24 * 6);
                    $position = array('北京', '天津', '上海', '重庆', '河北', '山西', '辽宁', '吉林', '黑龙江', '江苏', '浙江', '安徽', '福建', '江西', '山东', '河南', '湖北', '湖南', '广东', '海南', '四川', '贵州', '云南', '陕西', '甘肃', '青海', '台湾', '内蒙古', '广西', '西藏', '宁夏', '新疆', '香港', '澳门');
                    for ($i = 0; $i < count($position); $i++) {
                        $res['position']['time'][$i] = $position[$i];
                        if (S('type')) {
                            $res['position']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24*7) . '" and code_id="' . S('id') . '" and type="' . S('type') . '" and position="' . $position[$i] . '"'));
                            $res['position']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24*7) . '" and is_on="1" and code_id="' . S('id') . '" and type="' . S('type') . '" and position="' . $position[$i] . '"'));
                        } elseif (S('key')) {
                            $res['position']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24*7) . '" and type="' . S('key') . '" and position="' . $position[$i] . '"'));
                            $res['position']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24*7) . '" and is_on="1" and type="' . S('key') . '" and position="' . $position[$i] . '"'));
                        } else {
                            $res['position']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24*7) . '" and position="' . $position[$i] . '"'));
                            $res['position']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24*7) . '" and is_on="1" and position="' . $position[$i] . '"'));
                        }
                        $res['position']['off'][$i] = $res['position']['sao'][$i] - $res['position']['on'][$i];
                    }
                    $res['data'] = $this->charts($time, 7, '7日扫码统计');

                } elseif ($_POST['key'] == '3') {
                    $time = strtotime(date('Y-m-d', time())) - (3600 * 24 * 29);
                    $position = array('北京', '天津', '上海', '重庆', '河北', '山西', '辽宁', '吉林', '黑龙江', '江苏', '浙江', '安徽', '福建', '江西', '山东', '河南', '湖北', '湖南', '广东', '海南', '四川', '贵州', '云南', '陕西', '甘肃', '青海', '台湾', '内蒙古', '广西', '西藏', '宁夏', '新疆', '香港', '澳门');
                    for ($i = 0; $i < count($position); $i++) {
                        $res['position']['time'][$i] = $position[$i];
                        if (S('type')) {
                            $res['position']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24*30) . '" and code_id="' . S('id') . '" and type="' . S('type') . '" and position="' . $position[$i] . '"'));
                            $res['position']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24*30) . '" and is_on="1" and code_id="' . S('id') . '" and type="' . S('type') . '" and position="' . $position[$i] . '"'));
                        } elseif (S('key')) {
                            $res['position']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24*30) . '" and type="' . S('key') . '" and position="' . $position[$i] . '"'));
                            $res['position']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24*30) . '" and is_on="1" and type="' . S('key') . '" and position="' . $position[$i] . '"'));
                        } else {
                            $res['position']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24*30) . '" and position="' . $position[$i] . '"'));
                            $res['position']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24*30) . '" and is_on="1" and position="' . $position[$i] . '"'));
                        }
                        $res['position']['off'][$i] = $res['position']['sao'][$i] - $res['position']['on'][$i];
                    }
                    $res['data'] = $this->charts($time, 30, '30日扫码统计');
                } else {
                    $time = strtotime(date('Y-m-d', time()));

                    $res = $this->address($time);
                    for ($i = 0; $i < 24; $i++) {
                        $res['data']['time'][$i] = date("H:i", $time);
                        $time += 3600;
                    }
                    $time = strtotime(date('Y-m-d', time()));

                    for ($i = 0; $i < 24; $i++) {
                        if (S('type')) {
                            $res['data']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and code_id="' . S('id') . '" and type="' . S('type') . '"'));
                            $res['data']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1" and code_id="' . S('id') . '" and type="' . S('type') . '"'));
                        } elseif (S('key')) {
                            $res['data']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and type="' . S('key') . '"'));
                            $res['data']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1" and type="' . S('key') . '"'));
                        } else {
                            $res['data']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '"'));
                            $res['data']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1"'));
                        }
                        $res['data']['off'][$i] = $res['data']['sao'][$i] - $res['data']['on'][$i];
                        $time += 3600;
                        if ($time > time()) {
                            $i = 24;
                        }
                    }
                    $res['data']['title'] = '今日扫码统计';
                }
            }


            $this->ajaxReturn($res);
        }

    }

    function phone()
    {
        if (IS_GET) {
            $model = new \Think\Model();
            $code['one'] = $model->query('SELECT * FROM qw_one_code');
            $code['ones'] = $model->query('SELECT * FROM qw_ones_code');
            $this->assign('code', $code);
            $this->display();
        } elseif (IS_POST) {
            $model = new \Think\Model();
            $res=array();
            if(S('auth')){
                if ($_POST['key'] == '1') {

                    if(S('type') == '1'){
                        $group=$model->query('SELECT * FROM qw_one_code  where group_id="' .S('id'). '"');
                    }else{
                        $group=$model->query('SELECT * FROM qw_ones_code where group_id="' .S('id'). '"');
                    }
                    foreach ($group as $key) {
                        $time = strtotime(date('Y-m-d', time())) - (3600 * 24);
                        $res['position']['iphone'] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24) . '" and code_id="' . $key['id'] . '" and type="' . S('type') . '" and phone="1"'));
                        $res['position']['Android'] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24) . '" and  code_id="' .$key['id'] . '" and type="' . S('type') . '" and phone="2"'));
                        for ($i = 0; $i < 24; $i++) {
                            $res['data']['time'][$i] = date("H:i", $time);
                            if (S('type')) {
                                $res['data']['sao'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                                $res['data']['on'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                            }
                            $res['data']['off'][$i] = $res['data']['sao'][$i] - $res['data']['on'][$i];
                            $time += 3600;
                        }
                    }
                    $res['data']['title'] = '昨日扫码统计';

                } elseif ($_POST['key'] == '2') {
                    if(S('type') == '1'){
                        $group=$model->query('SELECT * FROM qw_one_code  where group_id="' .S('id'). '"');
                    }else{
                        $group=$model->query('SELECT * FROM qw_ones_code where group_id="' .S('id'). '"');
                    }
                    foreach ($group as $key) {
                        $time = strtotime(date('Y-m-d', time())) - (3600 * 24 * 6);
                        $res['position']['iphone'] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24*7) . '" and code_id="' . $key['id'] . '" and type="' . S('type') . '" and phone="1"'));
                        $res['position']['Android'] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24*7) . '" and  code_id="' .$key['id'] . '" and type="' . S('type') . '" and phone="2"'));
                        for ($i = 0; $i < 7; $i++) {
                            $res['data']['time'][$i] = date("m-d", $time);
                            if (S('type')) {
                                $res['data']['sao'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + (3600 *24)) . '" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                                $res['data']['on'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + (3600 *24)) . '" and is_on="1" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                            }
                            $res['data']['off'][$i] = $res['data']['sao'][$i] - $res['data']['on'][$i];
                            $time += (3600 *24);
                        }
                    }
                    $res['data']['title'] = '7日扫码统计';
                } elseif ($_POST['key'] == '3') {
                    if(S('type') == '1'){
                        $group=$model->query('SELECT * FROM qw_one_code  where group_id="' .S('id'). '"');
                    }else{
                        $group=$model->query('SELECT * FROM qw_ones_code where group_id="' .S('id'). '"');
                    }
                    foreach ($group as $key) {
                        $time = strtotime(date('Y-m-d', time())) - (3600 * 24 * 29);
                        $res['position']['iphone'] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24*30) . '" and code_id="' . $key['id'] . '" and type="' . S('type') . '" and phone="1"'));
                        $res['position']['Android'] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24*30) . '" and  code_id="' .$key['id'] . '" and type="' . S('type') . '" and phone="2"'));
                        for ($i = 0; $i < 30; $i++) {
                            $res['data']['time'][$i] = date("m-d", $time);
                            if (S('type')) {
                                $res['data']['sao'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + (3600 *24)) . '" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                                $res['data']['on'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + (3600 *24)) . '" and is_on="1" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                            }
                            $res['data']['off'][$i] = $res['data']['sao'][$i] - $res['data']['on'][$i];
                            $time += (3600 *24);
                        }
                    }
                    $res['data']['title'] = '30日扫码统计';
                } else {
                    $time = strtotime(date('Y-m-d', time()));
                    for ($i = 0; $i < 24; $i++) {
                        $res['data']['time'][$i] = date("H:i", $time);
                        $time += 3600;
                    }
                    if(S('type') == '1'){
                        $group=$model->query('SELECT * FROM qw_one_code  where group_id="' .S('id'). '"');
                    }else{
                        $group=$model->query('SELECT * FROM qw_ones_code where group_id="' .S('id'). '"');
                    }
                    foreach ($group as $key) {
                        $time = strtotime(date('Y-m-d', time()));
                        $res['position']['iphone'] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24) . '" and code_id="' . $key['id'] . '" and type="' . S('type') . '" and phone="1"'));
                        $res['position']['Android'] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24) . '" and  code_id="' .$key['id'] . '" and type="' . S('type') . '" and phone="2"'));
                        for ($i = 0; $i < 24; $i++) {
                            $res['data']['time'][$i] = date("H:i", $time);
                            if (S('type')) {
                                $res['data']['sao'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                                $res['data']['on'][$i] += count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1" and code_id="' . $key['id'] . '" and type="' . S('type') . '"'));
                            }
                            $res['data']['off'][$i] = $res['data']['sao'][$i] - $res['data']['on'][$i];
                            $time += 3600;
                            if ($time > time()) {
                                $i = 24;
                            }
                        }
                    }
                    $res['data']['title'] = '今日扫码统计';
                }
            }else{
                if ($_POST['key'] == '1') {
                    $time = strtotime(date('Y-m-d', time())) - (3600 * 24);

                    $res = $this->phone_Model($time,1);
                    for ($i = 0; $i < 24; $i++) {
                        $res['data']['time'][$i] = date("H:i", $time);
                        if (S('type')) {
                            $res['data']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and code_id="' . S('id') . '" and type="' . S('type') . '"'));
                            $res['data']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1" and code_id="' . S('id') . '" and type="' . S('type') . '"'));
                        } elseif (S('key')) {
                            $res['data']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and type="' . S('key') . '"'));
                            $res['data']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1" and type="' . S('key') . '"'));
                        } else {
                            $res['data']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '"'));
                            $res['data']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1"'));
                        }
                        $res['data']['off'][$i] = $res['data']['sao'][$i] - $res['data']['on'][$i];
                        $time += 3600;
                    }
                    $res['data']['title'] = '昨日扫码统计';

                } elseif ($_POST['key'] == '2') {
                    $time = strtotime(date('Y-m-d', time())) - (3600 * 24 * 6);
                    $res = $this->phone_Model($time,7);
                    $res['data'] = $this->charts($time, 7, '7日扫码统计');

                } elseif ($_POST['key'] == '3') {
                    $time = strtotime(date('Y-m-d', time())) - (3600 * 24 * 29);
                    $res = $this->phone_Model($time,30);
                    $res['data'] = $this->charts($time, 30, '30日扫码统计');
                } else {
                    $time = strtotime(date('Y-m-d', time()));
                    $res = $this->phone_Model($time,1);
                    for ($i = 0; $i < 24; $i++) {
                        $res['data']['time'][$i] = date("H:i", $time);
                        $time += 3600;
                    }
                    $time = strtotime(date('Y-m-d', time()));

                    for ($i = 0; $i < 24; $i++) {
                        if (S('type')) {
                            $res['data']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and code_id="' . S('id') . '" and type="' . S('type') . '"'));
                            $res['data']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1" and code_id="' . S('id') . '" and type="' . S('type') . '"'));
                        } elseif (S('key')) {
                            $res['data']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and type="' . S('key') . '"'));
                            $res['data']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1" and type="' . S('key') . '"'));
                        } else {
                            $res['data']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '"'));
                            $res['data']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600) . '" and is_on="1"'));
                        }
                        $res['data']['off'][$i] = $res['data']['sao'][$i] - $res['data']['on'][$i];
                        $time += 3600;
                        if ($time > time()) {
                            $i = 24;
                        }
                    }
                    $res['data']['title'] = '今日扫码统计';
                }
            }

            $this->ajaxReturn($res);
        }
    }

    function charts($time, $key, $title)
    {
        $model = new \Think\Model();
        for ($i = 0; $i < $key; $i++) {
            $data['time'][$i] = date("m-d", $time);
            if (S('type')) {
                $data['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24) . '" and code_id="' . S('id') . '" and type="' . S('type') . '"'));
                $data['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24) . '" and is_on="1" and code_id="' . S('id') . '" and type="' . S('type') . '"'));
            } elseif (S('key')) {
                $data['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24) . '" and type="' . S('key') . '"'));
                $data['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24) . '" and is_on="1" and type="' . S('key') . '"'));
            } else {
                $data['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . $time . '" and time < "' . ($time + 3600 * 24) . '"'));
                $data['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . $time . '" and time < "' . ($time + 3600 * 24) . '" and is_on="1"'));

            }
            $data['off'][$i] = $data['sao'][$i] - $data['on'][$i];
            $time += (3600 * 24);
        }
        $data['title'] = $title;
        return $data;
    }

    function phone_Model($time,$key)
    {
        $model = new \Think\Model();
        if (S('type')) {
            $res['position']['iphone']  = count($model->query('SELECT * FROM qw_user_code where time >= "' . $time . '" and time < "' . ($time + 3600 * 24 * $key) . '" and code_id="' . S('id') . '" and type="' . S('type') . '" and phone="1"'));
            $res['position']['Android'] = count($model->query('SELECT * FROM qw_user_code where time >= "' . $time . '" and time < "' . ($time + 3600 * 24 * $key) . '" and code_id="' . S('id') . '" and type="' . S('type') . '" and phone="2"'));
        } elseif (S('key')) {
            $res['position']['iphone']  = count($model->query('SELECT * FROM qw_user_code where time >= "' . $time . '" and time < "' . ($time + 3600 * 24 * $key) . '" and type="' . S('key') . '" and phone="1"'));
            $res['position']['Android'] = count($model->query('SELECT * FROM qw_user_code where time >= "' . $time . '" and time < "' . ($time + 3600 * 24 * $key) . '" and type="' . S('key') . '" and phone="2"'));
        } else {
            $res['position']['iphone']  = count($model->query('SELECT * FROM qw_user_code where time >= "' . $time . '" and time < "' . ($time + 3600 * 24 * $key) . '" and phone="1"'));
            $res['position']['Android'] = count($model->query('SELECT * FROM qw_user_code where time >= "' . $time . '" and time < "' . ($time + 3600 * 24 * $key) . '" and phone="2"'));
        }
        return $res;
    }

    function address($time)
    {
        $model = new \Think\Model();
        $position = array('北京', '天津', '上海', '重庆', '河北', '山西', '辽宁', '吉林', '黑龙江', '江苏', '浙江', '安徽', '福建', '江西', '山东', '河南', '湖北', '湖南', '广东', '海南', '四川', '贵州', '云南', '陕西', '甘肃', '青海', '台湾', '内蒙古', '广西', '西藏', '宁夏', '新疆', '香港', '澳门');
        for ($i = 0; $i < count($position); $i++) {
            $res['position']['time'][$i] = $position[$i];
            if (S('type')) {
                $res['position']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24) . '" and code_id="' . S('id') . '" and type="' . S('type') . '" and position="' . $position[$i] . '"'));
                $res['position']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24) . '" and is_on="1" and code_id="' . S('id') . '" and type="' . S('type') . '" and position="' . $position[$i] . '"'));
            } elseif (S('key')) {
                $res['position']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24) . '" and type="' . S('key') . '" and position="' . $position[$i] . '"'));
                $res['position']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24) . '" and is_on="1" and type="' . S('key') . '" and position="' . $position[$i] . '"'));
            } else {
                $res['position']['sao'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24) . '" and position="' . $position[$i] . '"'));
                $res['position']['on'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "' . ($time) . '" and time < "' . ($time + 3600 * 24) . '" and is_on="1" and position="' . $position[$i] . '"'));
            }
            $res['position']['off'][$i] = $res['position']['sao'][$i] - $res['position']['on'][$i];
        }
        return $res;
    }
}