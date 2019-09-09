<?php

namespace App\Helpers;

use Lang;

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpPresentation\Autoloader;
use PhpOffice\PhpPresentation\Settings;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\AbstractShape;
use PhpOffice\PhpPresentation\DocumentLayout;
use PhpOffice\PhpPresentation\Shape\Drawing;
use PhpOffice\PhpPresentation\Shape\RichText;
use PhpOffice\PhpPresentation\Shape\RichText\BreakElement;
use PhpOffice\PhpPresentation\Shape\RichText\TextElement;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Bullet;
use PhpOffice\PhpPresentation\Style\Border;
use PhpOffice\PhpPresentation\Style\Fill;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Slide;
use PhpOffice\PhpPresentation\Slide\Transition;
use PhpOffice\PhpPresentation\Slide\Background\Image;
use PhpOffice\PhpPresentation\Slide\Background\Color as StyleColor;
use PhpOffice\PhpPresentation\Shape\Drawing\Base64;
use PhpOffice\PhpPresentation\Shape\RichText\Paragraph;

class ExportPowerPoint
{
    function createShape($shape, $shape_type, $width, $height, $offsetX, $offsetY, $background_color = null, $border_color = null)
    {
        if ($shape_type == 'TextShape') {
            $shape->setHeight($height);
            $shape->setWidth($width);
            $shape->setOffsetX($offsetX);
            $shape->setOffsetY($offsetY);

            if ($border_color != null) {
                $shape->getBorder()->setColor(new Color($border_color))->setDashStyle(Border::DASH_SOLID)->setLineStyle(Border::LINE_THICKTHIN);
            }
            if ($background_color != null) {
                $shape->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->setRotation(90) // fill color rotation
                    ->setStartColor(new Color($background_color))
                    ->setEndColor(new Color($background_color));
            }
        }
        return $shape;
    }

    function createText($shape, $width, $height, $offsetX, $offsetY, $textContent, $textSize, $textColor, $isBold, $textFont, $rotation = 0, $align_horizontal = null, $align_vertical = null, $line_spacing = null)
    {
        $shape->setHeight($height);
        $shape->setWidth($width);
        $shape->setOffsetX($offsetX);
        $shape->setOffsetY($offsetY);
        $shape->setRotation($rotation);

        if ($align_horizontal == "HORIZONTAL_RIGHT")
            $shape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);

        if ($align_horizontal == "HORIZONTAL_CENTER")
            $shape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        if ($line_spacing != null)
            $shape->getActiveParagraph()->setLineSpacing($line_spacing);
        /*
        if ($textType == "TRUE")
            $textRun = $shape->createTextRun(trans('powerpoint_text.'.$textContent));
        else
            $textRun = $shape->createTextRun($textContent);
        */
        $textRun = $shape->createTextRun($textContent);
        $textRun->getFont()->setBold($isBold);
        $textRun->getFont()->setSize($textSize);
        $textRun->getFont()->setName($textFont);
        $textRun->getFont()->setColor(new Color($textColor));

