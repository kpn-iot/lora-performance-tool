<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\DeviceGroup */
/** @var $data array */
/** @var $formModel \app\models\forms\DeviceGroupGraphForm */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Device Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$nrResults = 0;
?>

<?= $this->render('_view_header', [
  'model' => $model,
  'activeTab' => 'daily-stats',
]) ?>

<?= $this->render('_view_filter', [
  'formModel' => $formModel,
]) ?>

<?php if ($data === null): ?>
  <?php if ($formModel->hasErrors()): ?>
        <div class="alert alert-danger">
            Due to errors in the form above no results could be shown. Please fix the errors and try again.
        </div>
  <?php else: ?>
        <div class="alert alert-warning">
            There are no frames in the selected date period for the Devices in this Device group. Please widen your date
            search and reload.
        </div>
  <?php endif ?>
<?php else: ?>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>Date</th>
            <th>Nr frames</th>
            <th>Drop rate</th>
            <th>Geoloc Accuracy median</th>
            <th>Geoloc Accuracy average</th>
            <th>Geoloc Success Rate</th>
            <th>Average gateway count</th>
            <th>Average SNR</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($data['daily'] as $bin): ?>
            <tr>
                <td><?= $bin['date'] ?></td>
                <td><?= $bin['nr_frames'] ?></td>
                <td><?= Yii::$app->formatter->asDecimal($bin['drop_rate'], 2) ?>%</td>
                <td><?= Yii::$app->formatter->asDecimal($bin['geoloc_acc_median_avg'], 2) ?></td>
                <td><?= Yii::$app->formatter->asDecimal($bin['geoloc_acc_avg'], 2) ?></td>
                <td><?= Yii::$app->formatter->asDecimal($bin['geoloc_success_rate'], 2) ?></td>
                <td><?= Yii::$app->formatter->asDecimal($bin['gw_count_avg'], 2) ?></td>
                <td><?= Yii::$app->formatter->asDecimal($bin['snr_avg'], 2) ?></td>
            </tr>
        <?php endforeach ?>
        <tr>
            <th>Aggregated</th>
            <th><?= $data['aggregated']['nr_frames'] ?></th>
            <th><?= Yii::$app->formatter->asDecimal($data['aggregated']['drop_rate'], 2) ?>%</th>
            <th><?= Yii::$app->formatter->asDecimal($data['aggregated']['geoloc_acc_median_avg'], 2) ?></th>
            <th><?= Yii::$app->formatter->asDecimal($data['aggregated']['geoloc_acc_avg'], 2) ?></th>
            <th><?= Yii::$app->formatter->asDecimal($data['aggregated']['geoloc_success_rate'], 2) ?></th>
            <th><?= Yii::$app->formatter->asDecimal($data['aggregated']['gw_count_avg'], 2) ?></th>
            <th><?= Yii::$app->formatter->asDecimal($data['aggregated']['snr_avg'], 2) ?></th>
        </tr>
        </tbody>
    </table>

<?php endif ?>
