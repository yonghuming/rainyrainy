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
use app\index\model\Student as StudentModel;

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
//如何判断返回解析的值是否成功
//解析json
        $user_obj = json_decode($res,true);
        $_SESSION['user'] = $user_obj;

        $user = new UserModel;
        UserModel::where('openid','oWkLpt2Iko8020TyLyzSE6dApN0Q')->delete();

        $rst = $user->where('openid',$openid)->find();
        //是否授权，没有授权授权，然后看是否绑定，没有绑定绑定
        //这样可以保证安全性，
        //即便授权了，也要验证，姓名，班级，座号字段是否齐全
        //这样可以保证准确性
        //绑定的时候要跟也有的信息进行鉴定
        //所以学生信息要放到单独的表里面
        //当然可以直接插到一张表里
        if($rst){
            //在确定授权成功的前提下这段代码都是要执行的
            session('user','langxm');
            session('openid',$openid);
            $this->redirect('Index/index');
        }else{
            $user->data($user_obj);
            //如果没有注册是要跟用户的信息绑定的
            if($user->save()){
                session('user','langxm');
                //添加到数据库之后，提交表单，验证身份
                return $this->fetch();
                //$this->redirect('Index/index');
            }else{
                return $user->getError();
            }
        }

    }

    public function bind(){
        echo '请求参数：';
        dump(input());
        echo 'name:'.input('stu_name');
        $stu = new StudentModel;
        $rst = $stu->where('stu_name',input('stu_name'))->find();
        if($rst && $rst['stu_name'] == input('stu_name') && $rst['stu_number']==input('stu_number')){
            dump($rst);
        }else{
            return $stu->getError();
        }

    }
}