<?php

use app\helpers\Html;
use yii\grid\GridView;
use app\models\SessionSearch;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $model app\models\DeviceGroup */
/** @var $sessionsProvider \yii\data\ActiveDataProvider */
/** @var $formModel \app\models\forms\DeviceGroupGraphForm */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Device Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Tables';

?>

<?= $this->render('_view_header', [
    'model' => $model,
    'activeTab' => 'table'
]) ?>
<?= $this->render('_view_filter', [
    'formModel' => $formModel
]) ?>
<br/>
</div>
<div class="container-fluid">
  <?=
  GridView::widget([
      'id' => 'grid',
      'dataProvider' => $sessionsProvider,
      'columns' => [
          'id',
          [
              'label' => 'Device',
              'attribute' => 'device_id',
              'value' => function ($data) {
                return $data->device->name;
              }
          ],
          [
              'label' => 'Session date',
              'format' => 'datetime',
              'value' => function ($data) {
                return $data->prop->session_date_at;
              }
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
              'label' => 'Nr frames',
              'value' => function ($data) {
                return $data->prop->nr_frames;
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
              'template' => '{coverage} {geoloc} {update} {delete}',
              'controller' => 'sessions',
              'options' => [
                  'width' => '80px'
              ],
              'buttons' => [
                  'coverage' => function ($url, $model) {
                    return Html::a(Html::icon('stats'), ['/sessions/report-coverage', 'id' => $model->id]);
                  },
                  'geoloc' => function ($url, $model) {
                    return Html::a(Html::icon('equalizer'), ['/sessions/report-geoloc', 'id' => $model->id]);
                  },
              ]
          ]
      ]
  ]) ?>
</div>
<div class="container">
