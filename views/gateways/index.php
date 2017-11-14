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
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\GatewaySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Gateways';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="gateway-index">

  <p>
    <?= Html::a('Create Gateway', ['create'], ['class' => 'btn btn-success']) ?>
    <?= Html::a('Upload new gateways', ['upload'], ['class' => 'btn btn-link']) ?>
  </p>
  <?=
  GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
      'lrr_id',
      [
        'attribute' => 'type',
        'filter' => $searchModel::$typeOptions
      ],
      'coordinates:coordinates',
      'created_at:timeAgo',
      'updated_at:timeAgo',
      ['class' => 'yii\grid\ActionColumn'],
    ],
  ]);
  ?>
</div>
