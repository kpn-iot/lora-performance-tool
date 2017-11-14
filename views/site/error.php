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

/* @var $exception \yii\web\HttpException|\Exception */
/* @var $handler \yii\web\ErrorHandler */
if ($exception instanceof \yii\web\HttpException) {
  $code = $exception->statusCode;
} else {
  $code = $exception->getCode();
}
$name = $handler->getExceptionName($exception);
if ($name === null) {
  $name = 'Error';
}
if ($code) {
  $name .= " (#$code)";
}

if ($exception instanceof \yii\base\UserException) {
  $message = $exception->getMessage();
} else {
  $message = 'An internal server error occurred.';
}

$this->title = "An error occured";
?>
<div class="site-error">
  <?php if ($exception->getMessage() != null): ?>
    <div class="alert alert-danger">
      <?= nl2br($handler->htmlEncode($message)) ?>
    </div>
  <?php endif ?>

  <?php if (YII_DEBUG): ?>
    <h3><?= $handler->htmlEncode($name) ?></h3>
    <p>In <?= $exception->getFile() ?>:<?= $exception->getLine() ?></p>
  <?php endif ?>

  <p>
    The above error occurred while the Web server was processing your request.
  </p>
  <p>
    Please contact us if you think this is a server error. Thank you.
  </p>
</div>
