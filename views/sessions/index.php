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
/* @var $model \app\models\Device */
/* @var $devicesFilter array */
/* @var $sessionSets array */

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
        'label' => 'Location Stats',
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

<p class="lead">The large table below gives you an overview of all measurement sessions that are currently in the
    Performance Tool.</p>
<div class="row">
    <div class="col-md-8">
        <h4>Bulk actions</h4>
        <p>With the checkboxes in the first row of the table you can select multiple sessions and do a single operation
            on them.</p>

        <div class="alert alert-danger" id="response" style="display:none"><b>Error on performing bulk action:</b> <span></span></div>
        <div class="list-group">
            <div class="list-group-item">
                <div class="list-group-item-heading">Session set from selected sessions</div>
                <form onsubmit="save()">
                    <div class="form-group form-group-sm">
                      <?= Html::dropDownList('session-set', null, $sessionSets, ['class' => 'form-control', 'prompt' => '- New session set - ', 'id' => 'session-set']) ?>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-default btn-sm" onclick="save()">Add selected sessions to selected
                            session set
                        </button>
                    </div>
                </form>
            </div>
            <div class="list-group-item">
                <div class="list-group-item-heading">Quick session sets</div>
                <p>
                    You can generate a report of multiple sessions by editing the url manually to the following:
                    <code><?= str_replace('100', '[sessionid1].[sessionid2].(etc)', Url::to(['report-coverage', 'id' => 100], true)) ?></code>.
                    The session id can be found in the left column of the table below.
                </p>
            </div>
            <div class="list-group-item">
                <div class="list-group-item-heading">Ad hoc coverage report</div>
                <p>Get an ad hoc coverage report of a selection of sessions.</p>
                <p>
                    <button class="btn btn-default btn-sm" type="button" onclick="quickReport()">Quick coverage report
                    </button>
                </p>
            </div>
            <div class="list-group-item">
                <div class="list-group-item-heading">Delete multiple sessions at once</div>
                <button class="btn btn-danger btn-sm" type="button" onclick="bulkDelete()">Delete selected sessions</button>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <h4>Legenda</h4>
        <p>The list below explains all available actions.</p>
        <ul class="list-group">
            <li class="list-group-item"><?= Html::icon('modal-window') ?> is to get a map preview of the session.
            </li>
            <li class="list-group-item"><?= Html::icon('stats') ?> is to view the coverage report.</li>
            <li class="list-group-item"><?= Html::icon('equalizer') ?> is to view the location report.</li>
            <li class="list-group-item"><?= Html::icon('map-marker') ?> is to go to map view.</li>
            <li class="list-group-item"><?= Html::icon('pencil') ?> is to edit the session properties.</li>
            <li class="list-group-item"><?= Html::icon('resize-full') ?> is to split the session.</li>
            <li class="list-group-item"><?= Html::icon('trash') ?> is to remove the session.</li>
        </ul>
    </div>
</div>
<br/>
<script>
    $(function () {
        $('[data-toggle="popover"]').popover()
    });

    function getIds() {
        event.preventDefault();
        var ids = $('#grid').yiiGridView('getSelectedRows');
        $("#response span").text('');
        if (ids.length === 0) {
            $("#response span").text('No sessions selected');
            $("#response").show();
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

    function bulkDelete() {
        var ids = getIds();
        if (!confirm("Are you sure to delete " + ids.length + " session(s)?")) {
            return;
        }

        $.ajax('<?= Url::to(['/sessions/bulk-delete']) ?>', {
            type: 'POST',
            data: {
                ids: ids
            },
            complete: function() {
                window.location.reload();
            }
        });
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
            $("#response").hide();
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
