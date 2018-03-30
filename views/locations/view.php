<?php

use app\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Location */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Locations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="location-view">

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
      'name',
      'description:ntext',
      'coordinates:coordinates',
      'created_at:datetime',
      'updated_at:datetime',
    ],
  ])
  ?>

  <h3>Sessions</h3>
  <?=
  GridView::widget([
    'dataProvider' => new yii\data\ActiveDataProvider(['query' => $model->getSessions()->with(['properties', 'device'])]),
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
        'attribute' => 'frr',
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
        'value' => 'prop.last_frame_at',
        'format' => 'timeAgo'
      ],
      [
        'label' => 'First frame',
        'attribute' => 'prop.first_frame_at',
        'format' => 'dateTime'
      ],
      [
        'label' => 'Last frame',
        'attribute' => 'prop.last_frame_at',
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
            return Html::a(Html::icon('stats'), ['/sessions/report-coverage', 'id' => $model->id]);
          },
          'geoloc' => function ($url, $model) {
            return Html::a(Html::icon('equalizer'), ['/sessions/report-geoloc', 'id' => $model->id]);
          }
        ]
      ]
    ]
  ]);
  ?>

</div>
