<?php

/*  _  __  ____    _   _ 
 * | |/ / |  _ \  | \ | |
 * | ' /  | |_) | |  \| |
 * | . \  |  __/  | |\  |
 * |_|\_\ |_|     |_| \_|
 * 
 * (c) 2018 KPN
 * License: GNU General Public License v3.0
 * Author: Paul Marcelis
 * 
 */

namespace app\components;


use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;

class WordExport {
  private $_document, $_section;

  public function __construct($name) {
    // General
    $document = new PhpWord();
    $document->setDefaultFontName('Arial');
    $document->setDefaultFontSize(10);
    $document->setDefaultParagraphStyle(['spaceAfter' => 0]);
    $green = "#009900";
    $document->addTitleStyle(null, ['size' => 28, 'color' => $green]);
    $document->addTitleStyle(1, ['size' => 12, 'color' => $green, 'bold' => true], ['spaceBefore' => Converter::pointToTwip(10)]);
    $document->addTitleStyle(2, ['size' => 10, 'color' => $green], ['spaceBefore' => Converter::pointToTwip(6)]);
    $properties = $document->getDocInfo();
    $properties->setCreator('LoRa Network Performance Tool');

    $this->_name = $name;
    $this->_document = $document;
    $this->_section = $this->_document->addSection();
  }

  public function _escape($string) {
    return htmlspecialchars(strip_tags($string));
  }

  public function getSection() {
    return $this->_section;
  }

  public function addTitle($title, $depth) {
    return $this->_section->addTitle($this->_escape($title), $depth);
  }

  public function addText($text, $fStyle = null, $pStyle = null) {
    return $this->_section->addText($this->_escape($text), $fStyle, $pStyle);
  }

  public function addTable($info) {
    $table = $this->_section->addTable(['cellMargin' => Converter::pixelToTwip(6)]);
    $i = 0;
    foreach ($info as $row) {
      $table->addRow();
      foreach ($row as $cell) {
        $bgColor = ($i % 2 == 1) ? '#f2f2f2' : '#ffffff';
        $cellStyle = ['bgColor' => $bgColor, 'valign' => 'center'];
        $table->addCell(null, $cellStyle)->addText($this->_escape($cell));
      }
      $i++;
    }
  }

  public function addInfoTable($info) {
    $table = $this->_section->addTable(['cellMargin' => Converter::pixelToTwip(6)]);
    $i = 0;
    foreach ($info as $key => $value) {
      $table->addRow();
      $bgColor = ($i++ % 2 == 1) ? '#f2f2f2' : '#ffffff';
      $cellStyle = ['bgColor' => $bgColor, 'valign' => 'center'];
      $table->addCell(Converter::cmToTwip(4.41), $cellStyle)->addText($this->_escape($key), ['bold' => true]);
      $table->addCell(Converter::cmToTwip(11.83), $cellStyle)->addText($this->_escape($value));
    }
  }

  public function addColumnChart($values) {
    list($xAxis, $yAxis) = $this->_getChartAxis($values);

    $chart = $this->_section->addChart('column', $xAxis, $yAxis, ['showAxisLabels' => true, 'showGridY' => true]);
    $chartStyle = $chart->getStyle();
    $chartStyle->setWidth(Converter::cmToEmu(10));
    $chartStyle->setHeight(Converter::cmToEmu(7));
    $chartStyle->setDataLabelOptions(['showCatName' => false]);
  }

  public function addLineChart($values) {
    list($xAxis, $yAxis) = $this->_getChartAxis($values);

    $chart = $this->_section->addChart('line', $xAxis, $yAxis, ['showAxisLabels' => true, 'showGridY' => true]);
    $chartStyle = $chart->getStyle();
    $chartStyle->setWidth(Converter::cmToEmu(16));
    $chartStyle->setHeight(Converter::cmToEmu(7));
    $chartStyle->setDataLabelOptions(['showVal' => false, 'showCatName' => false]);
  }

  private function _getChartAxis($values) {
    $xAxis = [];
    $yAxis = [];
    foreach ($values as $key => $value) {
      $xAxis[] = $key;
      $yAxis[] = $value;
    }
    return [$xAxis, $yAxis];
  }

  public function end() {
    $fileName = $this->_name . ".docx";

    header("Content-Description: File Transfer");
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');

    $docWriter = IOFactory::createWriter($this->_document, 'Word2007');
    $docWriter->save("php://output");
    \Yii::$app->end();
  }

}