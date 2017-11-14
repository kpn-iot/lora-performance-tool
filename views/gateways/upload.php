<?php
/*  _  __  ____    _   _ 
 * | |/ / |  _ \  | \ | |
 * | ' /  | |_) | |  \| |
 * | . \  |  __/  | |\  |
 * |_|\_\ |_|     |_| \_|
 * 
 * (c) 2017 KPN
 * License: GNU General Public License v3.0
 * Author: Paul Marcelis
 * 
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\GatewayUpload */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Upload new gateways';
$this->params['breadcrumbs'][] = ['url' => ['index'], 'label' => 'Gateways'];
$this->params['breadcrumbs'][] = $this->title;
?>

<p>This upload script can be used when an overview of gateways and its locations has been shared with you by the network operator. Otherwise the gateway table will be populated on the fly from incoming LoRa messages, if reception metadata is enabled.</p>

<?php if ($uploadPending): ?>
  <div class="alert alert-info">
    There is still a gateway upload pending. Click <?= Html::a('here', ['upload-check'], ['class' => 'alert-link']) ?> to continue this upload. If you upload a new file, the current gateway upload will be discarded
  </div>
<?php endif ?>

<div class="well">

  <?php
  $form = ActiveForm::begin();
  ?>

  <?= $form->field($model, 'file')->fileInput() ?>

  <?= $form->field($model, 'type')->radioList($model::$typeOptions, ['separator' => '<br />']) ?>

  <div class="form-group">
    <?= Html::submitButton('Upload', ['class' => 'btn btn-primary']) ?>
  </div>

  <?php ActiveForm::end(); ?>

</div>
