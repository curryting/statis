<?php
namespace api\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\db\Expression;
use yii\db\session;

class Test extends Model
{
    public function lists()
    {

        $query = new Query();
        $res =  $query
            ->from('article')
            ->select(new Expression("*,from_unixtime(created_at,'%Y-%m-%d')  created_at"))
            ->orderBy('id DESC')
            ->all();

        return $res;   
    }
     

    public function account_city($province_id,$city_id,$area_id)
    {
        $w = '';
        if(!empty($province_id))    $w['province_id']  = $province_id;
        if(!empty($city_id))        $w['city_id']      = $city_id;
        if(!empty($area_id))        $w['area_id']      = $area_id;

        $query  = new Query();
        $res    =  $query
                ->from('account')
                ->select('id,province_id,city_id,area_id,title,teacher_count,student_count')
                ->where($w)
                ->all();

        //城市分组
        $city_area = [];
        foreach ($res as $k => $v) {
            $areas          = $query->from('province_city_area')->select('name')->where(['id'=>$v['area_id']])->one();
            $v['area_name'] = $areas['name'];
            $city_area[]    = $v;
        }

        //地区分组
        $new_city_area = [];
        foreach ($city_area as $k => $v) {
            $school_counts = $query->from('statis_school_expect')->select('counts')->where(['areaid'=>$v['area_id']])->one(); 
            $new_city_area[$v['area_id']]['school_counts']  = intval($school_counts['counts']); //学校总数
            $new_city_area[$v['area_id']]['schools'][]      = $v;
        }

        //全校数量
        foreach ($new_city_area as $k => $v) {
            $new_city_area[$k]['area_name'] = $v['schools'][0]['area_name'];
            $new_city_area[$k]['school_online_counts'] = count($v['schools']); //上线学校数

            //全区学校上线率
            $new_city_area[$k]['school_avg'] = (count($v['school_counts']) > 0) ? (round(count($v['schools'])/intval($v['school_counts']),3)*100).'%' : '0%';

            $tea_counts = 0;        //教职工总人数
            $tea_online_counts = 0; //教职工导入人数
        
            $stu_counts = 0;        //学生总人数
            $stu_online_counts = 0; //学生导入人数

            foreach ($v['schools'] as $kk => $vv) {

                //老师相关
                $tea_counts += intval($vv['teacher_count']);    
                $teas = $query->from('contacts_member')->where(['qid'=>$vv['id'],'type'=>1])->count(); 
                $tea_online_counts += $teas;
                $teas_focus = $query->from('contacts_member')->where(['qid'=>$vv['id'],'type'=>1,'fstatus'=>['>',0]])->count();  
                
                //学生相关
                $stu_counts += intval($vv['student_count']);    //所有学生
                $stus = $query->from('contacts_member')->where(['qid'=>$vv['id'],'type'=>2])->count();
                $stu_online_counts += $stus;
                $stus_focus = $query->from('contacts_member')->where(['qid'=>$vv['id'],'type'=>2,'fstatus'=>['>',0]])->count();  
            }

            $new_city_area[$k]['tea_counts']        = $tea_counts;
            $new_city_area[$k]['tea_online_counts'] = $tea_online_counts;   
            $new_city_area[$k]['tea_online_focus']  = intval($teas_focus);
            $new_city_area[$k]['teas_focus_avg']    = ($tea_online_counts > 0) ? (round($teas_focus/$tea_online_counts,3)*100).'%' : '0%';

            $new_city_area[$k]['stu_counts']        = $stu_counts;
            $new_city_area[$k]['stu_online_counts'] = $stu_online_counts;   
            $new_city_area[$k]['stu_online_focus']  = intval($stus_focus);
            $new_city_area[$k]['stus_focus_avg']    = ($stu_online_counts > 0) ? (round($stus_focus/$stu_online_counts,3)*100).'%' : '0%';

            //全校总数
            $new_city_area[$k]['member_counts'] = $tea_counts + $stu_counts;
            //总关注率
            $member_focus = intval($teas_focus) + intval($stus_focus);
            $member_online_counts = $tea_online_counts + $stu_online_counts;
            $new_city_area[$k]['member_focus_avg'] = ($member_focus > 0) ? (round($member_focus/$member_online_counts,3)*100).'%' : '0%';
            //导入总数
            $new_city_area[$k]['member_online_counts'] = $member_online_counts; 
            //关注总数
            $new_city_area[$k]['member_focus'] = $member_focus;

        }

        return $new_city_area = array_values($new_city_area);
    } 


