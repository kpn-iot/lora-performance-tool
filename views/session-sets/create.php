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

/* @var $this yii\web\View */
/* @var $model app\models\SessionSet */

$this->title = 'Create Session Set';
$this->params['breadcrumbs'][] = ['label' => 'Session Sets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="session-set-create">
  <?php if ($nrNewSessions): ?>
  <div class="alert alert-info">When creating this session set, <?= $nrNewSessions ?> sessions will be added.</div>
  <?php endif ?>

  <?=
  $this->render('_form', [
    'model' => $model,
  ])
  ?>

</div>
