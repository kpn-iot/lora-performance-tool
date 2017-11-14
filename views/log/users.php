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
/* @var $searchModel app\models\UserLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'User Log';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-log-index">
  <?php Pjax::begin(); ?>
  <?=
  GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
      'ip_address',
      'username',
      'action',
      'result',
      'created_at:timeAgo'
    ],
  ]);
  ?>
  <?php Pjax::end(); ?></div>
