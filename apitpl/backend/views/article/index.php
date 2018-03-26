<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\Article;
use common\models\Adminuser;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ArticleSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '文章管理';
$this->params['breadcrumbs'][] = $this->title;
$this->params['createString'] = '<a href="'.Yii::$app->urlManager->createUrl(['article/create']).'"><i class=" fa fa-fw fa-plus-circle"></i></a>';
?>
<div class="article-index">

<?php /*
    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Article', ['create'], ['class' => 'btn btn-success']) ?>
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
            'title',
            //'content:ntext',
            //'category_id',
            //'status',
            [
                'attribute'=>'category_id',
                'value'=>'cateStr',
                'filter'=>Article::allCategory(),
            ],
            [
                'attribute'=>'status',
                'value'=>'statusStr',
                'filter'=>Article::allStatus(),
            ],
            // 'created_by',
            ['attribute'=>'created_by',
                'value'=>'createdBy.realname',
                'filter'=>Adminuser::find()
                ->select(['realname','id'])
                ->indexBy('id')
                ->column(),
            ],
            
            // 'created_at',
            [
                'attribute'=>'created_at',
                'format'=>['date','php:Y-m-d H:i:s'],
            ],
            // 'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
