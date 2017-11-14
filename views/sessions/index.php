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

use app\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use app\models\SessionSearch;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $searchModel app\models\SessionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = ['label' => 'Devices', 'url' => ['/devices']];
if ($model != null) {
  $this->title = 'Sessions of ' . $model->name;
  $this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['/devices/view', 'id' => $model->id]];
} else {
  $this->title = 'Sessions';
}
$this->params['breadcrumbs'][] = 'Sessions';

$columns = [
  'id',
  [
    'label' => 'Device',
    'attribute' => 'device_id',
    'filter' => Select2::widget(['name' => 'SessionSearch[device_id]', 'value' => $searchModel->device_id, 'data' => $devicesFilter, 'options' => ['placeholder' => '']]),
    'format' => 'raw',
    'value' => function ($data) {
      return $data->device->name;
    }
  ],
  [
    'attribute' => 'type',
    'filter' => ['moving' => 'Moving', 'static' => 'Static'],
    'value' => 'typeIcon',
    'format' => 'raw'
  ],
  [
    'attribute' => 'vehicle_type',
    'filter' => SessionSearch::$vehicleTypeOptions,
    'value' => 'vehicleTypeIcon',
    'format' => 'raw'
  ],
  [
    'attribute' => 'motion_indicator',
    'filter' => SessionSearch::$motionIndicatorOptions,
    'value' => 'motionIndicatorReadable'
  ],
  [
    'attribute' => 'description',
    'format' => 'raw',
    'value' => function ($data) {
      return $data->name;
    }
  ],
  [
    'attribute' => 'countUpRange',
    'format' => 'raw',
    'value' => function ($data) {
      return Html::a($data->countUpRange, ['/frames', 'FrameSearch[session_id]' => $data->id]);
    }
  ],
  [
    'attribute' => 'locSolveAccuracy',
    'headerOptions' => [
      'class' => 'text-right'
    ],
    'contentOptions' => [
      'class' => 'text-right'
    ]
  ],
  [
    'attribute' => 'locSolveSuccess',
    'headerOptions' => [
      'class' => 'text-right'
    ],
    'contentOptions' => [
      'class' => 'text-right'
    ]
  ],
  [
    'attribute' => 'frrRel',
    'headerOptions' => [
      'class' => 'text-right'
    ],
    'contentOptions' => [
      'class' => 'text-right'
    ]
  ],
  [
    'attribute' => 'runtime',
    'headerOptions' => [
      'class' => 'text-right'
    ],
    'contentOptions' => [
      'class' => 'text-right'
    ]
  ],
  [
    'label' => 'First frame',
    'attribute' => 'firstFrame.created_at',
    'format' => 'raw',
    'value' => function ($data) {
      if ($data->firstFrame === null) {
        return null;
      }
      return Yii::$app->formatter->asDatetime($data->firstFrame->created_at) .
        Html::tag('br') .
        Html::tag('i', Yii::$app->formatter->asTimeago($data->firstFrame->created_at));
    },
    'headerOptions' => [
      'style' => 'width:160px'
    ]
  ],
  [
    'label' => 'Last frame',
    'attribute' => 'lastActivity',
    'format' => 'raw',
    'value' => function ($data) {
      if ($data->lastFrame === null) {
        return null;
      }
      return Yii::$app->formatter->asDatetime($data->lastFrame->created_at) .
        Html::tag('br') .
        Html::tag('i', Yii::$app->formatter->asTimeago($data->lastFrame->created_at));
    },
    'headerOptions' => [
      'style' => 'width:160px'
    ]
  ],
  [
    'class' => 'yii\grid\ActionColumn',
    'template' => '{preview} {coverage} {geoloc} {map} {update} {split} {delete}',
    'controller' => 'sessions',
    'options' => [
      'width' => '80px'
    ],
    'buttons' => [
      'map' => function ($url, $model) {
        return Html::a(Html::icon('map-marker'), ['/map/index', 'session_id' => $model->id]);
      },
      'preview' => function ($url, $model) {
        return '<a data-toggle="popover" data-placement="left" data-container="body" data-html="true" role="button" 
              data-content="<iframe src=\'' . Url::to(['/map/popover', 'session_id' => $model->id]) . '\' style=\'border:none;margin: -9px -14px;width:272px;height:300px\'></iframe>">' . Html::icon('modal-window') . '</a>';
      },
      'split' => function ($url, $model) {
        return Html::a(Html::icon('resize-full'), ['split', 'id' => $model->id]);
      },
      'coverage' => function ($url, $model) {
        return Html::a(Html::icon('stats'), ['report-coverage', 'id' => $model->id]);
      },
      'geoloc' => function ($url, $model) {
        return Html::a(Html::icon('equalizer'), ['report-geoloc', 'id' => $model->id]);
      }
    ]
  ]
];
?>

<script>
  $(function () {
    $('[data-toggle="popover"]').popover()
  });
</script>
<p class="lead">
  Explanation of the icons in the action column: <?= Html::icon('modal-window') ?> is to get a map preview of the session. <?= Html::icon('map-marker') ?> is to go to map view. <?= Html::icon('eye-open') ?> is to view the report. <?= Html::icon('pencil') ?> is to edit the session properties. <?= Html::icon('resize-full') ?> is to split the session. <?= Html::icon('trash') ?> is to remove the session.
</p>
<p>
  You can generate a report of multiple sessions by editing the url manually to the following: <code><?= str_replace('100', '[sessionid1].[sessionid2].(etc)', Url::to(['report-coverage', 'id' => 100], true)) ?></code>. The session id can be found in the left column of the table below.
</p>
</div>
<div class="container-fluid">
  <div class="table-responsive">
    <?=
    GridView::widget([
      'dataProvider' => $dataProvider,
      'filterModel' => $searchModel,
      'columns' => $columns
    ]);
    ?>
  </div>
</div>
<div class="container">