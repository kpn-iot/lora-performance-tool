<?php

/*  _  __  ____    _   _ 
 * | |/ / |  _ \  | \ | |
 * | ' /  | |_) | |  \| |
 * | . \  |  __/  | |\  |
 * |_|\_\ |_|     |_| \_|
 * 
 * (c) 2018 KPN
 * License: GNU General Public License v3.0
 * Author: Paul Marcelis
 * 
 */

use app\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $sessionMerge \app\models\SessionMergeForm */

$this->title = "Merge sessions";
$this->params['breadcrumbs'][] = ['label' => 'Devices', 'url' => ['/devices/index']];
$this->params['breadcrumbs'][] = ['label' => 'Sessions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<p class="lead">A simple tool to merge sessions of the same device.</p>
<div class="well">
  <?php $form = ActiveForm::begin() ?>

  <?= $form->field($sessionMerge, 'sessionIdList')->hint('Give a comma (,) separated list of session IDs of the sessions you want to merge. All sessions should be of the same device!') ?>

  <?= $form->field($sessionMerge, 'targetSessionId')->hint("The session ID of the session to put all sessions in (should be in the session ID list)") ?>

    <div class="form-group">
      <?= Html::submitButton('Merge', ['class' => 'btn btn-primary']) ?>
    </div>

  <?php ActiveForm::end() ?>

</div>
