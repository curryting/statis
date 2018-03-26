<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\Adminuser;

/* @var $this yii\web\View */
/* @var $searchModel common\models\AdminuserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '管理员用户';
$this->params['breadcrumbs'][] = $this->title;
$this->params['createString'] = '<a href="'.Yii::$app->urlManager->createUrl(['adminuser/create']).'"><i class=" fa fa-fw fa-plus-circle"></i></a>';

?>
<div class="adminuser-index">
<?php /*
    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Adminuser', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
*/?>    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            //'id',
            ['attribute'=>'id',
                'contentOptions'=>['width'=>'20px'],
            ],
            'username',
            'realname',
            'email:email',
            //'status',
            [
                'attribute'=>'status',
                'value'=>'statusStr',
                'filter'=>Adminuser::allStatus(),
            ],
            // 'password_hash',
            // 'auth_key',
            // 'password_reset_token',
            // 'access_token',
            // 'expire_at',
            // 'logged_at',
            // 'created_at',
            [
                'attribute'=>'created_at',
                'format'=>['date','php:Y-m-d H:i:s'],
            ],
            // 'updated_at',

            //['class' => 'yii\grid\ActionColumn'],
            
            ['class' => 'yii\grid\ActionColumn',
                'template'=>'{view} {update} {resetpwd}',
                'buttons'=>[
                    'resetpwd'=>function($url,$model,$key)
                    {
                        $options=[
                            'title'=>Yii::t('yii','重置密码'),
                            'aria-label'=>Yii::t('yii','重置密码'),
                            'data-pjax'=>'0',
                        ];
                        return Html::a('<span class="glyphicon glyphicon-lock"></span>',$url,$options);
                },
                ],
           ],      
                
        ],
    ]); ?>
</div>
