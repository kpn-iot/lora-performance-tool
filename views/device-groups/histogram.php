<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\DeviceGroup */
/** @var $bins array */
/** @var $formModel \app\models\forms\DeviceGroupGraphForm */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Device Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$nrResults = 0;
?>

<?= $this->render('_view_header', [
    'model' => $model,
    'activeTab' => 'histogram',
]) ?>

<?= $this->render('_view_filter', [
    'formModel' => $formModel,
    'showBins' => true,
]) ?>

<?php if ($bins === null): ?>
  <?php if ($formModel->hasErrors()): ?>
        <div class="alert alert-danger">
            Due to errors in the form above no results could be shown. Please fix the errors and try again.
        </div>
  <?php else: ?>
        <div class="alert alert-warning">
            There are no frames in the selected date period for the Devices in this Device group. Please widen your date search and reload.
        </div>
  <?php endif ?>
<?php else: ?>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>Bin</th>
            <th>Nr frames</th>
            <th>Percentage</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($bins as $bin):
          $nrResults += $bin['nrFrames']; ?>
            <tr>
                <td><?= $bin['label'] ?></td>
                <td><?= $bin['nrFrames'] ?></td>
                <td><?= Yii::$app->formatter->asDecimal($bin['percentage'], 1) ?>%</td>
            </tr>
        <?php endforeach ?>
        <tr>
            <th>Total</th>
            <th><?= $nrResults ?></th>
            <th></th>
        </tr>
        </tbody>
    </table>

<?php endif ?>
