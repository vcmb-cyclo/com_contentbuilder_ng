<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @license     GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;

@ob_end_clean();

require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder_helpers.php');
require_once(__DIR__.'/../../../classes/PHPExcel.php');

$objPHPExcel = new PHPExcel();

$objPHPExcel->getProperties()->setCreator("ContentBuilder")
    ->setLastModifiedBy("ContentBuilder");

if($this->data->show_id_column){
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', Text::_('COM_CONTENTBUILDER_ID'));


    $c='B';
    $i=1;
    foreach($this->data->visible_labels As $label){
        $cell = "$c"."$i";
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($cell, $label);
        $c++;
    }

    $ch='B';
    $i=2;
    foreach($this->data->items As $item){
        for($ch='B';$ch<=$c;$ch++){

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$i, $item->colRecord);
            foreach($item As $key => $value){
                if($key != 'colRecord' && in_array(str_replace('col','',$key), $this->data->visible_cols)){
                    $cell="$ch"."$i";
                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue($cell, $value);
                    $ch++;
                }

            }
            $i++;
        }
    }



}

else {
    $c='A';
    $i=1;

    foreach($this->data->visible_labels As $label){
        $cell = "$c"."$i";
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($cell, $label);
        $objPHPExcel->getActiveSheet()->getStyle($cell)->getFont()->setBold(true);

        $c++;
    }

    $ch='A';
    $i=2;
    foreach($this->data->items As $item){

        for($ch='A';$ch<=$c;$ch++){
            foreach($item As $key => $value){
                if($key != 'colRecord' && in_array(str_replace('col','',$key), $this->data->visible_cols)){
                    $cell="$ch"."$i";
                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue($cell, $value);
                    $ch++;
                }

            }
            $i++;
        }
    }

}
$objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);
$cell_length = 0;
for($col='A';$col<$ch;$col++){
    for($row=1;$row<$i;$row++){
        $cell="$col"."$row";
        $length = strlen($objPHPExcel->getActiveSheet()->getCell($cell)->getValue());
        if($length > $cell_length){
            $cell_length = $length;
        }
        $objPHPExcel->getActiveSheet()
            ->getStyle($cell)
            ->getNumberFormat()
            ->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT );
    }
    if($cell_length < 1){
        $width = 15;
    }
    else if($cell_length <= 50){
        $width = $cell_length + 5;
    }
    else {
        $width = $cell_length/3;
    }
    $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setWidth($width);
    $cell_length = 0;

}



$objPHPExcel->getActiveSheet()->setTitle("export-".date('Y-m-d_Hi').".xlsx");


$filename = "export-".date('Y-m-d_Hi').".xlsx";
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename=' . $filename);
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');

exit;