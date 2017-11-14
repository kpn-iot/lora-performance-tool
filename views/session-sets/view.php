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
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\SessionSet */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Session Sets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="session-set-view">
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
    <?= Html::a(Html::icon('equalizer') . ' Geoloc report', ['report-geoloc', 'id' => $model->id], ['class' => 'btn btn-link hidden-print']) ?>

  </p>

  <?=
  DetailView::widget([
    'model' => $model,
    'attributes' => [
      'name',
      'description:ntext',
      'created_at:dateTime',
      'updated_at:dateTime',
    ],
  ])
  ?>

</div>
</div>
<div class="container-fluid">
  <h3>Sessions</h3>
  <?=
  GridView::widget([
    'dataProvider' => new yii\data\ActiveDataProvider(['query' => $model->getSessions()->with(['frames', 'lastFrame', 'firstFrame', 'frames.session', 'device'])]),
    'columns' => [
      'id',
      [
        'label' => 'Device',
        'attribute' => 'device_id',
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
        'label' => 'Last activity',
        'attribute' => 'lastActivity',
        'value' => 'lastFrame.created_at',
        'format' => 'timeAgo'
      ],
      [
        'label' => 'First frame',
        'attribute' => 'firstFrame.created_at',
        'format' => 'dateTime'
      ],
      [
        'label' => 'Last frame',
        'attribute' => 'lastFrame.created_at',
        'format' => 'dateTime'
      ],
      [
        'class' => 'yii\grid\ActionColumn',
        'template' => '{preview} {coverage} {geoloc} {map}',
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
          'coverage' => function ($url, $model) {
            return Html::a(Html::icon('stats'), ['report-coverage', 'id' => $model->id]);
          },
          'geoloc' => function ($url, $model) {
            return Html::a(Html::icon('equalizer'), ['report-geoloc', 'id' => $model->id]);
          }
        ]
      ]
    ]
  ]);
  ?>
</div>
<script>
  $(function () {
    $('[data-toggle="popover"]').popover()
  });
</script>
<div class="container">
