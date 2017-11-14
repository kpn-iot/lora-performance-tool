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
/* @var $model app\models\Quick */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Check new gateways upload';
$this->params['breadcrumbs'][] = ['url' => ['index'], 'label' => 'Gateways'];
$this->params['breadcrumbs'][] = ['url' => ['upload'], 'label' => 'Upload new gateways'];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php if (!$itIsDone): ?>
  <p class="lead">Below you can see the actions that will be performed when updating the gateways. <b>Scroll down</b> to perform the update.</p>

  <p>
    Now <?= $nrActiveGateways ?> active gateways. <?= $actions['update'] ?> will be updated. <?= $actions['delete'] ?> will be deleted. There are <?= $actions['create'] + $actions['revive'] ?> new gateways, so after the upload you will have <?= $nrActiveGateways - $actions['delete'] + $actions['create'] + $actions['revive'] ?> gateways.
  </p>
<?php else: ?>
  <p class="lead">Update has been executed. Results can be seen below</p>
<?php endif ?>

<table class="table">
  <thead>
    <tr>
      <th>Nr</th>
      <th>Gateway ID</th>
      <th>Action</th>
      <th>Current location</th>
      <th>New location</th>
      <th class="text-right">Distance difference</th>
      <th>Current type</th>
      <th>New type</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($operations as $index => $op): ?>
      <tr class="<?= (($op['action'] === 'create' || $op['action'] === 'revive') ? 'success' : (($op['action'] === 'delete') ? 'danger' : (($op['action'] === 'update') ? 'info' : ''))) ?>">
        <td>#<?= $index + 1 ?></td>
        <th><?= $op['lrrId'] ?></th>
        <td><?= $op['action'] ?></td>
        <td><?= ($op['current'] === null) ? "<i>n.a.</i>" : $op['current']->coordinates ?></td>
        <td><?= ($op['new'] === null) ? "<i>n.a.</i>" : Yii::$app->formatter->asCoordinates($op['new']['latitude'] . ', ' . $op['new']['longitude']) ?></td>
        <td class="text-right"><?= (isset($op['distance']) && $op['distance'] > 0) ? $op['distance'] : '' ?></td>
        <td><?= ($op['current'] === null) ? "<i>n.a.</i>" : $op['current']->type ?></td>
        <td><?= ($op['new'] === null) ? "<i>n.a.</i>" : $op['new']['type'] ?></td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>
<?php if (!$itIsDone): ?>
  <hr/>
  <?php ActiveForm::begin() ?>

  <?= Html::submitButton('Update gateways', ['class' => 'btn btn-large btn-primary']) ?>

  <?php ActiveForm::end() ?>
<?php endif ?>
<br /><br /><br /><br /><br /><br />
