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
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Device */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Devices', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="device-view">
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
    <?= Html::a('Stats', ['stats', 'id' => $model->id], ['class' => 'btn btn-link']) ?>
  </p>

  <?=
  DetailView::widget([
    'model' => $model,
    'attributes' => [
      'id',
      'description:ntext',
      'device_eui',
      'port_id',
      'payloadTypeReadable:raw',
      'as_id',
      'lrc_as_key',
      'autosplitFormatted:raw',
      'created_at:dateTime',
      'updated_at:dateTime',
    ],
  ])
  ?>
  <script>
    $(function () {
      $('[data-toggle="popover"]').popover()
    });
  </script>
  <h3><?= Html::a('Latest sessions', ['/sessions/index', 'SessionSearch[device_id]' => $model->id]) ?></h3>
  <div class="row">
    <?php foreach ($model->getSessions()->limit(5)->all() as $session): ?>
      <div class="col-md-4 col-sm-6 col-xs-12">
        <?= $this->render('/_partials/session_block', ['session' => $session, 'hideDevice' => true]) ?>
      </div>
    <?php endforeach ?>
    <div class="col-md-4 col-sm-6 col-xs-12">
      <h4>
        <?= Html::a('View all sessions&hellip;', ['/sessions/index', 'SessionSearch[device_id]' => $model->id]) ?>
      </h4>
    </div>
  </div>
</div>
