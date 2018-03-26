<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Article */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => '文章管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="article-view">

    <p>
        <?= Html::a('修改', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('删除', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '确定要删除这条记录?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'title',
            'content:ntext',
            //'category_id',
            [
                'attribute'=>'category_id',
                'value'=>$model->cateStr,
            ],
            //'status',
            [
                'attribute'=>'status',
                'value'=>$model->statusStr,
            ],          
            //'created_by',
            [
                'attribute'=>'created_by',
                'value'=>$model->createdBy->realname,
            ],
            //'created_at',
            [
                'attribute'=>'created_at',
                'value'=>date("Y-m-d H:i:s",$model->created_at),
            ],
            //'updated_at',
            [
                'attribute'=>'updated_at',
                'value'=>date("Y-m-d H:i:s",$model->updated_at),
            ],
            
            
        ],
    ]) ?>

</div>
