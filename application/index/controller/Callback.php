<?php
/**
 * Created by PhpStorm.
 * User: xpro
 * Date: 2016/9/19 0019
 * Time: 08:28
 */

namespace app\index\controller;

use think\Controller;

use app\index\model\User as UserModel;

class Callback extends Controller
{
    public function index(){
        //查询有无openid
        //如果有直接session然后跳转
        //没有注册然后生成session跳转


        $appid = "wxcebdc16fde18fb3c";
        $secret = "f992b2f264067298ddddb721987ae3a6";
        $code = $_GET["code"];
        $get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$get_token_url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $res = curl_exec($ch);
        curl_close($ch);
        $json_obj = json_decode($res,true);

//根据openid和access_token查询用户信息
        $access_token = $json_obj['access_token'];
        $openid = $json_obj['openid'];
        $get_user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$get_user_info_url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $res = curl_exec($ch);
        curl_close($ch);

//解析json
        $user_obj = json_decode($res,true);
        $_SESSION['user'] = $user_obj;

        $user = new UserModel;

        $rst = $user->where('openid',$openid)->find();
        if($rst){
            session('user','langxm');
            $this->redirect('Index/index');
        }else{
            $user->data($user_obj);

            if($user->save()){
                return '用户[ ' . $user->nickname . ':' . $user->id . ' ]新增成功';
            }else{
                return $user->getError();
            }
        }

    }
}