        return $shape;
    }

    function createImageObject($resource, $path, $height, $width, $offsetX, $offsetY, $base64)
    {
        if ($base64 == "FALSE") {
            $chart = new Drawing\File();
            $chart->setName('Image')
                ->setDescription('Image')
                ->setPath($resource . $path)
                ->setHeight($height)
                ->setWidth($width)
                ->setOffsetX($offsetX)
                ->setOffsetY($offsetY);
            return $chart;
        } else if ($base64 == "TRUE") {
            $chart = new Drawing\Base64();
            $chart->setName('Chart')
                ->setDescription('Chart')
                ->setData(gzdecode($path), false)
                ->setResizeProportional(false)
                ->setHeight($height)
                ->setWidth($width)
                ->setOffsetX($offsetX)
                ->setOffsetY($offsetY);
            return $chart;
        }
    }

    //Intalize all the table (Load from DB)
    function createTable($shape, $slide_number, $table_name, $width, $height, $offsetX, $offsetY, $table_data, $ppt_currency, $ppt_exchange_currency)
    {
        if ($table_name == "CurrentCostStructure") {
            $shape->setHeight($height);
            $shape->setWidth($width);
            $shape->setOffsetX($offsetX);
            $shape->setOffsetY($offsetY);
            $shape = $this->createTable_CurrentCostStructure_slide9($shape, $table_data['CurrentCostStructure'], $ppt_currency, $ppt_exchange_currency);
        }

        if ($table_name == "StateOfTheCurrentInfrastructure") {
            $shape->setHeight($height);
            $shape->setWidth($width);
            $shape->setOffsetX($offsetX);
            $shape->setOffsetY($offsetY);
            $shape = $this->createTable_StateOfTheCurrentInfrastructure_slide9($shape, $table_data['StateOfTheCurrentInfrastructure'], $ppt_currency, $ppt_exchange_currency);
        }

        if ($table_name == "CurrentProcessors") {
            $shape->setHeight($height);
            $shape->setWidth($width);
            $shape->setOffsetX($offsetX);
            $shape->setOffsetY($offsetY);
            $shape = $this->createTable_CurrentProcessors_slide9($shape, $table_data['CurrentProcessors']);
        }


        if ($table_name == "SpreadOfVMTypes") {
            $shape->setHeight($height);
            $shape->setWidth($width);
            $shape->setOffsetX($offsetX);
            $shape->setOffsetY($offsetY);
            $shape = $this->createTable_SpreadOfVMTypes_slide10($shape, $table_data['SpreadOfVMTypes']);
        }

        if ($table_name == "StorageCostFactorsComparision") {
            $shape->setHeight($height);
            $shape->setWidth($width);
            $shape->setOffsetX($offsetX);
            $shape->setOffsetY($offsetY);
            $shape = $this->createTable_StorageCostFactorsComparision_slide19($shape, $table_data['StorageCostFactorsComparision'], $ppt_currency, $ppt_exchange_currency);
        }

        if ($table_name == "AdjustingTheStorageMix") {
            $shape->setHeight($height);
            $shape->setWidth($width);
            $shape->setOffsetX($offsetX);
            $shape->setOffsetY($offsetY);
            $shape = $this->createTable_AdjustingTheStorageMix_slide19($shape, $table_data['AdjustingTheStorageMix'], $ppt_currency);
        }

        if ($table_name == "CorrectedVMMix") {
            $shape->setHeight($height);
            $shape->setWidth($width);
            $shape->setOffsetX($offsetX);
            $shape->setOffsetY($offsetY);
            $shape = $this->createTable_CorrectedVMMix_slide19($shape, $table_data['CorrectedVMMix']);
        }

        if ($table_name == "AzureSiteRecovery") {
            $shape->setHeight($height);
            $shape->setWidth($width);
            $shape->setOffsetX($offsetX);
            $shape->setOffsetY($offsetY);
            $shape = $this->createTable_AzureSiteRecovery_slide19($shape, $table_data['AzureSiteRecovery']);
        }

        if ($table_name == "PricePerGBRAMForPrimaryStorage") {
            $shape->setHeight($height);
            $shape->setWidth($width);
            $shape->setOffsetX($offsetX);
            $shape->setOffsetY($offsetY);
            $shape = $this->createTable_PricePerGBRAMForPrimaryStorage_slide19($shape, $table_data['PricePerGBRAMForPrimaryStorage'], $ppt_currency, $ppt_exchange_currency);
        }

        if ($table_name == "BenefitsOnSwitchingOnOffVMs") {
            $shape->setHeight($height);
            $shape->setWidth($width);
            $shape->setOffsetX($offsetX);
            $shape->setOffsetY($offsetY);
            $shape = $this->createTable_BenefitsOnSwitchingOnOffVMs_slide21($shape, $table_data['BenefitsOnSwitchingOnOffVMs']);
        }

        if ($table_name == "BenefitsOnOptimization") {
            $shape->setHeight($height);
            $shape->setWidth($width);
            $shape->setOffsetX($offsetX);
            $shape->setOffsetY($offsetY);
            $shape = $this->createTable_BenefitsOnOptimization_slide23($shape, $table_data['BenefitsOnOptimization']);
        }

        if ($table_name == "OptimisingTheStorageUsageWhenMigratingToAzure") {
            $shape->setHeight($height);
            $shape->setWidth($width);
            $shape->setOffsetX($offsetX);
            $shape->setOffsetY($offsetY);
            $shape = $this->createTable_OptimisingTheStorageUsageWhenMigratingToAzure_slide23($shape, $table_data['OptimisingTheStorageUsageWhenMigratingToAzure']);
        }

        if ($table_name == "AllocationOfReservedInstances") {
            $shape->setHeight($height);
            $shape->setWidth($width);
            $shape->setOffsetX($offsetX);
            $shape->setOffsetY($offsetY);
            $shape = $this->createTable_AllocationOfReservedInstances_slide25($shape, $table_data['AllocationOfReservedInstances'], $ppt_currency, $ppt_exchange_currency);
        }

        if ($table_name == "AzureQualityOfServices") {
            $shape->setHeight($height);
            $shape->setWidth($width);
            $shape->setOffsetX($offsetX);
            $shape->setOffsetY($offsetY);
            $shape = $this->createTable_AzureQualityOfServices_slide28($shape, $table_data['AzureQualityOfServices'], $ppt_currency, $ppt_exchange_currency);
        }

        if ($table_name == "BusinessCase") {
            $shape->setHeight($height);
            $shape->setWidth($width);
            $shape->setOffsetX($offsetX);
            $shape->setOffsetY($offsetY);
            $shape = $this->createTable_BusinessCase_slide31($shape, $table_data['BusinessCase']);
        }


        if ($table_name == "ProjectOver") {
            $shape->setHeight($height);
            $shape->setWidth($width);
            $shape->setOffsetX($offsetX);
            $shape->setOffsetY($offsetY);
            $shape = $this->createTable_ProjectOver_slide34($shape, $table_data['ProjectOver']);
        }

        return $shape;
    }

    //Create Table CurrentCostStructure
    function createTable_CurrentCostStructure_slide9($tableShape, $table_data, $ppt_currency, $ppt_exchange_currency)
    {
        //Add row 1
        $row = $tableShape->createRow();
        $row->setHeight(35);
        $row->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FF0080FF'))
            ->setEndColor(new Color('FF0080FF'));

        $title_cell = $row->nextCell();
        $title_cell->setWidth(250);
        $title_cell->createTextRun('Current Cost Structure')
            ->getFont()
            ->setSize(12)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));
        $title_cell->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $data_cell = $row->nextCell();
        $data_cell->setWidth(101.36);
        $data_cell->createTextRun($ppt_currency)
            ->getFont()
            ->setSize(12)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));
        $data_cell->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        //table_data
        $count_row = 0;
        foreach ($table_data as $key => $value) {
            // \Log::info($count_row);
            $row = $tableShape->createRow();
            $row->setHeight(20);
            $row->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFFFFFFF'))
                ->setEndColor(new Color('FFFFFFFF'));

            switch ($key) {
                case "total_os_lisence_cost":
                    $key_format = "Total OS/HypVisor license cost";
                    break;
                case "total_of_gb_in_use":
                    $key_format = "Volume of GB Ram in use";
                    break;
                case "num_of_reported_vms":
                    $key_format = "Number of reported VMs";
                    break;
                case "num_of_cpus_in_use":
                    $key_format = "Number of CPU in use";
                    break;
                case "num_of_reported_vms":
                    $key_format = "Volume of GB RAM in use";
                    break;
                case "ratio_over_committed_cpu":
                    $key_format = "Ratio over-committed CPU";
                    break;
                case "number_of_gbram_per_vm":
                    $key_format = "Number Of GBRAM Per Vm";
                    break;
                case "ratio_cpu_gbram":
                    $key_format = "Ratio CPU - GBRAM";
                    break;

                default:
                    $key_format = ucwords(str_replace('_', ' ', $key));
            }

            $cell1 = $row->nextCell();
            $cell1->setWidth(250);
            $cell1->createTextRun($key_format)
                ->getFont()
                ->setSize(10)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell1->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setMarginLeft(12);

            $value_formated = 0;
            /*
            if ($value-round($value)!=0)
                $value_formated = number_format($value,3,'.',',');
            else
                $value_formated = number_format($value,0);
            */
            if ($count_row < 4)
                $value_formated = number_format($value * $ppt_exchange_currency, 0);
            else if ($count_row > 3 && $count_row < 7)
                $value_formated = number_format($value, 0);
            else
                $value_formated = number_format($value, 1);

            $cell2 = $row->nextCell();
            $cell2->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFD9D9D9'))
                ->setEndColor(new Color('FFD9D9D9'));
            $cell2->setWidth(101.36);
            $cell2->createTextRun($value_formated)
                ->getFont()
                ->setSize(10)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell2->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            //format
            if ($count_row == 0) {
                $cell1->getBorders()->getBottom()->setLineWidth(0);
                $cell2->getBorders()->getBottom()->setLineWidth(0);
            }

            if ($count_row == 1 || $count_row == 2 || $count_row == 5) {
                $cell1->getBorders()->getTop()->setLineWidth(0);
                $cell1->getBorders()->getBottom()->setLineWidth(0);

                $cell2->getBorders()->getTop()->setLineWidth(0);
                $cell2->getBorders()->getBottom()->setLineWidth(0);
            }

            $count_row++;

            //Total monthly current infra-cost
            if ($count_row == 4) {
                $row = $tableShape->createRow();
                $row->setHeight(20);
                $row->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setRotation(90)
                    ->setStartColor(new Color('FFFFFFFF'))
                    ->setEndColor(new Color('FFFFFFFF'));

                $cell1 = $row->nextCell();
                $cell1->setWidth(250);
                $cell1->createTextRun('Total monthly current infra-cost')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(11)
                    ->setName('Trebuchet MS')
                    ->setColor(new Color('FF000000'));

                $cell1->getActiveParagraph()
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setMarginLeft(2);

                $value_formated = $table_data['total_indirect_cost']
                    + $table_data['total_storage_cost']
                    + $table_data['total_compute_cost']
                    + $table_data['total_os_lisence_cost'];
                /*
                if ($value_formated-round($value_formated)!=0)
                    $value_formated = number_format($value_formated,3,'.',',');
                else
                    $value_formated = number_format($value_formated,0);
                */
                $value_formated = number_format($value_formated * $ppt_exchange_currency, 0);
                $cell2 = $row->nextCell();
                $cell2->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setRotation(90)
                    ->setStartColor(new Color('FFD9D9D9'))
                    ->setEndColor(new Color('FFD9D9D9'));
                $cell2->setWidth(101.36);
                $cell2->createTextRun($value_formated)
                    ->getFont()
                    ->setSize(11)
                    ->setBold(true)
                    ->setName('Trebuchet MS')
                    ->setColor(new Color('FF000000'));

                $cell2->getActiveParagraph()
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
            }


            if ($count_row == 10) {

                $row = $tableShape->createRow();
                $row->setHeight(20);
                $row->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setRotation(90)
                    ->setStartColor(new Color('FFFFFFFF'))
                    ->setEndColor(new Color('FFFFFFFF'));

                $cell1 = $row->nextCell();
                $cell1->setWidth(250);
                $cell1->createTextRun('Total')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(11)
                    ->setName('Trebuchet MS')
                    ->setColor(new Color('FF000000'));

                $cell1->getActiveParagraph()
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setMarginLeft(2);

                $value_formated = $table_data['ratio_cpu_gbram']
                    + $table_data['number_of_gbram_per_vm']
                    + $table_data['ratio_over_committed_cpu'];
                /*
                if ($value_formated-round($value_formated)!=0)
                    $value_formated = number_format($value_formated,3,'.',',');
                else
                    $value_formated = number_format($value_formated,0);
                */
                $value_formated = number_format($value_formated * $ppt_exchange_currency, 0);
                $cell2 = $row->nextCell();
                $cell2->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setRotation(90)
                    ->setStartColor(new Color('FFD9D9D9'))
                    ->setEndColor(new Color('FFD9D9D9'));
                $cell2->setWidth(101.36);
                $cell2->createTextRun($value_formated)
                    ->getFont()
                    ->setSize(11)
                    ->setBold(true)
                    ->setName('Trebuchet MS')
                    ->setColor(new Color('FF000000'));

                $cell2->getActiveParagraph()
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
            }
        }

        return $tableShape;
    }

    //Create Table StateOfTheCurrentInfrastructure
    function createTable_StateOfTheCurrentInfrastructure_slide9($tableShape, $table_data, $ppt_currency, $ppt_exchange_currency)
    {
        //Add row 1
        $row = $tableShape->createRow();
        $row->setHeight(20);
        $row->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FF0080FF'))
            ->setEndColor(new Color('FF0080FF'));

        $title_cell = $row->nextCell();
        $title_cell->setWidth(281.36);
        $title_cell->createTextRun('State of the current infrastructure	')
            ->getFont()
            ->setSize(12)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));
        $title_cell->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $data_cell = $row->nextCell();
        $data_cell->setWidth(70);

        $title_cell->setColSpan(2);

        //table_data
        $count_row = 0;
        //dd($table_data);
        foreach ($table_data as $key => $value) {
            $row = $tableShape->createRow();
            $row->setHeight(20);
            $row->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFFFFFFF'))
                ->setEndColor(new Color('FFFFFFFF'));

            $key_format = str_replace('_', ' ', $key);

            $cell1 = $row->nextCell();

            $cell1->createTextRun($key_format)
                ->getFont()
                ->setSize(10)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell1->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setMarginLeft(5);

            $cell2 = $row->nextCell();

            $cell2->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFD9D9D9'))
                ->setEndColor(new Color('FFD9D9D9'));

            $value_format = number_format($value, 0);

            if ($count_row == 0)
                $value_format = number_format($value, 0) . '%';

            if ($count_row == 2)
                $value_format = $ppt_currency . ' ' . number_format($value * $ppt_exchange_currency, 0);

            $cell2->createTextRun($value_format)
                ->getFont()
                ->setSize(10)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell2->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            //format
            if ($count_row == 0) {
                $cell1->getBorders()->getBottom()->setLineWidth(0);
                $cell2->getBorders()->getBottom()->setLineWidth(0);
            }

            if ($count_row == 1) {
                $cell1->getBorders()->getTop()->setLineWidth(0);
                $cell1->getBorders()->getBottom()->setLineWidth(0);

                $cell2->getBorders()->getTop()->setLineWidth(0);
                $cell2->getBorders()->getBottom()->setLineWidth(0);
            }

            if ($count_row == 2) {
                $cell1->getBorders()->getTop()->setLineWidth(0);
                $cell2->getBorders()->getTop()->setLineWidth(0);

                $cell1->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setRotation(90)
                    ->setStartColor(new Color('FFF2F2F2'))
                    ->setEndColor(new Color('FFF2F2F2'));
            }

            $count_row++;
        }
        return $tableShape;
    }

    //Create Table Current Processor
    function createTable_CurrentProcessors_slide9($tableShape, $table_data)
    {
        //Add row 1
        $row = $tableShape->createRow();
        $row->setHeight(20);
        $row->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FF0080FF'))
            ->setEndColor(new Color('FF0080FF'));

        $title_cell = $row->nextCell();
        $title_cell->setWidth(281.36);
        $title_cell->createTextRun('Current Processors')
            ->getFont()
            ->setSize(12)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));
        $title_cell->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $data_cell = $row->nextCell();
        $data_cell->setWidth(70);

        $title_cell->setColSpan(2);

        //table_data
        $count_row = 0;
        //dd($table_data);
        foreach ($table_data as $key => $value) {
            $row = $tableShape->createRow();
            $row->setHeight(20);
            $row->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFFFFFFF'))
                ->setEndColor(new Color('FFFFFFFF'));

            $key_format = str_replace('_', ' ', $key);

            $cell1 = $row->nextCell();

            $cell1->createTextRun($key_format)
                ->getFont()
                ->setSize(10)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell1->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setMarginLeft(5);

            $cell2 = $row->nextCell();

            $cell2->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFD9D9D9'))
                ->setEndColor(new Color('FFD9D9D9'));

            $value_format = $value;
            $cell2->createTextRun($value_format)
                ->getFont()
                ->setSize(10)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell2->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            //format
            if ($count_row == 0) {
                $cell1->getBorders()->getBottom()->setLineWidth(0);
                $cell2->getBorders()->getBottom()->setLineWidth(0);
            }

            if ($count_row > 0) {
                $cell1->getBorders()->getTop()->setLineWidth(0);
                $cell1->getBorders()->getBottom()->setLineWidth(0);

                $cell2->getBorders()->getTop()->setLineWidth(0);
                $cell2->getBorders()->getBottom()->setLineWidth(0);
            }
            $count_row++;
        }
        return $tableShape;
    }

    //Create Table SpreadOfVMTypes
    function createTable_SpreadOfVMTypes_slide10($tableShape, $table_data)
    {
        //Add row 1
        $row = $tableShape->createRow();
        $row->setHeight(25);
        $row->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FF0080FF'))
            ->setEndColor(new Color('FF0080FF'));

        $title_cell = $row->nextCell();
        $title_cell->setWidth(319.52);

        $title_cell->createTextRun('Spread of VM Types')
            ->getFont()
            ->setSize(12)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $title_cell->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setMarginLeft(2);

        $data_cell = $row->nextCell();
        $data_cell->setWidth(100);

        $title_cell->setColSpan(2);

        //table_data
        $count_row = 0;
        foreach ($table_data as $key => $value) {
            $row = $tableShape->createRow();
            $row->setHeight(20);
            $row->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFFFFFFF'))
                ->setEndColor(new Color('FFFFFFFF'));

            $key_format = str_replace('_', ' ', $key);

            $cell1 = $row->nextCell();

            $cell1->createTextRun($key_format)
                ->getFont()
                ->setSize(10)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell1->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setMarginLeft(12);

            $cell2 = $row->nextCell();

            $cell2->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFD9D9D9'))
                ->setEndColor(new Color('FFD9D9D9'));

            $cell2->createTextRun(number_format($value * 100, 0) . '%')
                ->getFont()
                ->setSize(10)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell2->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            //format
            if ($count_row == 0) {
                $cell1->getBorders()->getBottom()->setLineWidth(0);
                $cell2->getBorders()->getBottom()->setLineWidth(0);
            }

            if ($count_row == 1) {
                $cell1->getBorders()->getTop()->setLineWidth(0);
                $cell2->getBorders()->getTop()->setLineWidth(0);
            }

            $count_row++;
        }

        return $tableShape;
    }

    //Create Table StorageCostFactorsComparision
    function createTable_StorageCostFactorsComparision_slide19($tableShape, $table_data, $ppt_currency, $ppt_exchange_currency)
    {
        //Add row tilte and format
        $row1 = $tableShape->createRow();
        $row1->setHeight(8);
        $row1->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FF0080FF'))
            ->setEndColor(new Color('FF0080FF'));

        $title_cell1_1 = $row1->nextCell();
        $title_cell1_1->setWidth(338.72);

        $title_cell1_1->createTextRun(Lang::get('powerpoint.storage_cost_factor_comparison'))
            ->getFont()
            ->setSize(8)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $title_cell1_1->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setMarginLeft(2);

        $data_cell1_1 = $row1->nextCell();
        $data_cell1_1->setWidth(50);
        $data_cell1_1->createTextRun('per GB')
            ->getFont()
            ->setSize(8)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $data_cell1_1->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $data_cell1_1->setColSpan(2);

        $data_cell1_1->getBorders()->getLeft()->setLineWidth(0);
        $data_cell1_1->getBorders()->getBottom()->setLineWidth(0);

        $data_cell1_2 = $row1->nextCell();
        $data_cell1_2->setWidth(50);

        $row2 = $tableShape->createRow();
        $row2->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FF0080FF'))
            ->setEndColor(new Color('FF0080FF'));
        $title_cell2_1 = $row2->nextCell();

        $data_cell2_1 = $row2->nextCell();
        $data_cell2_1->createTextRun('CusCos')
            ->getFont()
            ->setSize(8)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));
        $data_cell2_1->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $data_cell2_1->createBreak();

        $data_cell2_1->createTextRun($ppt_currency)
            ->getFont()
            ->setSize(8)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));
        $data_cell2_1->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $data_cell2_1->getBorders()->getRight()->setLineWidth(0);
        $data_cell2_1->getBorders()->getLeft()->setLineWidth(0);

        $data_cell2_2 = $row2->nextCell();
        $data_cell2_2->createTextRun('WeAz')
            ->getFont()
            ->setSize(8)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));
        $data_cell2_2->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $data_cell2_2->createBreak();
        $data_cell2_2->createTextRun($ppt_currency)
            ->getFont()
            ->setSize(8)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));
        $data_cell2_2->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $data_cell2_2->getBorders()->getTop()->setLineWidth(0);

        $title_cell1_1->setRowSpan(2);
        $title_cell1_1->getBorders()->getRight()->setLineWidth(0);

        //table_data
        $count_row = 0;
        foreach ($table_data as $mainKey => $mainValue) {
            $row = $tableShape->createRow();
            $row->setHeight(8);
            $row->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFF2F2F2'))
                ->setEndColor(new Color('FFF2F2F2'));

            $cell1 = $row->nextcell();

            //$mainKey_format = ucwords(str_replace('_',' ', $mainKey));
            $mainKey_format = ucwords(Lang::get('powerpoint.' . $mainKey));

            $cell1->createTextRun($mainKey_format)
                ->getFont()
                ->setSize(8)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell1->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setMarginLeft(12);

            foreach ($mainValue as $key => $value) {
                $data_cell = $row->nextcell();

                $data_cell->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setRotation(90)
                    ->setStartColor(new Color('FFD9D9D9'))
                    ->setEndColor(new Color('FFD9D9D9'));

                $data_cell->createTextRun(number_format($value * $ppt_exchange_currency, 3, '.', ','))
                    ->getFont()
                    ->setSize(8)
                    ->setName('Trebuchet MS')
                    ->setColor(new Color('FF000000'));

                $data_cell->getActiveParagraph()
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                //format
                if ($count_row == 0) {
                    $data_cell->getBorders()->getBottom()->setLineWidth(0);
                }

                if ($count_row == 1) {
                    $data_cell->getBorders()->getTop()->setLineWidth(0);
                }
            }

            //format
            if ($count_row == 0) {
                $cell1->getBorders()->getBottom()->setLineWidth(0);
            }

            if ($count_row == 1) {
                $cell1->getBorders()->getTop()->setLineWidth(0);
            }

            $count_row++;
        }
        return $tableShape;
    }

    //Create Table AdjustingTheStorageMix
    function createTable_AdjustingTheStorageMix_slide19($tableShape, $table_data, $ppt_currency)
    {
        //table Weighted Storage Mix
        //Add row tilte and format
        $row1 = $tableShape->createRow();
        $row1->setHeight(8);
        $row1->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FF0080FF'))
            ->setEndColor(new Color('FF0080FF'));

        $title_cell1_1 = $row1->nextCell();
        $title_cell1_1->setWidth(238.72);

        $title_cell1_1->createTextRun('Primary Storage Mix                                       Total Primary Storage: ' . $table_data['total_primary_storage'])
            ->getFont()
            ->setSize(9)
            ->setBold(true)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $title_cell1_1->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setMarginLeft(2);

        // $title_cell1_2 = $row1->nextCell();
        // $title_cell1_2->setWidth(119.36);
        // $title_cell1_2->createTextRun('Total Primary Storage: 500TB')
        //     ->getFont()
        //     ->setSize(9)
        //     ->setBold(true)
        //     ->setName('Trebuchet MS')
        //     ->setColor(new Color('FFFFFFFF'));

        // $title_cell1_2->getActiveParagraph()
        //     ->getAlignment()
        //     ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
        //     ->setVertical(Alignment::VERTICAL_CENTER)
        //     ->setMarginLeft(2);



        $data_cell1_2 = $row1->nextCell();
        $data_cell1_2->setWidth(100);

        $data_cell1_3 = $row1->nextCell();
        $data_cell1_3->setWidth(100);
        $title_cell1_1->setColSpan(3);
        //table data Storage Mix
        $count_row = 0;
        foreach ($table_data['weighted_primary_storage_usage_allocation'] as $key => $value) {
            $row = $tableShape->createRow();
            $row->setHeight(20);
            $row->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFF2F2F2'))
                ->setEndColor(new Color('FFF2F2F2'));

            $key_format = str_replace('_', '/', $key);

            $cell1 = $row->nextCell();

            // if($key=="SaS_SATA"){
            //     $cell1->createTextRun($key_format)
            //         ->getFont()
            //         ->setBold(true)
            //         ->setSize(8)
            //         ->setName('Trebuchet MS')
            //         ->setColor(new Color('FF000000'));

            //     $cell1->getActiveParagraph()
            //         ->getAlignment()
            //         ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            //         ->setVertical(Alignment::VERTICAL_CENTER)
            //         ->setMarginLeft(2);
            // }
            // else {
            $cell1->createTextRun($key_format)
                ->getFont()
                ->setSize(8)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));
            $cell1->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setMarginLeft(2);
            //}

            $cell2 = $row->nextCell();
            $cell2->setColSpan(2);

            $cell2->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFD9D9D9'))
                ->setEndColor(new Color('FFD9D9D9'));

            $cell2->createTextRun(number_format($value * 100, 0) . '%')
                ->getFont()
                ->setSize(8)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell2->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            //format
            if ($count_row == 0) {
                $cell1->getBorders()->getBottom()->setLineWidth(0);
                $cell1->getBorders()->getRight()->setDashStyle(Border::DASH_DOT);

                $cell2->getBorders()->getLeft()->setDashStyle(Border::DASH_DOT);
                $cell2->getBorders()->getBottom()->setDashStyle(Border::DASH_DOT);
            }
            if ($count_row == 1) {
                $cell1->getBorders()->getTop()->setLineWidth(0);
                $cell1->getBorders()->getRight()->setDashStyle(Border::DASH_DOT);

                $cell2->getBorders()->getLeft()->setDashStyle(Border::DASH_DOT);
                $cell2->getBorders()->getTop()->setDashStyle(Border::DASH_DOT);
            }
            $count_row++;
        }

        //Primary Storage Mix
        $row1 = $tableShape->createRow();
        $row1->setHeight(8);
        $row1->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FF0080FF'))
            ->setEndColor(new Color('FF0080FF'));

        $title_cell1_1 = $row1->nextCell();
        $title_cell1_1->createTextRun('Primary Storage SSD Mix')
            ->getFont()
            ->setSize(9)
            ->setBold(true)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $title_cell1_1->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setMarginLeft(2);

        $title_cell1_2 = $row1->nextCell();
        $title_cell1_2->createTextRun('Price per GB')
            ->getFont()
            ->setSize(9)
            ->setBold(true)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $title_cell1_2->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setMarginLeft(2);

        $title_cell1_3 = $row1->nextCell();
        $title_cell1_3->createTextRun('Percentage allocated')
            ->getFont()
            ->setSize(9)
            ->setBold(true)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $title_cell1_3->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setMarginLeft(2);

        $count_row = 0;
        foreach ($table_data['primary_storage_mix'] as $value) {
            $row = $tableShape->createRow();
            $row->setHeight(20);
            $row->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFFFFFFF'))
                ->setEndColor(new Color('FFFFFFFF'));

            $cell1 = $row->nextCell();
            $cell1->createTextRun($value['storage_type_name'])
                ->getFont()
                ->setSize(8)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell1->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setMarginLeft(2);

            $cell2 = $row->nextCell();
            $cell2->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFFFFFFF'))
                ->setEndColor(new Color('FFFFFFFF'));

            $cell2->createTextRun($ppt_currency . ' ' . number_format($value['price_per_gb'], 4))
                ->getFont()
                ->setSize(8)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell2->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            $cell3 = $row->nextCell();
            $cell3->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFFFFFFF'))
                ->setEndColor(new Color('FFFFFFFF'));

            $cell3->createTextRun(number_format($value['percentage_allocated'] * 100, 0) . '%')
                ->getFont()
                ->setSize(8)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell3->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            //format
            if ($count_row == 0) {
                $cell1->getBorders()->getTop()->setLineWidth(0);
                $cell1->getBorders()->getBottom()->setLineWidth(0);
                $cell1->getBorders()->getRight()->setDashStyle(Border::DASH_DOT);

                $cell2->getBorders()->getRight()->setDashStyle(Border::DASH_DOT);
                $cell2->getBorders()->getLeft()->setDashStyle(Border::DASH_DOT);
                $cell2->getBorders()->getBottom()->setDashStyle(Border::DASH_DOT);
                $cell2->getBorders()->getTop()->setLineWidth(0);

                $cell3->getBorders()->getTop()->setLineWidth(0);
                $cell3->getBorders()->getLeft()->setDashStyle(Border::DASH_DOT);
                $cell3->getBorders()->getBottom()->setDashStyle(Border::DASH_DOT);
                //$cell3->getBorders()->getRight()->setDashStyle(Border::DASH_DOT);
                //$cell3->getBorders()->getBottom()->setDashStyle(Border::DASH_DOT);
            }
            if ($count_row > 0) {
                $cell1->getBorders()->getBottom()->setLineWidth(0);
                $cell1->getBorders()->getTop()->setLineWidth(0);
                $cell1->getBorders()->getRight()->setDashStyle(Border::DASH_DOT);

                $cell2->getBorders()->getTop()->setLineWidth(0);
                $cell2->getBorders()->getLeft()->setDashStyle(Border::DASH_DOT);
                $cell2->getBorders()->getBottom()->setDashStyle(Border::DASH_DOT);
                $cell2->getBorders()->getRight()->setDashStyle(Border::DASH_DOT);

                $cell3->getBorders()->getTop()->setLineWidth(0);
                $cell3->getBorders()->getTop()->setDashStyle(Border::DASH_DOT);
                $cell3->getBorders()->getLeft()->setDashStyle(Border::DASH_DOT);
                //$cell3->getBorders()->getRight()->setDashStyle(Border::DASH_DOT);
                //$cell3->getBorders()->getBottom()->setDashStyle(Border::DASH_DOT);
            }

            // $count_row++;
        }

        //Table Weighted Backup Storage
        //Add row tilte and format
        $row1 = $tableShape->createRow();
        $row1->setHeight(8);
        $row1->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FF0080FF'))
            ->setEndColor(new Color('FF0080FF'));

        $title_cell1_1 = $row1->nextCell();

        $title_cell1_1->createTextRun('Weighted Backup Storage                               Total Backup Storage: ' . $table_data['total_backup_storage'])
            ->getFont()
            ->setSize(9)
            ->setBold(true)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $title_cell1_1->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setMarginLeft(2);

        $data_cell1_2 = $row1->nextCell();
        $data_cell1_3 = $row1->nextCell();

        $title_cell1_1->setColSpan(3);

        //table data Weighted Backup Storage
        $count_row = 0;
        foreach ($table_data['weighted_backup_storage'] as $key => $value) {
            $row = $tableShape->createRow();
            $row->setHeight(20);
            $row->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFFFFFFF'))
                ->setEndColor(new Color('FFFFFFFF'));

            $key_format = str_replace('_', ' ', $key);

            $cell1 = $row->nextCell();

            $cell1->createTextRun($key_format)
                ->getFont()
                ->setSize(8)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell1->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setMarginLeft(2);

            $cell2 = $row->nextCell();
            $cell2->setColSpan(2);

            $cell2->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFFFFFFF'))
                ->setEndColor(new Color('FFFFFFFF'));

            $cell2->createTextRun(number_format($value * 100, 0) . '%')
                ->getFont()
                ->setSize(8)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell2->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            //format
            if ($count_row == 0) {
                $cell1->getBorders()->getBottom()->setLineWidth(0);
                $cell1->getBorders()->getRight()->setDashStyle(Border::DASH_DOT);

                $cell2->getBorders()->getLeft()->setDashStyle(Border::DASH_DOT);
                $cell2->getBorders()->getBottom()->setDashStyle(Border::DASH_DOT);
            }

            if ($count_row == 1) {
                $cell1->getBorders()->getTop()->setLineWidth(0);
                $cell1->getBorders()->getBottom()->setLineWidth(0);
                $cell1->getBorders()->getRight()->setDashStyle(Border::DASH_DOT);

                $cell2->getBorders()->getTop()->setDashStyle(Border::DASH_DOT);
                $cell2->getBorders()->getLeft()->setDashStyle(Border::DASH_DOT);
                $cell2->getBorders()->getBottom()->setLineWidth(1);
            }

            if ($count_row == 2) {
                $cell1->getBorders()->getTop()->setLineWidth(0);
                $cell1->getBorders()->getRight()->setDashStyle(Border::DASH_DOT);

                $cell2->getBorders()->getLeft()->setDashStyle(Border::DASH_DOT);
                $cell2->getBorders()->getTop()->setLineWidth(0);
            }

            $count_row++;
        }

        return $tableShape;
    }

    //Create Table CorrectedVMMix
    function createTable_CorrectedVMMix_slide19($tableShape, $table_data)
    {
        //Add row 1
        $row = $tableShape->createRow();
        $row->setHeight(8);
        $row->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FFFFC000'))
            ->setEndColor(new Color('FFFFC000'));

        $title_cell = $row->nextCell();
        $title_cell->setWidth(152);
        $title_cell->createTextRun('Corrected VM mix')
            ->getFont()
            ->setSize(8)
            ->setBold(8)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FF000000'));
        $title_cell->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setMarginLeft(2);

        $data_cell = $row->nextCell();
        $data_cell->setWidth(40);

        $title_cell->setColSpan(2);

        //table_data
        $count_row = 0;
        foreach ($table_data as $key => $value) {
            $row = $tableShape->createRow();
            $row->setHeight(20);

            $key_format = str_replace('_', ' ', $key);

            $cell1 = $row->nextCell();

            $cell1->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFF2F2F2'))
                ->setEndColor(new Color('FFF2F2F2'));

            $cell1->createTextRun($key_format)
                ->getFont()
                ->setSize(7)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell1->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setMarginLeft(7);

            $cell2 = $row->nextCell();

            $cell2->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFD9D9D9'))
                ->setEndColor(new Color('FFD9D9D9'));

            $cell2->createTextRun(number_format($value * 100, 2, '.', ',') . '%')
                ->getFont()
                ->setSize(7)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell2->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            //format
            if ($count_row == 0) {
                $cell1->getBorders()->getBottom()->setLineWidth(0);
            }

            if ($count_row == 1) {
                $cell1->getBorders()->getTop()->setLineWidth(0);
            }

            if ($count_row == 2) {
                $cell2->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setRotation(90)
                    ->setStartColor(new Color('FFFFFFFF'))
                    ->setEndColor(new Color('FFFFFFFF'));
            }

            $count_row++;
        }

        return $tableShape;
    }

    //Create Table AzureSiteRecovery
    function createTable_AzureSiteRecovery_slide19($tableShape, $table_data)
    {
        //Add row 1
        $row = $tableShape->createRow();
        $row->setHeight(8);
        $row->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FFFFC000'))
            ->setEndColor(new Color('FFFFC000'));

        $title_cell = $row->nextCell();
        $title_cell->setWidth(172.16);
        $title_cell->createTextRun('Azure Site Recovery')
            ->getFont()
            ->setSize(8)
            ->setBold(8)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FF000000'));
        $title_cell->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setMarginLeft(2);

        $data_cell = $row->nextCell();
        $data_cell->setWidth(40);

        $title_cell->setColSpan(2);

        //table_data
        $count_row = 0;
        foreach ($table_data as $key => $value) {
            $row = $tableShape->createRow();
            $row->setHeight(20);
            $row->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFF2F2F2'))
                ->setEndColor(new Color('FFF2F2F2'));

            $key_format = str_replace('_', ' ', $key);

            $cell1 = $row->nextCell();

            $cell1->createTextRun(ucwords($key_format))
                ->getFont()
                ->setSize(7)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell1->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setMarginLeft(7);

            $cell2 = $row->nextCell();

            $cell2->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFD9D9D9'))
                ->setEndColor(new Color('FFD9D9D9'));

            $cell2->createTextRun($value)
                ->getFont()
                ->setSize(7)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell2->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            //format
            if ($count_row == 0) {
                $cell1->getBorders()->getBottom()->setLineWidth(0);
            }

            if ($count_row == 1) {
                $cell1->getBorders()->getTop()->setLineWidth(0);
            }

            if ($count_row == 2) {
                $cell2->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setRotation(90)
                    ->setStartColor(new Color('FFFFFFFF'))
                    ->setEndColor(new Color('FFFFFFFF'));
            }

            $count_row++;
        }

        return $tableShape;
    }

    //Create Table InternalMemoryCostFactorComparison
    function createTable_PricePerGBRAMForPrimaryStorage_slide19($tableShape, $table_data, $ppt_currency, $ppt_exchange_currency)
    {
        //Add row tilte and format
        $row1 = $tableShape->createRow();
        $row1->setHeight(8);
        $row1->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FF0080FF'))
            ->setEndColor(new Color('FF0080FF'));

        $title_cell1_1 = $row1->nextCell();
        $title_cell1_1->setWidth(338.72);

        $title_cell1_1->createTextRun('Internal Memory cost factor comparison')
            ->getFont()
            ->setSize(8)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $title_cell1_1->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setMarginLeft(2);

        $data_cell1_1 = $row1->nextCell();
        $data_cell1_1->setWidth(50);
        $data_cell1_1->createTextRun('per GB/Ram')
            ->getFont()
            ->setSize(8)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $data_cell1_1->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $data_cell1_1->setColSpan(2);

        $data_cell1_1->getBorders()->getLeft()->setLineWidth(0);
        $data_cell1_1->getBorders()->getBottom()->setLineWidth(0);

        $data_cell1_2 = $row1->nextCell();
        $data_cell1_2->setWidth(50);

        $row2 = $tableShape->createRow();
        $row2->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FF0080FF'))
            ->setEndColor(new Color('FF0080FF'));
        $title_cell2_1 = $row2->nextCell();

        $data_cell2_1 = $row2->nextCell();
        $data_cell2_1->createTextRun('CusCos')
            ->getFont()
            ->setSize(8)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));
        $data_cell2_1->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $data_cell2_1->createBreak();

        $data_cell2_1->createTextRun($ppt_currency)
            ->getFont()
            ->setSize(8)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));
        $data_cell2_1->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $data_cell2_1->getBorders()->getRight()->setLineWidth(0);
        $data_cell2_1->getBorders()->getLeft()->setLineWidth(0);

        $data_cell2_2 = $row2->nextCell();
        $data_cell2_2->createTextRun('WeAz')
            ->getFont()
            ->setSize(8)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));
        $data_cell2_2->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $data_cell2_2->createBreak();
        $data_cell2_2->createTextRun($ppt_currency)
            ->getFont()
            ->setSize(8)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));
        $data_cell2_2->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $data_cell2_2->getBorders()->getTop()->setLineWidth(0);

        $title_cell1_1->setRowSpan(2);
        $title_cell1_1->getBorders()->getRight()->setLineWidth(0);

        //table_data
        //dd($table_data);
        $count_row = 0;
        foreach ($table_data as $mainKey => $mainValue) {
            $row = $tableShape->createRow();
            $row->setHeight(8);
            $row->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFF2F2F2'))
                ->setEndColor(new Color('FFF2F2F2'));

            $cell1 = $row->nextcell();

            $mainKey_formated = ucwords(str_replace('_', ' ', $mainKey));

            $cell1->createTextRun($mainKey_formated)
                ->getFont()
                ->setSize(8)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell1->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setMarginLeft(12);

            foreach ($mainValue as $key => $value) {
                $data_cell = $row->nextcell();

                $data_cell->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setRotation(90)
                    ->setStartColor(new Color('FFD9D9D9'))
                    ->setEndColor(new Color('FFD9D9D9'));

                $data_cell->createTextRun(number_format($value * $ppt_exchange_currency, 2, '.', ','))
                    ->getFont()
                    ->setSize(8)
                    ->setName('Trebuchet MS')
                    ->setColor(new Color('FF000000'));

                $data_cell->getActiveParagraph()
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                if ($count_row == 0) {
                    $data_cell->getBorders()->getBottom()->setLineWidth(0);
                }

                if ($count_row == 9) {
                    $data_cell->getBorders()->getTop()->setLineWidth(0);
                }

                if ($count_row > 0 && $count_row < 9) {
                    $data_cell->getBorders()->getTop()->setLineWidth(0);
                    $data_cell->getBorders()->getBottom()->setLineWidth(0);
                }
            }
            //format
            if ($count_row == 0) {
                $cell1->getBorders()->getBottom()->setLineWidth(0);
            }

            if ($count_row == 9) {
                $cell1->getBorders()->getTop()->setLineWidth(0);
            }

            if ($count_row > 0 && $count_row < 9) {
                $cell1->getBorders()->getTop()->setLineWidth(0);
                $cell1->getBorders()->getBottom()->setLineWidth(0);
            }

            $count_row++;
        }
        //echo ($count_row);exit;
        return $tableShape;
    }

    //Create Table BenefitsOnSwitchingOnOffVMs
    function createTable_BenefitsOnSwitchingOnOffVMs_slide21($tableShape, $table_data)
    {
        //Add row 1
        $row = $tableShape->createRow();
        $row->setHeight(8);
        $row->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FFFFC000'))
            ->setEndColor(new Color('FFFFC000'));

        $title_cell = $row->nextCell();
        $title_cell->setWidth(420);
        $title_cell->createTextRun("Benefits on Switching on/off VMs")
            ->getFont()
            ->setSize(8)
            ->setBold(8)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FF000000'));
        $title_cell->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setMarginLeft(2);

        $data_cell = $row->nextCell();
        $data_cell->setWidth(60);

        $title_cell->setColSpan(2);

        //table_data
        //dd($table_data);
        $count_row = 0;
        foreach ($table_data as $key => $value) {
            $row = $tableShape->createRow();
            $row->setHeight(20);

            $key_format = str_replace('_', ' ', $key);
            $key_format = str_replace('-', '/', $key_format);

            $cell1 = $row->nextCell();

            $cell1->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFFFFFFF'))
                ->setEndColor(new Color('FFFFFFFF'));

            $cell1->createTextRun($key_format)
                ->getFont()
                ->setSize(7)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell1->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setMarginLeft(7);

            $cell2 = $row->nextCell();

            $cell2->createTextRun(number_format($value * 100, 0) . '%')
                ->getFont()
                ->setSize(7)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell2->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            //format
            if ($count_row == 0) {
                $cell2->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setRotation(90)
                    ->setStartColor(new Color('FFD9D9D9'))
                    ->setEndColor(new Color('FFD9D9D9'));
            }

            $count_row++;
        }
        return $tableShape;
    }

    //Create Table BenefitsOnOptimization
    function createTable_BenefitsOnOptimization_slide23($tableShape, $table_data)
    {
        //Add row 1
        $row = $tableShape->createRow();
        $row->setHeight(8);
        $row->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FFFFC000'))
            ->setEndColor(new Color('FFFFC000'));

        $title_cell = $row->nextCell();
        $title_cell->setWidth(420);
        $title_cell->createTextRun('Benefits on Optimization')
            ->getFont()
            ->setSize(8)
            ->setBold(8)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FF000000'));
        $title_cell->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setMarginLeft(2);

        $data_cell = $row->nextCell();
        $data_cell->setWidth(60);

        $title_cell->setColSpan(2);

        //table_data
        //dd($table_data);
        $count_row = 0;
        foreach ($table_data as $key => $value) {
            $row = $tableShape->createRow();
            $row->setHeight(20);

            $key_format = str_replace('_', ' ', $key);

            $cell1 = $row->nextCell();

            $cell1->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFFFFFFF'))
                ->setEndColor(new Color('FFFFFFFF'));

            $cell1->createTextRun($key_format)
                ->getFont()
                ->setSize(7)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell1->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setMarginLeft(7);

            $cell2 = $row->nextCell();

            $cell2->createTextRun(number_format($value * 100, 0) . '%')
                ->getFont()
                ->setSize(7)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell2->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            //format
            if ($count_row == 0) {
                $cell1->getBorders()->getBottom()->setLineWidth(0);
                $cell1->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setRotation(90)
                    ->setStartColor(new Color('FFD9D9D9'))
                    ->setEndColor(new Color('FFD9D9D9'));

                $cell2->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setRotation(90)
                    ->setStartColor(new Color('FFD9D9D9'))
                    ->setEndColor(new Color('FFD9D9D9'));
            }

            if ($count_row == 1) {
                $cell1->getBorders()->getTop()->setLineWidth(0);
            }

            $count_row++;
        }
        return $tableShape;
    }

    //Create Table OptimisingTheStorageUsageWhenMigratingToAzure
    function createTable_OptimisingTheStorageUsageWhenMigratingToAzure_slide23($tableShape, $table_data)
    {
        //Add row 1
        $row = $tableShape->createRow();
        $row->setHeight(8);
        $row->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FFFFC000'))
            ->setEndColor(new Color('FFFFC000'));

        $title_cell = $row->nextCell();
        $title_cell->setWidth(420);
        $title_cell->createTextRun('Optimising the storage usage when migrating to Azure')
            ->getFont()
            ->setSize(8)
            ->setBold(8)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FF000000'));
        $title_cell->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setMarginLeft(2);

        $data_cell = $row->nextCell();
        $data_cell->setWidth(60);

        $title_cell->setColSpan(2);

        //table_data
        //dd($table_data);
        $count_row = 0;
        foreach ($table_data as $key => $value) {
            $row = $tableShape->createRow();
            $row->setHeight(20);

            $key_format = ucwords(str_replace('_', ' ', $key));

            $cell1 = $row->nextCell();

            $cell1->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFFFFFFF'))
                ->setEndColor(new Color('FFFFFFFF'));

            $cell1->createTextRun($key_format)
                ->getFont()
                ->setSize(7)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell1->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setMarginLeft(7);

            $cell2 = $row->nextCell();

            $cell2->createTextRun(number_format($value * 100, 0) . '%')
                ->getFont()
                ->setSize(7)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FF000000'));

            $cell2->getActiveParagraph()
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            //format
            if ($count_row == 0) {
                $cell1->getBorders()->getBottom()->setLineWidth(0);
            }

            if ($count_row == 1) {
                $cell1->getBorders()->getTop()->setLineWidth(0);
            }

            $count_row++;
        }
        return $tableShape;
    }

    //Create Allocation Of Reserved Instances
    function createTable_AllocationOfReservedInstances_slide25($tableShape, $table_data, $ppt_currency, $ppt_exchange_currency)
    {
        $table_label = array(
            'VM-Series',
            'Weigthed',
            'RI 1Y',
            'RI 3Y',
            'RI 3Y Hyb',
        );

        //Add row 1
        $row = $tableShape->createRow();
        $row->setHeight(20);

        $row->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FFF2F2F2'))
            ->setEndColor(new Color('FFF2F2F2'));

        $cell = $row->nextCell();
        $cell->setWidth(144);
        $cell->createTextRun('ALLOCATION RESERVED INSTANCES')
            ->getFont()
            ->setBold(true)
            ->setSize(14)
            ->setName('Trebuchet MS');
        $cell->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setMarginLeft(5);

        $cell2 = $row->nextCell();
        $cell2->setWidth(144);

        $cell3 = $row->nextCell();
        $cell3->setWidth(96);

        $cell4 = $row->nextCell();
        $cell4->setWidth(96);

        $cell5 = $row->nextCell();
        $cell5->setWidth(96);

        $cell->setColSpan(5);

        //Add row 2 Label
        $orow = $tableShape->createRow();
        $orow->setHeight(15);
        $orow->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FF0080FF'))
            ->setEndColor(new Color('FF0080FF'));

        $count_row = 0;
        foreach ($table_label as $key => $value) {
            $oCell = $orow->nextCell();
            $oCell->createTextRun($value)
                ->getFont()
                ->setBold(true)
                ->setSize(11)
                ->setName('Trebuchet MS')
                ->setColor(new Color('FFFFFFFF'));

            if ($count_row == 0) {
                $oCell->getActiveParagraph()->getAlignment()->setMarginLeft(5);
                $oCell->getBorders()->getRight()->setLineWidth(0);
            }

            if ($count_row == 4) {
                $oCell->getBorders()->getLeft()->setLineWidth(0);
                $oCell->getActiveParagraph()->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
            }
            if ($count_row > 0 && $count_row < 4) {
                $oCell->getBorders()->getRight()->setLineWidth(0);
                $oCell->getBorders()->getLeft()->setLineWidth(0);
                $oCell->getActiveParagraph()->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
            }
            $count_row++;
        }

        //table_data
        //dd($table_data);
        $count_row_data = 0;
        $mo_row = $table_data['RI_allocation']['MO_row_location'];
        $line_location = (($mo_row - 1) * 2) + 2;

        unset($table_data['RI_allocation']['MO_row_location']);

        foreach ($table_data['RI_allocation'] as $mainKey => $mainValue) {
            $row_data = $tableShape->createRow();
            $row_data->setHeight(15);

            foreach ($mainValue as $key => $value) {
                $value_format = "";
                $cell_data = $row_data->nextcell();

                if ($key == 'VM-Series') {
                    $value_format = $value;
                } else {
                    if ($value == "" && $value != "0.0")
                        $value_format = $value;
                    else
                        $value_format = number_format($value * 100, 0) . '%';
                }

                if ($value == 'General Purpose' || $value == 'Memory Optimized') {
                    $cell_data->createTextRun($value_format)
                        ->getFont()
                        ->setBold(true)
                        ->setSize(11)
                        ->setName('Trebuchet MS')
                        ->setColor(new Color('FF000000'));
                } else {
                    $cell_data->createTextRun($value_format)
                        ->getFont()
                        ->setBold(false)
                        ->setSize(11)
                        ->setName('Trebuchet MS')
                        ->setColor(new Color('FF000000'));
                }
                //format            
                if ($key == 'VM-Series') {
                    $cell_data->getActiveParagraph()
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setMarginLeft(5);

                    if ($value != 'General Purpose' && $value != 'Memory Optimized') {
                        $cell_data->getFill()->setFillType(Fill::FILL_SOLID)
                            ->setRotation(90)
                            ->setStartColor(new Color('FFF2F2F2'))
                            ->setEndColor(new Color('FFF2F2F2'));
                    }

                    if ($count_row_data == 1) {
                        $cell_data->getBorders()->getBottom()->setLineWidth(0);
                    }

                    if (($count_row_data > 1 && $count_row_data < $mo_row - 1) || ($count_row_data > $mo_row + 1 && $count_row_data < count($table_data['RI_allocation']) - 1)) {
                        $cell_data->getBorders()->getTop()->setLineWidth(0);
                        $cell_data->getBorders()->getBottom()->setLineWidth(0);
                    }

                    if ($count_row_data == $line_location) {
                        $cell_data->getBorders()->getTop()->setLineWidth(1);
                    }

                    if ($count_row_data == count($table_data['RI_allocation']) - 1) {
                        $cell_data->getBorders()->getTop()->setLineWidth(0);
                    }
                } else {
                    if ($key == 'Weighted') {
                        if ($count_row_data != 0 && $count_row_data != $mo_row) {
                            $cell_data->getFill()->setFillType(Fill::FILL_SOLID)
                                ->setRotation(90)
                                ->setStartColor(new Color('FFD9D9D9'))
                                ->setEndColor(new Color('FFD9D9D9'));
                        }
                    }
                    $cell_data->getActiveParagraph()
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }

                if ($key == "RI_1Y" && $value == "" && $value != "0.0") {
                    $cell_data->getBorders()->getRight()->setLineWidth(0);
                }

                if ($key == "RI_3Y" && $value == "" && $value != "0.0") {
                    $cell_data->getBorders()->getRight()->setLineWidth(0);
                    $cell_data->getBorders()->getLeft()->setLineWidth(0);
                }

                if ($key == "RI_3Y_Hyb" && $value == "" && $value != "0.0") {
                    $cell_data->getBorders()->getLeft()->setLineWidth(0);
                }
            }
            $count_row_data++;
        }

        //Add row pre-payment
        $row = $tableShape->createRow();
        $row->setHeight(15);

        $row->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FFF2F2F2'))
            ->setEndColor(new Color('FFF2F2F2'));

        $cell1 = $row->nextCell();

        $cell1->createTextRun('Pre-payment per category')
            ->getFont()
            ->setSize(11)
            ->setName('Trebuchet MS');
        $cell1->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $cell2 = $row->nextCell();

        $cell1->setColSpan(2);

        $cell1->getBorders()->getRight()->setLineWidth(0);

        $count_row = 0;
        $total_pre_payment_reserved_instances = 0;

        foreach ($table_data['pre_payment_reserved_instances'] as $key => $value) {
            if ($count_row < 3) {
                $cell = $row->nextCell();

                $cell->createTextRun(number_format($value * $ppt_exchange_currency, 0, '.', ','))
                    ->getFont()
                    ->setSize(11)
                    ->setName('Trebuchet MS');

                $cell->getActiveParagraph()
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                //format
                if ($count_row != 2) {
                    $cell->getBorders()->getLeft()->setLineWidth(0);
                    $cell->getBorders()->getRight()->setLineWidth(0);
                } else
                    $cell->getBorders()->getLeft()->setLineWidth(0);
            } else
                $total_pre_payment_reserved_instances = number_format($value * $ppt_exchange_currency, 0, '.', ',');

            $count_row++;
        }

        //Add row total pre-payment for all RI
        $row = $tableShape->createRow();
        $row->setHeight(15);

        $row->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FFF2F2F2'))
            ->setEndColor(new Color('FFF2F2F2'));

        $cell1 = $row->nextCell();

        $cell1->createTextRun('Total pre-payment for all RI')
            ->getFont()
            ->setBold(true)
            ->setSize(11)
            ->setName('Trebuchet MS');
        $cell1->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $cell2 = $row->nextCell();

        $cell1->setColSpan(2);

        $cell3 = $row->nextCell();
        $cell3->createTextRun($ppt_currency)
            ->getFont()
            ->setBold(true)
            ->setSize(11)
            ->setName('Trebuchet MS');
        $cell3->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $cell4 = $row->nextCell();
        $cell4->createTextRun($total_pre_payment_reserved_instances)
            ->getFont()
            ->setBold(true)
            ->setSize(11)
            ->setName('Trebuchet MS');
        $cell4->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $cell5 = $row->nextCell();

        $cell4->setColSpan(2);
        //format
        $cell1->getBorders()->getRight()->setLineWidth(0);
        $cell3->getBorders()->getLeft()->setLineWidth(0);
        $cell3->getBorders()->getRight()->setLineWidth(0);
        $cell4->getBorders()->getLeft()->setLineWidth(0);

        return $tableShape;
    }

    //Create Azure Quality Of Services
    function createTable_AzureQualityOfServices_slide28($tableShape, $table_data, $ppt_currency, $ppt_exchange_currency)
    {
        $count_row = 0;

        foreach ($table_data as $row_title => $main_value) {
            $title = str_replace('_', ' ', $row_title);

            $row = $tableShape->createRow();
            $row->setHeight(20);

            if ($count_row == 0)
                $row->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setRotation(90)
                    ->setStartColor(new Color('FF0080FF'))
                    ->setEndColor(new Color('FF0080FF'));
            else
                $row->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setRotation(90)
                    ->setStartColor(new Color('FFFFFFFF'))
                    ->setEndColor(new Color('FFFFFFFF'));

            $cell = $row->nextcell();
            $cell->setWidth(288.32);

            if ($count_row == 0) {
                $cell->createTextRun($title)
                    ->getFont()
                    ->setBold(true)
                    ->setSize(12)
                    ->setName('Trebuchet MS')
                    ->setColor(new Color('FFFFFFFF'));
                $cell->getActiveParagraph()
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
            } else {
                if ($title == "Service Level Agreement" || $title == "Back-up" || $title == "Disaster Recovery" || $title == "Compliancy") {
                    $cell->createTextRun($title)
                        ->getFont()
                        ->setBold(true)
                        ->setSize(11)
                        ->setName('Trebuchet MS')
                        ->setColor(new Color('FF000000'));
                    $cell->getActiveParagraph()
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setMarginLeft(2);
                } else {
                    $cell->createTextRun($title)
                        ->getFont()
                        ->setBold(false)
                        ->setSize(10)
                        ->setName('Trebuchet MS')
                        ->setColor(new Color('FF000000'));
                    $cell->getActiveParagraph()
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setMarginLeft(5);
                }
            }

            //table data
            foreach ($main_value as $key => $value) {
                $ocell = $row->nextcell();
                $ocell->setWidth(200);

                if ($count_row == 0) {
                    $ocell->createTextRun($value)
                        ->getFont()
                        ->setBold(true)
                        ->setSize(12)
                        ->setName('Trebuchet MS')
                        ->setColor(new Color('FFFFFFFF'));
                } else {
                    if ($row_title == 'Pricing_policy_for_DR' && $key == 'customer') {
                        $value_format = $ppt_currency . ' ' . number_format($value * $ppt_exchange_currency, 0);
                        $ocell->createTextRun($value_format)
                            ->getFont()
                            ->setBold(false)
                            ->setSize(10)
                            ->setName('Trebuchet MS')
                            ->setColor(new Color('FF000000'));
                    } else {
                        $ocell->createTextRun($value)
                            ->getFont()
                            ->setBold(false)
                            ->setSize(10)
                            ->setName('Trebuchet MS')
                            ->setColor(new Color('FF000000'));
                    }
                }

                $ocell->getActiveParagraph()
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
            }
            $count_row++;
        }

        return $tableShape;
    }

    //Create Business Case
    function createTable_BusinessCase_slide31($tableShape, $table_data)
    {
        //-- Begin Migration Inputs
        //Add row tilte and format
        $row = $tableShape->createRow();
        $row->setHeight(8);
        $row->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FF0080FF'))
            ->setEndColor(new Color('FF0080FF'));

        $title_cell_1 = $row->nextCell();
        $title_cell_1->setWidth(388.32);

        $title_cell_1->createTextRun('Business case in a 12-48 months perspective')
            ->getFont()
            ->setSize(11)
            ->setBold(true)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $title_cell_1->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setMarginLeft(2);

        $title_cell_2 = $row->nextCell();
        $title_cell_2->setWidth(100);

        $title_cell_2->createTextRun('Scenario 1')
            ->getFont()
            ->setSize(11)
            ->setBold(true)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $title_cell_2->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $title_cell_3 = $row->nextCell();
        $title_cell_3->setWidth(100);

        $title_cell_3->createTextRun('Scenario 2')
            ->getFont()
            ->setSize(11)
            ->setBold(true)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $title_cell_3->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $title_cell_4 = $row->nextCell();
        $title_cell_4->setWidth(100);

        $title_cell_4->createTextRun('Scenario 3')
            ->getFont()
            ->setSize(11)
            ->setBold(true)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $title_cell_4->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);


        //table data
        for ($row_count = 0; $row_count < 7; $row_count++) {
            $orow = $tableShape->createRow();
            $orow->setHeight(8);
            $orow->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFFFFFFF'))
                ->setEndColor(new Color('FFFFFFFF'));

            $cell_count = 0;
            foreach ($table_data[$row_count] as $key => $value) {
                $ocell =  $orow->nextCell();
                $ocell->createTextRun($value)
                    ->getFont()
                    ->setSize(10)
                    ->setBold(false)
                    ->setName('Trebuchet MS')
                    ->setColor(new Color('FF000000'));

                if ($cell_count == 0) {
                    $ocell->getActiveParagraph()
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setMarginLeft(2);
                } else {
                    $ocell->getActiveParagraph()
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }

                //format
                if ($row_count == 0) {
                    $ocell->getBorders()->getBottom()->setLineWidth(0);
                }

                if ($row_count == 6) {
                    $ocell->getBorders()->getTop()->setLineWidth(0);
                }

                if ($row_count > 0 && $row_count < 6) {
                    $ocell->getBorders()->getTop()->setLineWidth(0);
                    $ocell->getBorders()->getBottom()->setLineWidth(0);
                }

                $cell_count++;
            }
        }
        //-- End Migration Inputs

        //-- Begin Remaining Bookvalues
        //Add row tilte and format
        $row = $tableShape->createRow();
        $row->setHeight(8);
        $row->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FF0080FF'))
            ->setEndColor(new Color('FF0080FF'));

        $title_cell_1 = $row->nextCell();

        $title_cell_1->createTextRun('Remaining bookvalues at the end of the DC contract')
            ->getFont()
            ->setSize(11)
            ->setBold(true)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $title_cell_1->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setMarginLeft(2);

        $title_cell_1->setColSpan(4);

        //table data
        for ($row_count = 7; $row_count < 13; $row_count++) {
            $orow = $tableShape->createRow();
            $orow->setHeight(8);
            $orow->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFFFFFFF'))
                ->setEndColor(new Color('FFFFFFFF'));

            $cell_count = 0;
            foreach ($table_data[$row_count] as $key => $value) {
                $ocell =  $orow->nextCell();
                $ocell->createTextRun($value)
                    ->getFont()
                    ->setSize(10)
                    ->setBold(false)
                    ->setName('Trebuchet MS')
                    ->setColor(new Color('FF000000'));

                if ($cell_count == 0) {
                    $ocell->getActiveParagraph()
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setMarginLeft(2);
                } else {
                    $ocell->getActiveParagraph()
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }

                //format
                if ($row_count == 7) {
                    $ocell->getBorders()->getBottom()->setLineWidth(0);
                }

                if ($row_count == 12) {
                    $ocell->getBorders()->getTop()->setLineWidth(0);
                }

                if ($row_count > 7 && $row_count < 12) {
                    $ocell->getBorders()->getTop()->setLineWidth(0);
                    $ocell->getBorders()->getBottom()->setLineWidth(0);
                }

                $cell_count++;
            }
        }
        //-- End Remaining Bookvalues

        //-- Begin Migration Cost 
        //Add row tilte and format
        $row = $tableShape->createRow();
        $row->setHeight(8);
        $row->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FF0080FF'))
            ->setEndColor(new Color('FF0080FF'));

        $title_cell_1 = $row->nextCell();

        $title_cell_1->createTextRun('Migration Cost variables')
            ->getFont()
            ->setSize(11)
            ->setBold(true)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $title_cell_1->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setMarginLeft(2);

        $title_cell_1->setColSpan(4);

        //table data
        for ($row_count = 13; $row_count < 16; $row_count++) {
            $orow = $tableShape->createRow();
            $orow->setHeight(8);
            $orow->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFFFFFFF'))
                ->setEndColor(new Color('FFFFFFFF'));

            $cell_count = 0;
            foreach ($table_data[$row_count] as $key => $value) {
                $ocell =  $orow->nextCell();
                $ocell->createTextRun($value)
                    ->getFont()
                    ->setSize(10)
                    ->setBold(false)
                    ->setName('Trebuchet MS')
                    ->setColor(new Color('FF000000'));

                if ($cell_count == 0) {
                    $ocell->getActiveParagraph()
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setMarginLeft(2);
                } else {
                    $ocell->getActiveParagraph()
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }

                //format
                if ($row_count == 13) {
                    $ocell->getBorders()->getBottom()->setLineWidth(0);
                }

                if ($row_count == 15) {
                    $ocell->getBorders()->getTop()->setLineWidth(0);
                }

                if ($row_count > 13 && $row_count < 15) {
                    $ocell->getBorders()->getTop()->setLineWidth(0);
                    $ocell->getBorders()->getBottom()->setLineWidth(0);
                }

                $cell_count++;
            }
        }
        //-- End Migration Cost

        //-- Begin Microsoft migration support program
        //Add row tilte and format
        $row = $tableShape->createRow();
        $row->setHeight(8);
        $row->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FF0080FF'))
            ->setEndColor(new Color('FF0080FF'));

        $title_cell_1 = $row->nextCell();

        $title_cell_1->createTextRun('Microsoft migration support program')
            ->getFont()
            ->setSize(11)
            ->setBold(true)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $title_cell_1->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setMarginLeft(2);

        $title_cell_1->setColSpan(4);

        //table data
        for ($row_count = 16; $row_count < 22; $row_count++) {
            $orow = $tableShape->createRow();
            $orow->setHeight(8);
            $orow->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFFFFFFF'))
                ->setEndColor(new Color('FFFFFFFF'));

            $cell_count = 0;
            foreach ($table_data[$row_count] as $key => $value) {
                $ocell =  $orow->nextCell();
                $ocell->createTextRun($value)
                    ->getFont()
                    ->setSize(10)
                    ->setBold(false)
                    ->setName('Trebuchet MS')
                    ->setColor(new Color('FF000000'));

                if ($cell_count == 0) {
                    $ocell->getActiveParagraph()
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setMarginLeft(2);
                } else {
                    $ocell->getActiveParagraph()
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }

                //format
                if ($row_count == 17 || $row_count == 19) {
                    $ocell->getBorders()->getBottom()->setLineWidth(0);
                }

                if ($row_count == 18 || $row_count == 20) {
                    $ocell->getBorders()->getTop()->setLineWidth(0);
                }

                $cell_count++;
            }
        }
        //-- End Microsoft migration support program 

        return $tableShape;
    }

    //Create Project Over
    function createTable_ProjectOver_slide34($tableShape, $table_data)
    {
        //-- Begin Project Over
        //Add row tilte and format
        $row = $tableShape->createRow();
        $row->setHeight(20);
        $row->getFill()->setFillType(Fill::FILL_SOLID)
            ->setRotation(90)
            ->setStartColor(new Color('FF0080FF'))
            ->setEndColor(new Color('FF0080FF'));

        $title_cell_1 = $row->nextCell();
        $title_cell_1->setWidth(350);

        $title_cell_1->createTextRun('Projection over 48 months')
            ->getFont()
            ->setSize(12)
            ->setBold(true)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $title_cell_1->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $title_cell_2 = $row->nextCell();
        $title_cell_2->setWidth(70);

        $title_cell_2->createTextRun(' ')
            ->getFont()
            ->setSize(12)
            ->setBold(true)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $title_cell_3 = $row->nextCell();
        $title_cell_3->setWidth(100);

        $title_cell_3->createTextRun('Scenario 1')
            ->getFont()
            ->setSize(12)
            ->setBold(true)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $title_cell_3->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $title_cell_4 = $row->nextCell();
        $title_cell_4->setWidth(100);

        $title_cell_4->createTextRun('Scenario 2')
            ->getFont()
            ->setSize(12)
            ->setBold(true)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $title_cell_4->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $title_cell_5 = $row->nextCell();
        $title_cell_5->setWidth(100);

        $title_cell_5->createTextRun('Scenario 3')
            ->getFont()
            ->setSize(12)
            ->setBold(true)
            ->setName('Trebuchet MS')
            ->setColor(new Color('FFFFFFFF'));

        $title_cell_5->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        //table data
        for ($row_count = 0; $row_count < count($table_data); $row_count++) {
            $orow = $tableShape->createRow();
            $orow->setHeight(15);
            $orow->getFill()->setFillType(Fill::FILL_SOLID)
                ->setRotation(90)
                ->setStartColor(new Color('FFFFFFFF'))
                ->setEndColor(new Color('FFFFFFFF'));

            $cell_count = 0;
            foreach ($table_data[$row_count] as $key => $value) {
                $ocell =  $orow->nextCell();
                $ocell->createTextRun($value)
                    ->getFont()
                    ->setSize(11)
                    ->setBold(false)
                    ->setName('Trebuchet MS')
                    ->setColor(new Color('FF000000'));

                if ($cell_count == 0) {
                    $ocell->getActiveParagraph()
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setMarginLeft(2);
                }

                if ($cell_count == 1) {
                    $ocell->getActiveParagraph()
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }

                if ($cell_count > 1) {
                    $ocell->getActiveParagraph()
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }

                //format
                if ($row_count == 0 && $cell_count == 0) {
                    $ocell->getBorders()->getBottom()->setLineWidth(0);
                }

                if ($row_count == 4 && $cell_count == 0) {
                    $ocell->getBorders()->getTop()->setLineWidth(0);
                }

                if ($row_count > 0 && $row_count < 4 && $cell_count == 0) {
                    $ocell->getBorders()->getTop()->setLineWidth(0);
                    $ocell->getBorders()->getBottom()->setLineWidth(0);
                }

                $cell_count++;
            }
        }
        //-- End Migration Inputs
    }

    function generateSlide($slide_configs = null, $ppt_images = null, $ppt_shape = null, $ppt_text = null, $ppt_text_data = null, $ppt_table_data = null, $ppt_template = null, $ppt_currency = null, $ppt_exchange_currency = null, $ppt_output = null)
    {


        $export_resource_path = __DIR__ . '/../../public/exports/resources/CloudLab/';

        // Set writers
        $writers = array('PowerPoint2007' => 'pptx');
        $objPHPPresentation = new PhpPresentation();
        $objPHPPresentation->getLayout()->setDocumentLayout(DocumentLayout::LAYOUT_SCREEN_16X9);
        $slide_count = 1;

        // dd($slide_configs);
        // $tmpArr=[];
        
        // $slide_configsAt = [
        //     $slide_configs[1],
        //     $slide_configs[2],
        //     $slide_configs[3],
        //     $slide_configs[0],
        //     $slide_configs[4],
        // ];

        // echo "<pre>";
        // print_r($ppt_shape);
        // echo "</pre>";
        // exit;

        foreach ($slide_configs as $slide_item) {

            // Create slide
            // if (!$this->isChartImage($ppt_images, $slide_item)) {
            //     continue;
            // }
            // \Log::info($slide_item->id);
            if ($slide_count == 1) // set active
            {
                $slide = $objPHPPresentation->getActiveSlide();
                // \Log::info("A");    
            } else {
                $slide = $objPHPPresentation->createSlide();
                // \Log::info("B");    
            }
            $slide_count++;
            //set top logo [DISABLE 04DEC2018]
            // if($slide_item->top_logo != null && $slide_item->top_logo != ''){
            //     $top_logo = $this->createImageObject($export_resource_path, $slide_item->top_logo, 39, 50.88, 880, 22, 'FALSE'); 
            //     $slide->addShape($top_logo);
            // }

            if ($slide_item->header_line == 'TRUE') {
                $headerLineShape = $slide->createLineShape(0, 51, 800, 51)->getBorder()->setColor(new Color('FFE46C0A'))->setLineWidth(2);
            }

            //set background image
            if ($slide_item->background_image != "" && $slide_item->background_image != null) {
                $oBkgImage = new Image();
                $oBkgImage->setPath($export_resource_path . $slide_item->background_image);
                $slide->setBackground($oBkgImage);
            }

            //set background color
            if ($slide_item->background_color != "" && $slide_item->background_color != null) {
                $oColor = new Color();
                $oColor->setRGB($slide_item->background_color);
                $oBkgColor = new StyleColor();
                $oBkgColor->setColor($oColor);
                $slide->setBackground($oBkgColor);
            }

            //set title
            if ($slide_item->title != "" || $slide_item->title != null) {
                // Add tile
                $titleShape = $slide->createRichTextShape();
                $titleShape->setHeight(50)
                    ->setWidth(strlen($slide_item->title) * 18)
                    ->setOffsetX(24)
                    ->setOffsetY(16.32);

                $title = $titleShape->createTextRun($slide_item->title);
                //$title->getFont()->setBold(true);
                $title->getFont()->setSize(25);
                $title->getFont()->setName('Gotham Medium');
            }

            //set shape
            foreach ($ppt_shape as $item) {
                if ($item->slide_number == $slide_item->slide_number) {
                    if ($item->shape_type == 'TextShape') {
                        $shape = $slide->createRichTextShape();
                        $shape = $this->createShape(
                            $shape,
                            $item->shape_type,
                            $item->width,
                            $item->height,
                            $item->offsetX,
                            $item->offsetY,
                            $item->background_color,
                            $item->border_color,
                            $item->rotation
                        );
                    }

                    if ($item->shape_type == 'LineShape') {
                        $shape = $slide->createLineShape($item->offsetX, $item->offsetY, $item->offsetX_end, $item->offsetY_end)
                            ->getBorder()
                            ->setColor(new Color($item->border_color))
                            ->setLineWidth($item->line_width);
                    }

                    if ($item->shape_type == 'TableShape') {
                        // if($item->table_name!='StateOfTheCurrentInfrastructure' && $item->table_name != 'CurrentProcessors')
                        // {
                        $shape = $slide->createTableShape($item->maximum_row);
                        $shape = $this->createTable(
                            $shape,
                            $item->slide_number,
                            $item->table_name,
                            $item->width,
                            $item->height,
                            $item->offsetX,
                            $item->offsetY,
                            $ppt_table_data,
                            $ppt_currency,
                            $ppt_exchange_currency
                        );
                        // }

                    }
                }
            }

            //set images
            foreach ($ppt_images as $item) {
                if ($item->slide_number == $slide_item->slide_number && $item->image_source != null) {
                    $chartObject = $this->createImageObject($export_resource_path, $item->image_source, $item->height, $item->width, $item->offsetX, $item->offsetY, $item->base64);
                    $slide->addShape($chartObject);
                }
            }

            //set text
            foreach ($ppt_text as $item) {
                if ($item->slide_number == $slide_item->slide_number) {

                    if ($item->id == '131' || $item->id == '239') {
                        $item->text = str_replace('59%', $ppt_text_data['comparision']['general_purpose'], $item->text);
                    }

                    if ($item->id == '132' || $item->id == '240') {
                        $item->text = str_replace('41%', $ppt_text_data['comparision']['memory_optimized_compute'], $item->text);
                    }

                    if ($item->id == '133' || $item->id == '241') {
                        $item->text = str_replace('10', $ppt_text_data['comparision']['vm_in_scope'], $item->text);
                    }

                    if ($item->id == '134' || $item->id == '242') {
                        $temp = "";
                        $count = 0;
                        if (isset($ppt_text_data['comparision']['GP_allocation'])) {
                            foreach ($ppt_text_data['comparision']['GP_allocation'] as $key => $value) {
                                //count space for format
                                $count_space = 26 - strlen($key) - strlen($value) - (strlen($key) - 8);
                                for ($k = 0; $k < $count_space; $k++)
                                    $key = $key . ' ';

                                if (count($ppt_text_data['comparision']['GP_allocation']) == 1)
                                    $temp = $temp . $key . $value;
                                else {
                                    if ($count == 0)
                                        $temp = $key . ' ' . $value . "\r\n";

                                    if ($count == count($ppt_text_data['comparision']['GP_allocation']) - 1)
                                        $temp = $temp . $key . ' ' . $value;

                                    if ($count > 0 && $count < count($ppt_text_data['comparision']['GP_allocation']) - 1)
                                        $temp = $temp . $key . $value . "\r\n";
                                }
                                $count++;
                            }
                            $item->text = $temp;
                        }
                    }

                    if ($item->id == '135' || $item->id == '243') {
                        $temp = "";
                        $count = 0;
                        if (isset($ppt_text_data['comparision']['MO_allocation'])) {
                            foreach ($ppt_text_data['comparision']['MO_allocation'] as $key => $value) {
                                //count space for format
                                $count_space = 26 - strlen($key) - strlen($value) - (strlen($key) - 8);
                                for ($k = 0; $k < $count_space; $k++)
                                    $key = $key . ' ';

                                if (count($ppt_text_data['comparision']['MO_allocation']) == 1)
                                    $temp = $temp . $key . $value;
                                else {
                                    if ($count == 0)
                                        $temp = $key . ' ' . $value . "\r\n";

                                    if ($count == count($ppt_text_data['comparision']['MO_allocation']) - 1)
                                        $temp = $temp . $key . ' ' . $value;

                                    if ($count > 0 && $count < count($ppt_text_data['comparision']['MO_allocation']) - 1)
                                        $temp = $temp . $key . $value . "\r\n";
                                }
                                $count++;
                            }
                            $item->text = $temp;
                        }
                    }

                    if ($item->id == '136' || $item->id == '244') {
                        $item->text = str_replace('10', $ppt_text_data['comparision']['site_recovery'], $item->text);
                    }

                    $shape = $slide->createRichTextShape();
                    $shape = $this->createText(
                        $shape,
                        $item->width,
                        $item->height,
                        $item->offsetX,
                        $item->offsetY,
                        $item->text,
                        $item->size,
                        $item->text_color,
                        $item->is_bold,
                        $item->font,
                        $item->rotation,
                        $item->setHorizontal,
                        $item->setVertical,
                        $item->lineSpacing
                    );
                }
            }

            // \Log::info($slide_count);
            // if(!$this->isChartImage($ppt_images, $slide_item)){
            //     $index=$objPHPPresentation->getActiveSlideIndex();
            //     \Log::info("index ".$index);

            //     $objPHPPresentation->removeSlideByIndex($index);
            // }
        }

        //Export
        // Save file
        $this->write($objPHPPresentation, $ppt_output, $writers);
    }

    function write($phpPresentation, $filename, $writers)
    {
        // Write documents
        foreach ($writers as $writer => $extension) {
            if (!is_null($extension)) {
                header('Content-type: application/vnd.openxmlformats-officedocument.presentationml.presentation'); //exit;
                header('Content-Disposition: attachment;filename="' . $filename . '"');
                //ob_end_clean();
                $objWriter = IOFactory::createWriter($phpPresentation, $writer);
                $objWriter->save('php://output');
                exit;
            }
        }
        return $result;
    }

    function isChartImage($ppt_images, $slide_item)
    {
        $isImage = false;
        foreach ($ppt_images as $item) {
            if ($item->slide_number == $slide_item->slide_number && $item->image_source != null) {
                $isImage = true;
            }
        }
        return $isImage;
    }
}
