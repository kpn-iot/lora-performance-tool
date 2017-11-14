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

use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ApiLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Api log';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="api-log-index">

  <?php Pjax::begin(); ?>    <?=
  GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
      'created_at:timeAgo',
      'created_at:dateTime',
      'device_eui',
      'comments:ntext',
      [
        'class' => 'yii\grid\ActionColumn',
        'template' => '{view}'
      ],
    ],
  ]);
  ?>
  <?php Pjax::end(); ?></div>
