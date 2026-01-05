<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */



// no direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use PhpOffice\PhpSpreadsheet\Shared\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use CB\Component\Contentbuilder\Administrator\Helper\ContentbuilderHelper;

@ob_end_clean();

require __DIR__ . '/../../../librairies/PhpSpreadsheet-5.3.0/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Joomla\CMS\Factory;

//Font::setAutoSizeMethod(Font::AUTOSIZE_METHOD_EXACT);

$database = Factory::getDbo();

$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()->setCreator("ContentBuilder")->setLastModifiedBy("ContentBuilder");

// Create "Sheet 1" tab as the first worksheet.
// https://phpspreadsheet.readthedocs.io/en/latest/topics/worksheets/adding-a-new-worksheet
$spreadsheet->removeSheetByIndex(0);

$worksheet1 = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, substr($this->data->title ?? 'default', 0, 31));
$spreadsheet->addSheet($worksheet1, 0);

// LETTER -> A4.
$worksheet1->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

// Freeze first line.
$worksheet1->freezePane('A2');

// First row in grey.
// Appliquer le style à la première ligne
$style = $worksheet1->getStyle('1:1');

// Fond gris
$style->getFill()
    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    ->getStartColor()
    ->setARGB('c0c0c0');

// Centrage horizontal et vertical
$style->getAlignment()
    ->setHorizontal(PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
    ->setVertical(PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

// 1 -- Labels.
$labels = $this->data->visible_labels;
$colreserved = 0;

// Case of show_id_column true -> First column reserved.
$col_id = 0;
$reserved_labels = [];
if ($this->data->show_id_column) {
    $col_id = ++$colreserved;
    array_push($reserved_labels, Text::_('COM_CONTENTBUILDER_ID'));
}

// Case of state true -> column reserved.
$col_state = 0;
if ($this->data->list_state) {
    $col_state = ++$colreserved;
    array_push($reserved_labels, Text::_('COM_CONTENTBUILDER_EDIT_STATE'));
}

// Case of publish true -> column reserved.
$col_publish = 0;
if ($this->data->list_publish) {
    $col_publish = ++$colreserved;
    array_push($reserved_labels, Text::_('COM_CONTENTBUILDER_PUBLISH'));
}

$labels = array_merge($reserved_labels, $labels);

$col = 1;
foreach ($labels as $label) {
    $cell = [$col++, 1];
    $worksheet1->setCellValue($cell, $label);
    $worksheet1->getStyle($cell)->getFont()->setBold(true);
}

// 2 -- Data.
$row = 2;
foreach ($this->data->items as $item) {
    $i = 1; // Colonne de départ
    
    // Si on veut mettre l'ID
    if ($col_id > 0) {
        $worksheet1->setCellValue([$i++, $row], $item->colRecord);
    }

    // Si on veut mettre la colonne d'état.
    if ($col_state > 0) {
        // Sécuriser la requête
        $recordId = $database->quote($item->colRecord);
        $sql = "SELECT title, color 
                FROM `#__contentbuilder_list_states` 
                WHERE id = (SELECT state_id 
                            FROM `#__contentbuilder_list_records` 
                            WHERE record_id = $recordId)";
        $database->setQuery($sql);
        $result = $database->loadRow();

        if ($result !== null) {
            if (empty($result[1]) || !preg_match('/^[0-9A-F]{6}$/i', $result[1])) {
                $result[1] = 'FFFFFF'; // Blanc par défaut
            }

            // Convertir $i en lettre de colonne
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $cell = $columnLetter . $row; // Ex. 'B2'
            
            // Retrait de la couleur dans l'export.
            /*
            if ($result[1] !== 'FFFFFF') { // !== pour cohérence avec chaînes
                $worksheet1->getStyle($cell)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $result[1]]
                    ]
                ]);
            }*/
            $worksheet1->setCellValue([$i++, $row], $result[0]);
        }
        else {
            $i++;
        }
    }

    // Si on veut mettre la colonne d'état.
    if ($col_publish > 0) {
        $i++;
    }
 
    // Les autres colonnes.
    foreach($this->data->visible_cols as $id) {
        $worksheet1->setCellValue([$i++, $row], $item->{"col$id"});          
    }

    $row++; // Passer à la ligne suivante pour chaque item
}

$spreadsheet->getDefaultStyle()->getAlignment()->setWrapText(true);
//$worksheet1->setTitle("export-" . date('Y-m-d_Hi') . ".xlsx");

// Name file.
// Récupérer le fuseau horaire du client (via POST, GET, ou autre)
$input = Factory::getApplication()->input;
$userTimezone = $input->get('user_timezone', null, 'string');

// Si aucun fuseau horaire client n'est fourni, utiliser celui de Joomla
if (!$userTimezone) {
    $config = Factory::getConfig();
    $userTimezone = $config->get('offset', 'UTC');
}

// Créer la date avec le fuseau horaire
$date = Factory::getDate('now', $userTimezone);

$query = $database->getQuery(true)
    ->select($database->quoteName('name'))
    ->from($database->quoteName('#__facileforms_forms'))
    ->where($database->quoteName('id') . ' = ' . (int) $this->data->reference_id);

$database->setQuery($query);
$name = $database->loadResult() ?: 'Formulaire_inconnu';


$filename = "CB_export_" . $name. '_' .$date->format('Y-m-d_Hi', true) . ".xlsx";


$spreadsheet->setActiveSheetIndex(0);

foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
    // Active l'auto-size pour toutes les colonnes qui contiennent des données
    foreach ($worksheet->getColumnIterator() as $column) {
        $worksheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
    }

    // Force le calcul des largeurs basées sur le contenu réel
    $worksheet->calculateColumnWidths();

    // Applique un plafond de 70 caractères de largeur
    foreach ($worksheet->getColumnIterator() as $column) {
        $dimension = $worksheet->getColumnDimension($column->getColumnIndex());

        if ($dimension->getWidth() > 70) {
            $dimension->setAutoSize(false);
            $dimension->setWidth(70);
        }
    }
}


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