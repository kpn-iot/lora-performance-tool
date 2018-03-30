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

namespace app\helpers;

class Calc {

  public static function coordinateDistance($latA, $lonA, $latB, $lonB) {
    $vars = ['latA', 'lonA', 'latB', 'lonB'];
    foreach ($vars as $var) {
      $$var = (float) $$var;
    }
    return static::vincentyGreatCircleDistance($latA, $lonA, $latB, $lonB);
  }

  public static function coordinateBearing($latA, $lonA, $latB, $lonB) {
    $vars = ['latA', 'lonA', 'latB', 'lonB'];
    foreach ($vars as $var) {
      $$var = (float) $$var;
    }
    return static::bearing($latA, $lonA, $latB, $lonB);
  }

  private static function bearing($lat1_d, $lon1_d, $lat2_d, $lon2_d) {

    $lat1 = deg2rad($lat1_d);
    $lon1 = deg2rad($lon1_d);
    $lat2 = deg2rad($lat2_d);
    $lon2 = deg2rad($lon2_d);

    $L = $lon2 - $lon1;

    $cosD = sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($L);
    $D = acos($cosD);
    $cosC = (sin($lat2) - $cosD * sin($lat1)) / (sin($D) * cos($lat1));

    $C = 180.0 * acos($cosC) / pi();

    if (sin($L) < 0.0) {
      $C = 360.0 - $C;
    }
	
	if (is_nan($C)) {
		return null;
	}

    return $C;
  }

  /**
   * Calculates the great-circle distance between two points, with
   * the Vincenty formula.
   * @param float $latitudeFrom Latitude of start point in [deg decimal]
   * @param float $longitudeFrom Longitude of start point in [deg decimal]
   * @param float $latitudeTo Latitude of target point in [deg decimal]
   * @param float $longitudeTo Longitude of target point in [deg decimal]
   * @param float $earthRadius Mean earth radius in [m]
   * @return float Distance between points in [m] (same as earthRadius)
   */
  public static function vincentyGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
    // convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    $lonDelta = $lonTo - $lonFrom;
    $a = pow(cos($latTo) * sin($lonDelta), 2) +
      pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
    $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

    $angle = atan2(sqrt($a), $b);
    return $angle * $earthRadius;
  }

}
