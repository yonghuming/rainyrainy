<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
        if(session('?user')){
            return 'welcom you:'.session('user');
        }else{
            $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxcebdc16fde18fb3c&redirect_uri=http://www.eeyi.org/rainyrainy/public/index.php/index/callback/index&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect';
            header("Location:".$url);
        }
    }
}
