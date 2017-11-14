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

use yii\widgets\Breadcrumbs;

$this->beginContent('@app/views/layouts/base.php');
?>
<main>
  <div class="container">
    <?php if ($this->title != false): ?>
      <div class="page-header">
        <h1><?= $this->title ?></h1>
      </div>
    <?php endif ?>
    <?=
    Breadcrumbs::widget([
      'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
      'options' => [
        'class' => 'breadcrumb hidden-print'
      ]
    ])
    ?>

    <?php
    foreach (Yii::$app->session->getAllFlashes() as $key => $messageList) {
      if (!is_array($messageList)) {
        $messageList = [$messageList];
      }
      foreach ($messageList as $message) {
        echo '<div class="alert alert-' . $key . '">' . $message . '</div>';
      }
    }
    ?>
    <?= $content ?>
  </div>
</main>
<?php $this->endContent() ?>
