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
use yii\bootstrap\ActiveForm;
use app\models\Session;
use yii\grid\GridView;
use app\components\data\Decoding;

/* @var $this yii\web\View */
/* @var $sessionSearchModel app\models\SessionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $frameCollection app\models\lora\FrameCollection */

$this->title = 'Report';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
$form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
  ]);
?>
<div class="row">

  <div class="col-xs-6 col-md-3">
    <?= $form->field($deviceSearchModel, 'payload_type')->checkboxList(Decoding::getSupportedPayloadTypes()) ?>
    <?= $form->field($sessionSearchModel, 'type')->checkboxList(Session::$typeOptions) ?>
  </div>

  <div class="col-xs-6 col-md-2">
    <?= $form->field($sessionSearchModel, 'vehicle_type')->checkboxList(Session::$vehicleTypeOptions) ?>
  </div>
  <div class="col-xs-6 col-md-2">
    <?= $form->field($sessionSearchModel, 'motion_indicator')->checkboxList(Session::$motionIndicatorOptions) ?>
  </div>
  <div class="col-xs-12 col-md-5">
    <label>Gateway count range</label>
    <div class="row">
      <div class="col-xs-6">
        <?= $form->field($frameSearchModel, 'gatewayCountMin')->label(false) ?>

      </div>
      <div class="col-xs-6">
        <?= $form->field($frameSearchModel, 'gatewayCountMax')->label(false) ?>

      </div>
    </div>

    <label>Frame received range</label>
    <div class="row">
      <div class="col-xs-6">
        <?= $form->field($frameSearchModel, 'createdAtMin')->input('datetime-local')->hint('dd-mm-yyyy hh:mm')->label(false) ?>
      </div>
      <div class="col-xs-6">
        <?= $form->field($frameSearchModel, 'createdAtMax')->input('datetime-local')->label(false) ?>
      </div>
      <div class="col-xs-12">
        <?= $form->field($frameSearchModel, 'sf')->checkboxList([7 => 'SF7', 8 => 'SF8', 9 => 'SF9', 10 => 'SF10', 11 => 'SF11', 12 => 'SF12']) ?>
      </div>
    </div>
  </div>
</div>
<div class="form-group">
  <?= Html::submitButton('Go!', ['class' => 'btn btn-primary']) ?>
  <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
</div>

<?php ActiveForm::end(); ?>

<hr />
<?php
$info = [
  ['Payload type', $deviceSearchModel->payload_type],
  ['Measurement type', $sessionSearchModel->type],
  ['Vehicle type', $sessionSearchModel->vehicle_type],
  ['Motion indicator', $sessionSearchModel->motion_indicator],
  ['Gateway count', function() use ($frameSearchModel) {
      if ($frameSearchModel->gatewayCountMin == null && $frameSearchModel->gatewayCountMax == null) {
        return null;
      }
      $ret = '';
      if ($frameSearchModel->gatewayCountMin != null) {
        $ret .= $frameSearchModel->gatewayCountMin;
      }
      $ret .= ' - ';
      if ($frameSearchModel->gatewayCountMax != null) {
        $ret .= $frameSearchModel->gatewayCountMax;
      }

      return $ret;
    }
  ],
  ['Time period', function() use ($frameSearchModel) {
      if ($frameSearchModel->createdAtMin == null && $frameSearchModel->createdAtMax == null) {
        return null;
      }
      $ret = '';
      if ($frameSearchModel->createdAtMin != null) {
        $ret .= $frameSearchModel->createdAtMin;
      }
      $ret .= ' - ';
      if ($frameSearchModel->createdAtMax != null) {
        $ret .= $frameSearchModel->createdAtMax;
      }

      return $ret;
    }
  ],
  ['Nr frames', Yii::$app->formatter->asInteger($frameProvider->count()), true],
];
?>

<table class="table table-condensed table-striped">
  <?php foreach ($info as $row): ?>
    <?php
    if (isset($row[2])) {
      $echo = $row[2];
    } else {
      if (is_callable($row[1])) {
        $echo = ($row[1]() !== null);
      } else {
        $echo = ($row[1] != null);
      }
    }
    ?>

    <?php if ($echo): ?>
      <tr>
        <th><?= $row[0] ?></th>
        <td>
          <?php
          if (is_callable($row[1])) {
            echo $row[1]();
          } elseif (is_array($row[1])) {
            echo implode(', ', $row[1]);
          } else {
            echo $row[1];
          }
          ?>
        </td>
      </tr>
    <?php endif ?>
  <?php endforeach ?>
</table>

<?php if ($frameProvider->count() > 50000): ?>
  <p>The report cannot be generated for more than 50.000 frames</p>
<?php endif ?>

<?php if ($frameCollection !== null): ?>
  <?= $this->render('/_partials/geoloc-pdf-cdf-graphs', ['stats' => $frameCollection->geoloc]) ?>
<?php endif ?>


</div>
<div class="container-fluid">

  <?=
  GridView::widget([
    'dataProvider' => $dataProvider,
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
        'attribute' => 'frr',
        'headerOptions' => [
          'class' => 'text-right'
        ],
        'contentOptions' => [
          'class' => 'text-right'
        ]
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
          return Yii::$app->formatter->asDatetime($data->lastFrame->created_at) .
            Html::tag('br') .
            Html::tag('i', Yii::$app->formatter->asTimeago($data->lastFrame->created_at));
        },
        'headerOptions' => [
          'style' => 'width:160px'
        ]
      ]
    ]
  ]);
  ?>
</div>
<div class="container">