    public function account_list($province_id,$city_id,$area_id)
    {

        $w = '';
        if(!empty($province_id))    $w['province_id']  = $province_id;
        if(!empty($city_id))        $w['city_id']      = $city_id;
        if(!empty($area_id))        $w['area_id']      = $area_id;


        $query   = new Query();
        $account =  $query 
            ->from('account')
            ->select('id,province_id,city_id,area_id,title,teacher_count,student_count,school_type,teacher_count,student_count')
            ->where($w)
            ->all();

        $schoolType = [ 0=>'其它',1=>'幼儿园',2=>'小学',3=>'初中',4=>'高中',5=>'大学' ];    

        $new_account = [];
        foreach ($account as $k => $v) {
            $v['type_info'] = $schoolType[$v['school_type']];
            $areas          = $query->from('province_city_area')->select('name')->where(['id'=>$v['area_id']])->one();
            $v['area_name'] = $areas['name'];
            $new_account[$v['area_id']][] = $v;
        }

        $data = [];
        foreach ($new_account as $k => $v) {
            foreach ($v as $kk => $vv) {
               $data[] = $vv;
            }
        }

        foreach ($data as $kk => $vv) {
            //老师相关
            $tea_counts = intval($vv['teacher_count']);    
            $teas = $query->from('contacts_member')->where(['qid'=>$vv['id'],'type'=>1])->count(); 
            $tea_online_counts = $teas;
            $teas_focus = $query->from('contacts_member')->where(['qid'=>$vv['id'],'type'=>1,'fstatus'=>['>',0]])->count();  
            
            //学生相关
            $stu_counts = intval($vv['student_count']);    //所有学生
            $stus = $query->from('contacts_member')->where(['qid'=>$vv['id'],'type'=>2])->count();
            $stu_online_counts = $stus;
            $stus_focus = $query->from('contacts_member')->where(['qid'=>$vv['id'],'type'=>2,'fstatus'=>['>',0]])->count();  


            $data[$kk]['tea_counts']        = $tea_counts;
            $data[$kk]['tea_online_counts'] = $tea_online_counts;   
            $data[$kk]['tea_online_focus']  = intval($teas_focus);
            $data[$kk]['teas_focus_avg']    = ($tea_online_counts > 0) ? (round($teas_focus/$tea_online_counts,3)*100).'%' : '0%';

            $data[$kk]['stu_counts']        = $stu_counts;
            $data[$kk]['stu_online_counts'] = $stu_online_counts;   
            $data[$kk]['stu_online_focus']  = intval($stus_focus);
            $data[$kk]['stus_focus_avg']    = ($stu_online_counts > 0) ? (round($stus_focus/$stu_online_counts,3)*100).'%' : '0%';

            //全校总数
            $data[$kk]['member_counts'] = $tea_counts + $stu_counts;
            //总关注率
            $member_focus = intval($teas_focus) + intval($stus_focus);
            $member_online_counts = $tea_online_counts + $stu_online_counts;
            $data[$kk]['member_focus_avg'] = ($member_focus > 0) ? (round($member_online_counts/$member_focus,3)*100).'%' : '0%';
            //导入总数
            $data[$kk]['member_online_counts'] = $member_online_counts; 
            //关注总数
            $data[$kk]['member_focus'] = $member_focus;
        }

        return $data;
    }


