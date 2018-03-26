<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Article;

/* @var $this yii\web\View */
/* @var $model common\models\Article */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="article-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'content')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'category_id')->dropDownList(Article::allCategory(),
    		['prompt'=>'请选择文章分类']);  
     ?>
         
    <?= $form->field($model, 'status')->dropDownList(Article::allStatus(),
    		['prompt'=>'请选择文章状态']);  
     ?>
 
 <?php /*
    <?= $form->field($model, 'category_id')->textInput() ?>
    
    <?= $form->field($model, 'status')->textInput() ?>
    
    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>
*/?>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '新增' : '修改', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
