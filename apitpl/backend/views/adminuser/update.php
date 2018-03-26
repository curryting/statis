<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Adminuser */

$this->title = '管理员用户修改: ' . $model->realname;
$this->params['breadcrumbs'][] = ['label' => '管理员用户', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '修改';
?>
<div class="adminuser-update">

<?php /*
    <h1><?= Html::encode($this->title) ?></h1>
*/?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
