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
     
    // 获取地区数据
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
            $new_city_area[$k]['school_avg'] = (intval($v['school_counts']) > 0) ? (round(count($v['schools'])/intval($v['school_counts']),4)*100).'%' : '0%';

            $tea_counts = 0;        //教职工总人数
            $tea_online_counts = 0; //教职工导入人数
        
            $stu_counts = 0;        //学生总人数
            $stu_online_counts = 0; //学生导入人数

            $teas_focus = 0; //全区老师关注总数
            $stus_focus = 0; //全区学生关注总数
            foreach ($v['schools'] as $kk => $vv) {

                //老师相关
                $tea_counts += intval($vv['teacher_count']);    
                $teas = $query->from('contacts_member')->where(['qid'=>$vv['id'],'type'=>1,'status'=>1])->count('id'); 
                $tea_online_counts += $teas;
                $teas_focus_one = $query->from('contacts_member')->where(['qid'=>$vv['id'],'type'=>1,'fstatus'=>[1,2,3],'status'=>1])->count('id');  
                $teas_focus += $teas_focus_one;

                //学生相关
                $stu_counts += intval($vv['student_count']);    //所有学生
                $stus = $query->from('contacts_member')->where(['qid'=>$vv['id'],'type'=>2,'status'=>1])->count('id');
                $stu_online_counts += $stus;
                $stus_focus_one = $query->from('contacts_member')->where(['qid'=>$vv['id'],'type'=>2,'fstatus'=>[1,2,3],'status'=>1])->count('id');  
                $stus_focus += $stus_focus_one;
            }

            $new_city_area[$k]['tea_counts']        = $tea_counts;
            $new_city_area[$k]['tea_online_counts'] = $tea_online_counts;   
            $new_city_area[$k]['tea_online_focus']  = intval($teas_focus);
            $tea_check_count = $this->check_count($tea_counts,$tea_online_counts);
            $new_city_area[$k]['teas_focus_avg']    = ($tea_check_count > 0) ? (round($teas_focus/$tea_check_count,4)*100).'%' : '0%';

            $new_city_area[$k]['stu_counts']        = $stu_counts;
            $new_city_area[$k]['stu_online_counts'] = $stu_online_counts;   
            $new_city_area[$k]['stu_online_focus']  = intval($stus_focus);
            $stu_check_count = $this->check_count($stu_counts,$stu_online_counts);
            $new_city_area[$k]['stus_focus_avg']    = ($stu_check_count > 0) ? (round($stus_focus/$stu_check_count,4)*100).'%' : '0%';

            //全校总数
            $new_city_area[$k]['member_counts'] = $tea_counts + $stu_counts;
            //总关注率
            $member_focus = intval($teas_focus) + intval($stus_focus);
            $member_online_counts = $tea_online_counts + $stu_online_counts;
            $mem_check_count = $this->check_count($member_focus,$member_online_counts);
            $new_city_area[$k]['member_focus_avg'] = ($mem_check_count > 0) ? (round($member_focus/$mem_check_count,4)*100).'%' : '0%';
            //导入总数
            $new_city_area[$k]['member_online_counts'] = $member_online_counts; 
            //关注总数
            $new_city_area[$k]['member_focus'] = $member_focus;

        }

        return $new_city_area = array_values($new_city_area);
    } 

    // 获取学校数据
    public function account_list($province_id,$city_id,$area_id)
    {

        $w = '';
        if(!empty($province_id))    $w['province_id']  = $province_id;
        if(!empty($city_id))        $w['city_id']      = $city_id;
        if(!empty($area_id))        $w['area_id']      = $area_id;


        $query   = new Query();
        $account =  $query 
            ->from('account')
            ->select('id,province_id,city_id,area_id,title,teacher_count,student_count,school_type')
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
            $teas = $query->from('contacts_member')->where(['qid'=>$vv['id'],'type'=>1,'status'=>1])->count(); 
            $tea_online_counts = $teas;
            $teas_focus = $query->from('contacts_member')->where(['qid'=>$vv['id'],'type'=>1,'fstatus'=>[1,2,3],'status'=>1])->count();  
            
            //学生相关
            $stu_counts = intval($vv['student_count']);    //所有学生
            $stus = $query->from('contacts_member')->where(['qid'=>$vv['id'],'type'=>2,'status'=>1])->count();
            $stu_online_counts = $stus;
            $stus_focus = $query->from('contacts_member')->where(['qid'=>$vv['id'],'type'=>2,'fstatus'=>[1,2,3],'status'=>1])->count();  


            $data[$kk]['tea_counts']        = $tea_counts;
            $data[$kk]['tea_online_counts'] = $tea_online_counts;   
            $data[$kk]['tea_online_focus']  = intval($teas_focus);
            $tea_check_count = $this->check_count($tea_counts,$tea_online_counts);
            $data[$kk]['teas_focus_avg']    = ($tea_check_count > 0) ? (round($teas_focus/$tea_check_count,4)*100).'%' : '0%';

            $data[$kk]['stu_counts']        = $stu_counts;
            $data[$kk]['stu_online_counts'] = $stu_online_counts;   
            $data[$kk]['stu_online_focus']  = intval($stus_focus);
            $stu_check_count = $this->check_count($stu_counts,$stu_online_counts); 
            $data[$kk]['stus_focus_avg']    = ($stu_check_count > 0) ? (round($stus_focus/$stu_check_count,4)*100).'%' : '0%';

            //全校总数
            $data[$kk]['member_counts'] = $tea_counts + $stu_counts;
            $member_focus = intval($teas_focus) + intval($stus_focus);
            $member_online_counts = $tea_online_counts + $stu_online_counts;
            $mem_check_count = $this->check_count($tea_counts+$stu_counts,$member_online_counts); 
            $data[$kk]['member_focus_avg'] = ($mem_check_count > 0) ? (round($member_focus/$mem_check_count,4)*100).'%' : '0%';
            //导入总数
            $data[$kk]['member_online_counts'] = $member_online_counts; 
            //关注总数
            $data[$kk]['member_focus'] = $member_focus;
        }

        return $data;
    }

    // 获取班级数据
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
            ->select('id,province_id,city_id,area_id,title,teacher_count,student_count,school_type')
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
                $grades[$kk]['extend_info'] = intval($vv['extend'] & 4095);
                $classes = $query->from('contacts_group')->select('id,pid,title,type,extend')->where(['pid'=>$vv['id'],'type'=>6,'status'=>1])->all();

                foreach ($classes as $key => $val) {
                    $classes[$key]['grade'] = intval($vv['extend'] & 4095);
                    $classes[$key]['area_name'] = $v['area_name'];
                    $classes[$key]['area_id']   = $v['area_id'];
                    $classes[$key]['type_info'] = $v['type_info'];
                    $classes[$key]['school_title'] = $v['title'];

                    //筛选属于这个班级的成员
                    $members = $query->from('contacts_group_link')->select('memid')->where(['qid'=>$v['id'],'groupid'=>$val['id'],'mtype'=>1])->all();
                    $stu_online_counts = count($members);
                    // $memids_arr = [];
                    // foreach ($members as $value) {
                    //     $memids_arr[] = $value['memid'];
                    // }

            
                    $classes[$key]['stu_counts'] = 0; //班级实际人数暂时为0;
                    $classes[$key]['stu_online_counts'] = intval($stu_online_counts);


                    $sql = 'select count(m.`id`) as count from contacts_member as m, contacts_group_link as g where m.`id`=g.`memid` and g.`qid`='.$v['id'].' and g.`groupid`='.$val['id'].' and g.`mtype`=1 and m.`status`=1 and m.`fstatus`>0 and m.`type`=2';
                    $stu_online_focus = Yii::$app->db->createCommand($sql)->queryAll()[0]['count'];

                    $classes[$key]['stu_online_focus'] = intval($stu_online_focus);

                    // $stu_online_focus = $query->from('contacts_member')->where(['status'=>1,'fstatus'=>[1,2],'id'=>$memids_arr])->count(); 
                    $stu_check_count = $this->check_count(0,intval($stu_online_counts));
                    $classes[$key]['stus_focus_avg'] = ($stu_check_count > 0) ? (round($stu_online_focus/$stu_check_count,4)*100).'%' : '0%';
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

                $user_zone = $this->user_zone($user_info['id'],1);
                return ['status'=>1,'msg'=>'登录成功','res'=>$user_info,'user_zone'=>$user_zone];
            }
        }
    }

    /*退出登录*/
    public function logout(){
        $session = \Yii::$app->session;
        $res = $session->remove('USER');

        return ['status'=>1,'msg'=>'退出登录成功'];
    }

    /*
     *$user['level'] 等级: 1为全国，2为省，3为市，4为区
     *$static：空为前端调用接口，1为后台调用   
     */

    //通过用户登录返回 对应区域列表
    public function user_zone($user_id,$static=''){
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
                        $area_res = $query->from('province_city_area')->select('id,name,type')->where(['id'=>$user_zone['area_id']])->all();
                       foreach ($area_res as $a => $b) {
                           $city_area[$vv['id']][$b['id']] = $b['name']; 
                        }     
                    }             
                }
            }
        }

        //拼接
        foreach ($province as $k => $v)      {$data[$k] = $v;}
        foreach ($province_city as $k => $v) {$data[$k] = $v;}
        foreach ($city_area as $k => $v)     {$data[$k] = $v;}

        if($static == 1){
            return $data;
        }else{
            return ['status'=>1,'msg'=>'获取成功','res'=>$data,'user'=>$user];
        }
       
    }

    /*
        实际人数已设置 关注率=关注人数/实际人数*100%
        实际人数未设置 关注率=关注人数/导入人数*100%
     */
    public function check_count($count1,$count2){
        if($count1 == $count2){
            return $count1;
        }else{
            if($count1 > $count2){
                return $count1;
            }else{
                return $count2;
            }
        } 
    }

    /*修改密码
    *user_id：用户ID
    *user_name：用户名
    *prepwd：原密码
    *pwd：新密码
    *repwd：确认新密码
   */
    public function modify_pwd($user_id,$user_name,$prepwd,$pwd,$repwd)
    {
        if(empty($user_id))     return ['status'=>0,'msg'=>'隐藏用户ID不能为空'];
        if(empty($user_name))   return ['status'=>0,'msg'=>'隐藏用户名不能为空'];
        if(empty($prepwd)) 		return ['status'=>0,'msg'=>'原密码不能为空'];
        if(empty($pwd))         return ['status'=>0,'msg'=>'新密码不能为空'];
        if(empty($repwd)) 		return ['status'=>0,'msg'=>'确认新密码不能为空'];

        if($pwd !== $repwd)     return ['status'=>0,'msg'=>'新密码和确认新密码不一致'];
        $query = new Query();

        $user = $query->from('data_center_user')->where(['id'=>$user_id,'user_name'=>$user_name,'status'=>1])->one();
        if($user['password'] !== $prepwd) 	return ['status'=>0,'msg'=>'原密码输出错误'];
        
        if(empty($user)) return ['status'=>0,'msg'=>'用户不存在'];

        $res = Yii::$app->db->createCommand()->update('data_center_user',['password'=> $pwd], 'id='.$user_id)->execute(); 

        if($res !== false) {
            return ['status'=>1,'msg'=>'密码修改成功'];
        }else{
            return ['status'=>0,'msg'=>'密码修改失败'];
        }
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
