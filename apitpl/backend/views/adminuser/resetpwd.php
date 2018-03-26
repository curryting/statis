<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
//use yii\widgets\ActiveForm;
//use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Adminuser */

$this->title = '重置用户密码（'.$userRealName.'）';
$this->params['breadcrumbs'][] = ['label' => '管理员用户', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="adminuser-resetpwd">

		<div class="adminuser-form">
		
		    <?php $form = ActiveForm::begin(); ?>
		 		
		    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
		    
		    <?= $form->field($model, 'password_repeat')->passwordInput(['maxlength' => true]) ?>
		
		    <div class="form-group">
		        <?= Html::submitButton('重置', ['class' =>'btn btn-success']) ?>
		    </div>
		   
		    <?php ActiveForm::end(); ?>
		
		</div>
    

</div>
