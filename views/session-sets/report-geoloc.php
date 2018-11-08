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

use yii\bootstrap\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\SessionSet */

$this->title = $model->name . ' <small>Geoloc report</small>';
$this->params['breadcrumbs'][] = ['label' => 'Session Sets', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Coverage report';

$sessionCollection = $model->sessionCollection;
?>
<p>
  <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary hidden-print']) ?>
  <?=
  Html::a('Delete', ['delete', 'id' => $model->id], [
      'class' => 'btn btn-danger hidden-print',
      'data' => [
          'confirm' => 'Are you sure you want to delete this item?',
          'method' => 'post',
      ],
  ])
  ?>
  <?= Html::a(Html::icon('stats') . ' Coverage report', ['report-coverage', 'id' => $model->id], ['class' => 'btn btn-link hidden-print']) ?>
  <?= Html::a(Html::icon('export') . ' Export', ['export', 'id' => $model->id], ['class' => 'btn btn-link hidden-print']) ?>

</p>

<?=
DetailView::widget([
    'model' => $model,
    'attributes' => [
        'name',
        'description:ntext',
        'sessionCollection.frameCollection.nrDevices',
        [
            'label' => 'Accuracy median',
            'value' => function ($model) {
              return Yii::$app->formatter->asDistance($model->sessionCollection->frameCollection->geoloc->median);
            }
        ],
        [
            'label' => 'Average accuracy',
            'value' => function ($model) {
              return Yii::$app->formatter->asDistance($model->sessionCollection->frameCollection->geoloc->average);
            }
        ],
        [
            'label' => 'LocSolve success rate',
            'value' => function ($model) {
              return Yii::$app->formatter->asDecimal($model->sessionCollection->frameCollection->geoloc->percentageNrLocalisations * 100, 1) . '%';
            }
        ],
        'sessionCollection.frameCollection.nrFrames'
    ],
])
?>

<hr/>
<?= $this->render('/_partials/geoloc-pdf-cdf-graphs', ['stats' => $sessionCollection->frameCollection->geoloc, 'makePNG' => $sessionCollection->isLarge]) ?>
<hr/>
<div class="row">
    <div class="col-sm-9">
      <?= $this->render('/_partials/geoloc-first-frames', ['avgDistances' => $sessionCollection->firstFrameLocSolveAccuracy]) ?>
    </div>
    <div class="col-sm-3">
      <?= $this->render('/_partials/geoloc-per-gateway-count-table', ['geolocStats' => $sessionCollection->frameCollection->geoloc]) ?>
    </div>
</div>

<?php if (!$sessionCollection->isLarge): ?>
<hr/>
</div>
<div class="container-fluid">
  <?= $this->render('/_partials/geoloc-table', ['frameCollection' => $sessionCollection->frameCollection]) ?>
</div>
<div class="container">
  <?php else: ?>
      <i>For large session sets (more than <?= $sessionCollection->frameCollection::$largeThreshold ?> frames) the table cannot be shown</i>
  <?php endif ?>

