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

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\ApiLog */

$this->title = Yii::$app->formatter->asDatetime($model->created_at);
$this->params['breadcrumbs'][] = ['label' => 'Api Log', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="api-log-view">

  <?=
  DetailView::widget([
    'model' => $model,
    'attributes' => [
      'created_at:timeAgo',
      'id',
      'origin',
      'device_eui',
      'query_string:ntext',
      'body:ntext',
      'comments:ntext',
      'created_at:dateTime',
      'updated_at:dateTime',
    ],
  ])
  ?>

</div>
