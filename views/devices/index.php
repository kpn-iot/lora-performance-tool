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
use yii\grid\GridView;
use app\components\data\Decoding;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DeviceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Devices for live measurements';
$this->params['breadcrumbs'][] = $this->title;
?>
<p>
  <?= Html::a('Create Device', ['create'], ['class' => 'btn btn-success']) ?>
</p>
</div>
<div class="container-fluid">
  <div class="table-responsive">
    <?=
    GridView::widget([
      'dataProvider' => $dataProvider,
      'filterModel' => $searchModel,
      'columns' => [
        [
          'attribute' => 'name',
          'format' => 'raw',
          'value' => function ($data) {
            return Html::a($data->name, ['view', 'id' => $data->id]);
          }
        ],
        'description:ntext',
        'device_eui',
        [
          'attribute' => 'payload_type',
          'filter' => Decoding::getSupportedPayloadTypes(),
          'value' => 'payloadTypeReadable',
          'format' => 'raw'
        ],
        [
          'format' => 'raw',
          'attribute' => 'totalSessions',
          'filter' => false,
          'value' => function ($data) {
            return Html::a($data->totalSessions, ['/sessions', 'SessionSearch[device_id]' => $data->id]);
          }
        ],
        [
          'label' => 'Last activity',
          'attribute' => 'lastFrame.created_at',
          'format' => 'timeAgo'
        ],
        [
          'class' => 'yii\grid\ActionColumn',
          'template' => '{live} {view} {update} {delete}',
          'buttons' => [
            'live' => function ($url, $model) {
              return Html::a(Html::icon('dashboard'), ['/data/dashboard', 'device_id' => $model->id]);
            }
          ],
          'options' => [
            'style' => 'width:80px'
          ]
        ]
      ],
    ]);
    ?>
  </div>
</div>
<div class="container">
