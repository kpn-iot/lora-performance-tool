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
use app\models\SessionSearch;
use kartik\select2\Select2;
use yii\grid\GridView;
use yii\helpers\Url;

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
    [
        'class' => 'yii\grid\CheckboxColumn'
    ],
    'id',
    [
        'label' => 'Device',
        'attribute' => 'device_id',
        'filter' => Select2::widget(['name' => 'SessionSearch[device_id]', 'value' => $searchModel->device_id, 'data' => $devicesFilter, 'options' => ['placeholder' => '']]),
        'format' => 'raw',
        'headerOptions' => [
            'style' => 'min-width:150px'
        ],
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
    'interval',
    [
        'attribute' => 'sf',
        'headerOptions' => [
            'style' => 'min-width:65px'
        ]
    ],
    [
        'label' => 'Avg GW count',
        'attribute' => 'avgGwCount',
        'headerOptions' => [
            'class' => 'text-right'
        ],
        'contentOptions' => [
            'class' => 'text-right'
        ]
    ],
    [
        'label' => 'LocSolve Stats',
        'attribute' => 'geolocStats',
        'format' => 'raw',
        'headerOptions' => [
            'class' => 'text-right',
            'style' => 'min-width: 140px'
        ],
        'contentOptions' => [
            'class' => 'text-right'
        ],
        'value' => function ($data) {
          if ($data->prop->geoloc_accuracy_average === null) {
            return null;
          }
          return "Median: <b>" . Yii::$app->formatter->asDistance($data->prop->geoloc_accuracy_median) . "</b><br />" .
              "Average: " . Yii::$app->formatter->asDistance($data->prop->geoloc_accuracy_average) . "<br />" .
              "90% under: " . Yii::$app->formatter->asDistance($data->prop->geoloc_accuracy_90perc) . "<br />" .
              "2D Avg.: <b>" . \app\models\Frame::formatBearingArrow($data->prop->geoloc_accuracy_2d_direction) . "</b> " . Yii::$app->formatter->asDistance($data->prop->geoloc_accuracy_2d_distance);
        }
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
        'label' => 'First frame',
        'attribute' => 'firstFrame',
        'format' => 'raw',
        'value' => function ($data) {
          if ($data->prop->first_frame_at === null) {
            return null;
          }
          return Yii::$app->formatter->asDatetime($data->prop->first_frame_at) .
              Html::tag('br') .
              Html::tag('i', Yii::$app->formatter->asTimeago($data->prop->first_frame_at));
        },
        'headerOptions' => [
            'style' => 'width:160px'
        ]
    ],
    [
        'label' => 'Last frame',
        'attribute' => 'lastFrame',
        'format' => 'raw',
        'value' => function ($data) {
          if ($data->prop->last_frame_at === null) {
            return null;
          }
          return Yii::$app->formatter->asDatetime($data->prop->last_frame_at) .
              Html::tag('br') .
              Html::tag('i', Yii::$app->formatter->asTimeago($data->prop->last_frame_at));
        },
        'headerOptions' => [
            'style' => 'width:160px'
        ]
    ],
    [
        'class' => 'yii\grid\ActionColumn',
        'template' => '{preview} {coverage} {geoloc} {map} {update} {export} {split} {delete}',
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
            },
            'export' => function ($url, $model) {
              return Html::a(Html::icon('export'), ['export', 'id' => $model->id]);
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
    Explanation of the icons in the action column: <?= Html::icon('modal-window') ?> is to get a map preview of the
    session. <?= Html::icon('stats') ?> is to view the coverage report. <?= Html::icon('equalizer') ?> is to view the
    geoloc report. <?= Html::icon('map-marker') ?> is to go to map view. <?= Html::icon('pencil') ?> is to edit the
    session properties. <?= Html::icon('resize-full') ?> is to split the session. <?= Html::icon('trash') ?> is to
    remove the session.
</p>
<p>
    You can generate a report of multiple sessions by editing the url manually to the following:
    <code><?= str_replace('100', '[sessionid1].[sessionid2].(etc)', Url::to(['report-coverage', 'id' => 100], true)) ?></code>.
    The session id can be found in the left column of the table below.
</p>
<form class="form-inline" onsubmit="save()">
    <div class="input-group">
      <?= Html::dropDownList('session-set', null, $sessionSets, ['class' => 'form-control', 'prompt' => '- New session set - ', 'id' => 'session-set']) ?>
        <div class="input-group-btn">
            <button class="btn btn-default" onclick="save()">Put selected sessions in session set</button>
        </div>
    </div>
    <div class="input-group">
        <button class="btn btn-link" onclick="quickReport()">Quick coverage report</button>
    </div>
    <span id="response"></span>
</form>
<br/>
<script>
    function getIds() {
        event.preventDefault();
        var ids = $('#grid').yiiGridView('getSelectedRows');
        $("#response").text('');
        if (ids.length === 0) {
            $("#response").text('No sessions selected');
            saveDone();
            return;
        }
        return ids;
    }

    function quickReport() {
        var ids = getIds();
        var url = "<?= Url::to(['/sessions/report-coverage', 'id' => 'HERE']) ?>";
        var sessionSetId = $("#session-set").val();
        url = url.replace("HERE", ids.join('.'));
        window.location = url;
    }

    function save() {
        var ids = getIds();
        var urlCreate = "<?= Url::to(['/session-sets/create', 'session_ids' => 'HERE']) ?>";
        var urlUpdate = "<?= Url::to(['/session-sets/add-sessions', 'id' => 'WHAT', 'session_ids' => 'HERE']) ?>";
        var sessionSetId = $("#session-set").val();
        var url;
        if (sessionSetId === '') {
            url = urlCreate.replace("HERE", ids.join('.'));
        } else {
            url = urlUpdate.replace('WHAT', sessionSetId).replace("HERE", ids.join('.'));
        }
        window.location = url;
    }

    function saveDone() {
        t = setTimeout(function () {
            $("#response").text("");
        }, 2500);
        return true;
    }
</script>
</div>
<div class="container-fluid">
    <div class="table-responsive">
      <?=
      GridView::widget([
          'id' => 'grid',
          'dataProvider' => $dataProvider,
          'filterModel' => $searchModel,
          'columns' => $columns
      ]);
      ?>
    </div>
</div>
<div class="container">