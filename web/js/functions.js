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

Date.prototype.ago = function () {
  var now = new Date();

  var left = (now - this) / 1000;

  if (left < 0) {
    return '';
  } else if (left < 60) {
    return 'Minder dan 1 minuut geleden';
  }

  left /= 60; //minutes
  var check = Math.round(left);

  if (check === 1) {
    return '1 minuut geleden';
  } else if (check < 60) {
    return check + ' minuten geleden';
  }

  left /= 60;
  check = Math.round(left);

  if (check < 24) {
    return check + ' uur geleden';
  }

  left /= 24;
  check = Math.round(left);
  if (check === 1) {
    return check + ' dag geleden';
  }

  return check + ' dagen geleden';
};

function makeId(length) {
  var text = "";
  var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

  for (var i = 0; i < length; i++)
    text += possible.charAt(Math.floor(Math.random() * possible.length));

  return text;
}