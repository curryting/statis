<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;


/* @var $this yii\web\View */
/* @var $model common\models\Adminuser */

$this->title = '新增管理员用户';
$this->params['breadcrumbs'][] = ['label' => '管理员用户', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="adminuser-create">

		<div class="adminuser-form">
		
		    <?php $form = ActiveForm::begin(); ?>
		 
		    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
		
		    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
		    
		    <?= $form->field($model, 'password_repeat')->passwordInput(['maxlength' => true]) ?>
		
		    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

		    <?= $form->field($model, 'realname')->textInput(['maxlength' => true]) ?>
		    
		    <div class="form-group">
		        <?= Html::submitButton('新增', ['class' =>'btn btn-success']) ?>
		    </div>
		   
		    <?php ActiveForm::end(); ?>
		
		</div>
    

</div>