    public function account_class($province_id,$city_id,$area_id)
    {

       
        // $extend & 4095 入学年份
        // $extend >> 12 学校类型

        $w = '';
        if(!empty($province_id))    $w['province_id']  = $province_id;
        if(!empty($city_id))        $w['city_id']      = $city_id;
        if(!empty($area_id))        $w['area_id']      = $area_id;
        $query   = new Query();

        $account =  $query 
            ->from('account')
            ->select('id,province_id,city_id,area_id,title,teacher_count,student_count,school_type,teacher_count,student_count')
            ->where($w)
            ->all();

        $schoolType = [ 0=>'其它',1=>'幼儿园',2=>'小学',3=>'初中',4=>'高中',5=>'大学' ];    

        $new_account = [];
        foreach ($account as $k => $v) {
            $v['type_info'] = $schoolType[$v['school_type']];
            $areas          = $query->from('province_city_area')->select('name')->where(['id'=>$v['area_id']])->one();
            $v['area_name'] = $areas['name'];
            $new_account[$v['area_id']][] = $v;
        }

        $data = [];
        foreach ($new_account as $k => $v) {
            foreach ($v as $kk => $vv) {
               $data[] = $vv;
            }
        }

        //获取年级
        foreach ($data as $k => $v) {
            $grades = $query->from('contacts_group')->select('id,pid,title,type,extend')->where(['qid'=>$v['id'],'type'=>5,'status'=>1])->all();
            foreach ($grades as $kk => $vv) {
                $grades[$kk]['extend_info'] = $vv['title'];
                $classes = $query->from('contacts_group')->select('id,pid,title,type,extend')->where(['pid'=>$vv['id'],'type'=>6,'status'=>1])->all();

                foreach ($classes as $key => $val) {
                    $classes[$key]['grade'] = $vv['title'];
                    $classes[$key]['area_name'] = $v['area_name'];
                    $classes[$key]['area_id']   = $v['area_id'];
                    $classes[$key]['type_info'] = $v['type_info'];
                    $classes[$key]['school_title'] = $v['title'];

                    //筛选属于这个班级的成员
                    $members = $query->from('contacts_group_link')->select('memid')->where(['qid'=>$v['id'],'groupid'=>$val['id']])->all();
                    $stu_online_counts = count($members);
                    $memids_arr = [];
                    foreach ($members as $value) {
                        $memids_arr[] = $value['memid'];
                    }

                    $stu_online_focus = $query->from('contacts_member')->where(['fstatus'=>['>',0],'id'=>['IN',$memids_arr]])->count();  
                    $classes[$key]['stus_focus_avg'] = ($stu_online_counts > 0) ? (round($stu_online_focus/$stu_online_counts,3)*100).'%' : '0%';
                    $classes[$key]['stu_counts'] = 0; //班级实际人数暂时为0;
                    $classes[$key]['stu_online_counts'] = intval($stu_online_counts);
                    $classes[$key]['stu_online_focus']  = intval($stu_online_focus);
                }

                $grades[$kk]['classes'] = $classes;
            }

            $data[$k]['grades'] = $grades;   
        }

        //重新排列班级
        $classes_list = [];
        foreach ($data as $v) {
            foreach ($v['grades'] as $vv) {
                foreach ($vv['classes'] as $val) {
                    $classes_list[] = $val;
                }  
            }
             
        }

        return $classes_list;   

    }

    //获取区域
    public function area_list()
    {

        $query     = new Query();
        $res =  $query ->from('province_city_area')->select('id,name,type')->all();

        $data           = [];
        $province       = [];
        $province_city  = [];
        $city_area      = [];

        //转换格式：id:name
        foreach ($res as $k => $v) {
            if($v['type'] == 1){
                $province['1'][$v['id']] = $v['name'];
                $city_res = $query ->from('province_city_area')->select('id,name,type')->where(['type'=>2,'pid'=>$v['id']])->all();
                foreach ($city_res as $kk => $vv) {
                   $province_city[$v['id']][$vv['id']] = $vv['name']; 
                }    
            }   

            if($v['type'] == 2){
                $city[$v['id']] = $v['name'];
                $area_res = $query ->from('province_city_area')->select('id,name,type')->where(['type'=>3,'pid'=>$v['id']])->all();
                foreach ($area_res as $kk => $vv) {
                   $city_area[$v['id']][$vv['id']] = $vv['name']; 
                }    
            }   

        } 
        
        //拼接
        foreach ($province as $k => $v) {
            $data[$k] = $v;
        }

        foreach ($province_city as $k => $v) {
            $data[$k] = $v;
        }

        foreach ($city_area as $k => $v) {
            $data[$k] = $v;
        }


        return ['status'=>1,'msg'=>'获取成功','res'=>$data];
    }


    //账号登录
    public function login($user_name,$password)
    {
        if(empty($user_name)) return ['status'=>0,'msg'=>'用户名不能为空'];
        if(empty($password))  return ['status'=>0,'msg'=>'密码不能为空'];

        $query = new Query();
        $user =  $query->from('data_center_user')->where(['user_name'=>$user_name])->one();

        if(empty($user)) {
            return ['status'=>0,'msg'=>'用户名不存在'];
        }else{
            $w = ['user_name'=>$user_name,'password'=>$password];
            $user_info =  $query->from('data_center_user')->where($w)->one();
            if(empty($user_info)) {
                return ['status'=>0,'msg'=>'密码有误'];
            }else{
                $session = \Yii::$app->session;
                $session->set('USER' , $user_info);
                return ['status'=>1,'msg'=>'登录成功','res'=>$user_info];
            }
        }
    }

    public function logout(){
        $session = \Yii::$app->session;
        $session->set('USER' , $user_info);

        return ['status'=>1,'msg'=>'退出成功'];
    }


    /*
     *$user['level'] 等级: 1为全国，2为省，3为市，4为区
     *   
     */

