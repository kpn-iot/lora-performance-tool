<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Location */

$this->title = 'Create Location';
$this->params['breadcrumbs'][] = ['label' => 'Locations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?=

$this->render('_form', [
  'model' => $model,
])
?>
