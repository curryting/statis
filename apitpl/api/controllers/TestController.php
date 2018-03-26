<?php

namespace api\controllers;
use Yii;
use yii\rest\Controller;
use yii\db\Query;
use api\models\Test;
use yii\web\Response;

class TestController extends Controller

{

    public function beforeAction($action)
    {
       return Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }

    public function actionIndex()
    {
       
        $Test = new Test();
        $res  = $Test->lists();

        $data = ['status'=>1,'info'=>'测试API接口','lists'=>$res];
        return $data;
    }

    //地级市数据中心
    public function actionAccount_city()
    {

        $request     = Yii::$app->request;
        $province_id = $request->get('province_id','');
        $city_id     = $request->get('city_id',''); 
        $area_id     = $request->get('area_id','');

        $Test = new Test();
        $res  = $Test->account_city($province_id,$city_id,$area_id);

        $data = ['status'=>1,'info'=>'获取成功','lists'=>$res];
        return $data;
    }

    //市辖区（县）级教育局数据中心
    public function actionAccount_list()
    {
        $request     = Yii::$app->request;
        $province_id = $request->get('province_id','');
        $city_id     = $request->get('city_id',''); 
        $area_id     = $request->get('area_id','');

        $Test = new Test();
        $res  = $Test->account_list($province_id,$city_id,$area_id);

        $data = ['status'=>1,'info'=>'获取成功','lists'=>$res];
        return $data;
    }

    //年级/班级数据中心
    public function actionAccount_class()
    {
        $request     = Yii::$app->request;
        $province_id = $request->get('province_id','');
        $city_id     = $request->get('city_id',''); 
        $area_id     = $request->get('area_id','');

        $Test = new Test();
        $res  = $Test->account_class($province_id,$city_id,$area_id);

        $data = ['status'=>1,'info'=>'获取成功','lists'=>$res];
        return $data;
    }

    //城市列表
    public function actionArea_list()
    {   
        $request = Yii::$app->request;
        $Test = new Test();
        $res  = $Test->area_list();

        $data = ['status'=>1,'info'=>'获取成功','lists'=>$res];
        return $data;
    }


    //登录
    public function actionLogin()
    {
        $request = Yii::$app->request;
        $user_name = $request->get('user_name',''); 
        $password  = $request->get('password','');

        $Test = new Test();
        $data  = $Test->login($user_name,$password);


        return $data;
    }

    //退出登录
    public function actionLogout(){
        $Test = new Test();
        $data  = $Test->logout();

        return $data;
    }


   /*修改密码
    *user_id：用户ID
    *user_name：用户名
    *prepwd：原密码
    *pwd：新密码
    *repwd：确认新密码
   */
    public function actionModify_pwd(){
        $request   = Yii::$app->request;

        $user_id   = $request->post('user_id','');
        $user_name = $request->post('user_name','');
        $prepwd    = $request->post('prepwd','');
        $pwd       = $request->post('pwd','');
        $repwd     = $request->post('repwd','');

        $Test = new Test();
        $data  = $Test->modify_pwd($user_id,$user_name,$prepwd,$pwd,$repwd);
        return $data;
    }

    //获取用户权限的区域
    public function actionUser_zone(){
        $request = Yii::$app->request;
        $Test = new Test();
        $user_id = $request->get('user_id','');
        $data  = $Test->user_zone($user_id);

        return $data;
    }

}