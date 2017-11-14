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

namespace app\models;

class User extends \yii\base\BaseObject implements \yii\web\IdentityInterface {

  public $id;
  public $username;
  public $password;
  public $authKey;
  public $accessToken;
  private static $_users = null;

  /**
   * @inheritdoc
   */
  public static function findIdentity($id) {
    $users = static::_getUsers();
    return isset($users[$id]) ? new static($users[$id]) : null;
  }

  /**
   * @inheritdoc
   */
  public static function findIdentityByAccessToken($token, $type = null) {
    $users = static::_getUsers();
    foreach ($users as $user) {
      if ($user['accessToken'] === $token) {
        return new static($user);
      }
    }

    return null;
  }

  /**
   * Finds user by username
   *
   * @param  string $username
   * @return static|null
   */
  public static function findByUsername($username) {
    $users = static::_getUsers();
    foreach ($users as $user) {
      if (strcasecmp($user['username'], $username) === 0) {
        return new static($user);
      }
    }

    return null;
  }

  /**
   * @inheritdoc
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @inheritdoc
   */
  public function getAuthKey() {
    return $this->authKey;
  }

  /**
   * @inheritdoc
   */
  public function validateAuthKey($authKey) {
    return $this->authKey === $authKey;
  }

  /**
   * Validates password
   *
   * @param  string $password password to validate
   * @return boolean if password provided is valid for current user
   */
  public function validatePassword($password) {
    return $this->password === $password;
  }

  private static function _getUsers() {
    if (static::$_users === null) {
      $fileName = dirname(__DIR__) . '/config/users.php';
      if (!is_readable($fileName)) {
        throw new \yii\web\HttpException(400, "User config file cannot be read");
      }
      static::$_users = require($fileName);
    }
    return static::$_users;
  }

}
