<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

@ob_end_clean();

require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder_helpers.php');
//require_once __DIR__ .'/../../../classes/PhpSpreadsheet/Spreadsheet.php';
require __DIR__ . '/../../../librairies/PhpSpreadsheet/vendor/autoload.php';
// require __DIR__ . '/var/www/html/joomla/administrator/components/com_contentbuilder/librairies/PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;

/// use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


$spreadsheet = new Spreadsheet();

$spreadsheet->getProperties()->setCreator("ContentBuilder")
    ->setLastModifiedBy("ContentBuilder");

// Freeze first line.
$spreadsheet->getActiveSheet()->freezePane('A2');

// First row in grey.
$spreadsheet
    ->getActiveSheet()
    ->getStyle('1:1')
    ->getFill()
    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    ->getStartColor()
    ->setARGB('c0c0c0');



if ($this->data->show_id_column) {
    $spreadsheet->setActiveSheetIndex(0)
        ->setCellValue('A1', Text::_('COM_CONTENTBUILDER_ID'));


    $c = 'B';
    $i = 1;
    foreach ($this->data->visible_labels as $label) {
        $cell = "$c" . "$i";
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue($cell, $label);
        $c++;
    }

    $ch = 'B';
    $i = 2;
    foreach ($this->data->items as $item) {
        for ($ch = 'B'; $ch <= $c; $ch++) {

            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $item->colRecord);
            foreach ($item as $key => $value) {
                if ($key != 'colRecord' && in_array(str_replace('col', '', $key), $this->data->visible_cols)) {
                    $cell = "$ch" . "$i";
                    $spreadsheet->setActiveSheetIndex(0)
                        ->setCellValue($cell, $value);
                    $ch++;
                }

            }
            $i++;
        }
    }



} else {
    $c = 'A';
    $i = 1;

    foreach ($this->data->visible_labels as $label) {
        $cell = "$c" . "$i";
        $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $label);
        $spreadsheet->getActiveSheet()->getStyle($cell)->getFont()->setBold(true);

        $c++;
    }

    $ch = 'A';
    $i = 2;
    foreach ($this->data->items as $item) {

        for ($ch = 'A'; $ch <= $c; $ch++) {
            foreach ($item as $key => $value) {
                if ($key != 'colRecord' && in_array(str_replace('col', '', $key), $this->data->visible_cols)) {
                    $cell = "$ch" . "$i";
                    $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $value);
                    $ch++;
                }

            }
            $i++;
        }
    }
}

$spreadsheet->getDefaultStyle()->getAlignment()->setWrapText(true);
$cell_length = 0;
for ($col = 'A'; $col < $ch; $col++) {
    for ($row = 1; $row < $i; $row++) {
        $cell = "$col" . "$row";
        $length = strlen($spreadsheet->getActiveSheet()->getCell($cell)->getValue() ?? '');
        if ($length > $cell_length) {
            $cell_length = $length;
        }
        $spreadsheet->getActiveSheet()
            ->getStyle($cell)
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
    }
    if ($cell_length < 1) {
        $width = 15;
    } else if ($cell_length <= 50) {
        $width = $cell_length + 5;
    } else {
        $width = $cell_length / 3;
    }
    $spreadsheet->getActiveSheet()->getColumnDimension($col)->setWidth($width);
    $cell_length = 0;
}



$spreadsheet->getActiveSheet()->setTitle("export-" . date('Y-m-d_Hi') . ".xlsx");

// Name file.
$filename = "export-" . date('Y-m-d_Hi') . ".xlsx";
$spreadsheet->setActiveSheetIndex(0);

// Autosizing
foreach ($spreadsheet->getActiveSheet()->getColumnDimensions() as $columnDimension) {
    $columnDimension->setAutoSize(true);
}
$spreadsheet->getActiveSheet()->calculateColumnWidths();



// Redirect output to a client’s web browser (Excel5)
//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//header('Content-Disposition: attachment; filename=' . $filename);
//header('Cache-Control: max-age=0');
/*header('Pragma: public'); // HTTP/1.0
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");// HTTP/1.1
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");;
header('Content-Disposition: attachment; filename=' . $filename);
header("Content-Transfer-Encoding: binary ");*/



header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
;
header('Cache-Control: max-age=0');
header('Content-Disposition: attachment; filename=' . $filename);
header("Content-Transfer-Encoding: binary ");

ob_end_clean();
ob_start();



$objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$objWriter->save('php://output');

exit;