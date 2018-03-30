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
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\SessionSet */

$this->title = 'Update Session Set ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Session Sets', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="well">

  <?=
  $this->render('_form', [
    'model' => $model,
  ])
  ?>

</div>
<?php if (count($sessions) > 0): ?>
  <hr />
  <?php $form = ActiveForm::begin(['action' => ['/session-sets/add-link', 'from' => 'set']]) ?>

  <?= Html::hiddenInput('set_id', $model->id) ?>
  <p>
    <label>Add session to session set:</label>
    <?=
    Select2::widget([
      'name' => 'session_id',
      'data' => $sessions,
      'options' => [
        'placeholder' => 'Select session...',
        'required' => true
      ]
    ])
    ?>
  </p>
  <?= Html::submitButton('Add to set', ['class' => 'btn btn-primary']) ?>
  <?php $form->end() ?>
<?php endif ?>

<h3>Sessions in set:</h3>
<?=
GridView::widget([
  'dataProvider' => new ActiveDataProvider(['query' => $model->getSessions()->with(['properties', 'device'])]),
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
      'attribute' => 'description',
      'format' => 'raw',
      'value' => function ($data) {
        return $data->name;
      }
    ],
    [
      'label' => 'Last activity',
      'attribute' => 'lastActivity',
      'value' => 'prop.last_frame_at',
      'format' => 'timeAgo'
    ],
    [
      'class' => 'yii\grid\ActionColumn',
      'template' => '{remove}',
      'buttons' => [
        'remove' => function ($url, $data, $key) use ($model) {
          return Html::a(Html::icon('trash'), ['delete-link', 'session_id' => $data->id, 'set_id' => $model->id, 'from' => 'set'], [
              'data' => [
                'confirm' => Yii::t('yii', 'Are you sure you want to delete this session from the set?'),
                'method' => 'post'
              ]
          ]);
        },
      ]
    ]
  ]
]);
?>
