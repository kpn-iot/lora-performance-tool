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
use yii\widgets\DetailView;
use yii\grid\GridView;
use app\helpers\Calc;

/* @var $this yii\web\View */
/* @var $model app\models\Gateway */

$this->title = $model->lrr_id;
$this->params['breadcrumbs'][] = ['label' => 'Gateways', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="gateway-view">

  <p>
    <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    <?=
    Html::a('Delete', ['delete', 'id' => $model->id], [
      'class' => 'btn btn-danger',
      'data' => [
        'confirm' => 'Are you sure you want to delete this item?',
        'method' => 'post',
      ],
    ])
    ?>
  </p>

  <?=
  DetailView::widget([
    'model' => $model,
    'attributes' => [
      'id',
      'lrr_id',
      'coordinates:coordinates',
      'type',
      'created_at:dateTime',
      'updated_at:dateTime',
      'updated_at:timeAgo',
    ],
  ])
  ?>

</div>

<?=
GridView::widget([
  'dataProvider' => new yii\data\ActiveDataProvider(['query' => $model->getReception()]),
  'columns' => [
    'frame_id',
    [
      'label' => 'Distance',
      'value' => function($data) use ($model) {
        if ($data->frame->latitude === null || $model->latitude == 0) {
          return null;
        } else {
          $distance = Calc::coordinateDistance($data->frame->latitude, $data->frame->longitude, $model->latitude, $model->longitude);
        }
        return Yii::$app->formatter->asDecimal($distance, 0) . 'm';
      }
    ],
    'rssi',
    'snr',
    'esp',
    'created_at:dateTime'
  ]
]);
?>