    //通过用户登录返回 对应区域列表
   public function user_zone($user_id){
        $query = new Query();
        $user  = $query->from('data_center_user')->where(['id'=>$user_id])->one();    
        if(empty($user))  return ['status'=>0,'msg'=>'用户不存在'];

        //获取属于用户查看的省份
        $query    = new Query();
        $user_zone = $query->from('data_user_zone')->select('province_id,city_id,area_id')->where(['uid'=>$user['id'],'status'=>1])->one();

        $data           = [];
        $province       = [];
        $province_city  = [];
        $city_area      = [];

        if($user['level'] < 3 ){
            //省
            if($user['level'] == 1){
                $res =  $query->from('province_city_area')->select('id,name,type')->where(['type'=>1])->all();    
            }elseif($user['level'] == 2){
                $res[] = $query->from('province_city_area')->select('id,name,type')->where(['id'=>$user_zone['province_id']])->one();
            }

            if(!empty($res)){
                //转换格式：id:name
                foreach ($res as $k => $v) {
                    if($v['type'] == 1){
                        $province['1'][$v['id']] = $v['name'];
                        $city_res = $query->from('province_city_area')->select('id,name,type')->where(['type'=>2,'pid'=>$v['id']])->all();
                        foreach ($city_res as $kk => $vv) {
                            $province_city[$v['id']][$vv['id']] = $vv['name'];
                            $area_res = $query->from('province_city_area')->select('id,name,type')->where(['type'=>3,'pid'=>$vv['id']])->all();
                            foreach ($area_res as $a => $b) {
                               $city_area[$vv['id']][$b['id']] = $b['name']; 
                            }    
                        }    
                    }   

                     
                } 
            }
        }elseif($user['level'] == 3){
            //市
            $res[] = $query->from('province_city_area')->select('id,name,type')->where(['id'=>$user_zone['province_id']])->one();
            if(!empty($res)){
                foreach ($res as $k => $v) {
                    $province['1'][$v['id']] = $v['name'];
                    $city_res[] = $query->from('province_city_area')->select('id,name,type')->where(['id'=>$user_zone['city_id']])->one();
                    foreach ($city_res as $kk => $vv) {
                        $province_city[$v['id']][$vv['id']] = $vv['name'];
                        $area_res = $query->from('province_city_area')->select('id,name,type')->where(['type'=>3,'pid'=>$vv['id']])->all();
                        foreach ($area_res as $a => $b) {
                           $city_area[$vv['id']][$b['id']] = $b['name']; 
                        }    
                    }  
                }
            }
            
        }else{
            //区
            $res[] = $query->from('province_city_area')->select('id,name,type')->where(['id'=>$user_zone['province_id']])->one();
            if(!empty($res)){
                foreach ($res as $k => $v) {

                    $province['1'][$v['id']] = $v['name'];
                    $city_res[] = $query->from('province_city_area')->select('id,name,type')->where(['id'=>$user_zone['city_id']])->one();
                    foreach ($city_res as $kk => $vv) {
                       $province_city[$v['id']][$vv['id']] = $vv['name']; 
                    }  

                    $area_res = $query->from('province_city_area')->select('id,name,type')->where(['id'=>$user_zone['area_id']])->all();
                    foreach ($area_res as $kk => $vv) {
                       $city_area[$v['id']][$vv['id']] = $vv['name']; 
                    }    
                      
                }
            }
        }

        //拼接
        foreach ($province as $k => $v)      {$data[$k] = $v;}
        foreach ($province_city as $k => $v) {$data[$k] = $v;}
        foreach ($city_area as $k => $v)     {$data[$k] = $v;}

        return ['status'=>1,'msg'=>'获取成功','res'=>$data,'user'=>$user];
    }


    // public function get_area($res){

    //     $data           = [];
    //     $province       = [];
    //     $province_city  = [];
    //     $city_area      = [];

    //     //转换格式：id:name
    //     foreach ($res as $k => $v) {
    //         if($v['type'] == 1){
    //             $province['1'][$v['id']] = $v['name'];
    //             $city_res = $query ->from('province_city_area')->select('id,name,type')->where(['type'=>2,'pid'=>$v['id']])->all();
    //             foreach ($city_res as $kk => $vv) {
    //                $province_city[$v['id']][$vv['id']] = $vv['name']; 
    //             }    
    //         }   

    //         if($v['type'] == 2){
    //             $city[$v['id']] = $v['name'];
    //             $area_res = $query ->from('province_city_area')->select('id,name,type')->where(['type'=>3,'pid'=>$v['id']])->all();
    //             foreach ($area_res as $kk => $vv) {
    //                $city_area[$v['id']][$vv['id']] = $vv['name']; 
    //             }    
    //         }   

    //     } 
        
    //     //拼接
    //     foreach ($province as $k => $v) {
    //         $data[$k] = $v;
    //     }

    //     foreach ($province_city as $k => $v) {
    //         $data[$k] = $v;
    //     }

    //     foreach ($city_area as $k => $v) {
    //         $data[$k] = $v;
    //     }

    //     return $data;    
    // }
    


}
