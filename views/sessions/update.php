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

/* @var $this yii\web\View */
/* @var $model app\models\Session */

$this->title = 'Update ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Devices', 'url' => ['/devices/index']];
$this->params['breadcrumbs'][] = ['label' => $model->device->name, 'url' => ['/devices/view', 'id' => $model->device_id]];
$this->params['breadcrumbs'][] = ['label' => 'Sessions', 'url' => ['index', 'SessionSearch[device_id]' => $model->device_id]];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('body{margin-top:200px}');
?>
<div style="position:absolute;width:100%;top:50px;left:0;height:278px;z-index:-1">
  <?= $this->render('/map/small', ['session_id' => $model->id]) ?>
</div>
<p>
  <?= Html::a(Html::icon('stats') . ' Coverage Report', ['/sessions/report-coverage', 'id' => $model->id], ['class' => 'btn btn-link hidden-print']) ?>
  <?= Html::a(Html::icon('equalizer') . ' Location report', ['/sessions/report-geoloc', 'id' => $model->id], ['class' => 'btn btn-link hidden-print']) ?>
  <?= Html::a(Html::icon('map-marker') . ' Map', ['/map/index', 'session_id' => $model->id], ['class' => 'btn btn-link hidden-print']) ?>
  <?= Html::a(Html::icon('list') . ' Frames', ['/frames/index', 'FrameSearch[session_id]' => $model->id], ['class' => 'btn btn-link hidden-print']); ?>
  <?= Html::a(Html::icon('resize-full') . ' Split', ['split', 'id' => $model->id], ['class' => 'btn btn-default hidden-print']) ?>
  <?=
  Html::a(Html::icon('trash') . ' Delete', ['delete', 'id' => $model->id], [
    'class' => 'btn btn-danger hidden-print',
    'data' => [
      'confirm' => 'Are you sure you want to delete this item?',
      'method' => 'post',
    ],
  ])
  ?>
</p>
<div class="well">
  <?=
  $this->render('_form', [
    'model' => $model,
  ])
  ?>

</div>
<?php if (count($sessionSets) > 0): ?>
  <hr />
  <?php $form = yii\bootstrap\ActiveForm::begin(['action' => ['/session-sets/add-link', 'from' => 'session']]) ?>

  <?= Html::hiddenInput('session_id', $model->id) ?>
  <p>
    <label>Add session to session set:</label>
    <?= Html::dropDownList('set_id', null, $sessionSets, ['prompt' => 'Select session set...', 'class' => 'form-control', 'required' => true]) ?>
  </p>
  <?= Html::submitButton('Add to set', ['class' => 'btn btn-primary']) ?>
  <?php $form->end() ?>
<?php endif ?>

<h3>In sets:</h3>
<?=
GridView::widget([
  'dataProvider' => new yii\data\ActiveDataProvider(['query' => $model->getSessionSets()]),
  'columns' => [
    [
      'attribute' => 'name',
      'format' => 'raw',
      'value' => function($data) {
        return Html::a($data->name, ['/session-sets/view', 'id' => $data->id]);
      }
    ],
    'description:ntext',
    'created_at:dateTime',
    'updated_at:dateTime',
    [
      'class' => 'yii\grid\ActionColumn',
      'template' => '{remove}',
      'buttons' => [
        'remove' => function ($url, $data, $key) use ($model) {
          return Html::a(Html::icon('trash'), ['/session-sets/delete-link', 'session_id' => $model->id, 'set_id' => $data->id, 'from' => 'session'], [
              'data' => [
                'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                'method' => 'post'
              ]
          ]);
        },
      ]
    ]
  ],
]);
?>
