<?php
namespace App\Helpers;

require_once __DIR__.'/../../vendor/autoload.php';

use PhpOffice\PhpWord\Settings;

class OpenTemplateProcessor extends \PhpOffice\PhpWord\TemplateProcessor
{
    protected $_instance;

    public function __construct($instance) {
		return parent::__construct($instance);
    }

    public function __get($key) {
        return $this->$key;
    }

    public function __set($key, $val) {
        return $this->$key = $val;
    }
}

class ExportWord
{ 
    //function generateWordFile($slide_configs=null, $ppt_images=null, $ppt_shape=null, $ppt_text=null)
    function generateWordFile($word_template, $word_data, $word_image, $word_table=null, $word_output)
    {
        
        $export_resource_path = __DIR__.'/../../public/exports/resources/CloudLab/';
        $templateProcessor = new OpenTemplateProcessor($export_resource_path.'/word/'.$word_template);

        //Add Word Variables
        foreach ($word_data as $key=>$value){
            $templateProcessor->setValue($key, $value);
        }

        //Add Table Data
        if ($word_template == "TEMPLATE_End-Customer_Proposal_CloudLab_AUG18_v21.docx" || $word_template == "TEMPLATE_ViabilityStudy_AUG18_v21.docx")
        {
            //Spread Of GP MO Compute
            $table_spread_of_GP_MO = $word_table['SpreadOfGPMOCompute'];
        
            $templateProcessor->cloneRow('spreadRowValue', count($table_spread_of_GP_MO));

            $count_row = 1;
            foreach ($table_spread_of_GP_MO as $item)
            {   
                $templateProcessor->setValue('spreadRowTitle#'.$count_row, $item['title']);
                $templateProcessor->setValue('spreadRowValue#'.$count_row, $item['value']);
                $count_row++;
            }

            $table_allocation = $word_table['AllocationOfRI'];
            
            $templateProcessor->cloneRow('allRowWeighted', count($table_allocation));

            $count_row = 1;
            foreach ($table_allocation as $item)
            {   
                $templateProcessor->setValue('allRowTitle#'.$count_row, $item['title']);
                $templateProcessor->setValue('allRowWeighted#'.$count_row, $item['weighted']);
                $templateProcessor->setValue('allRowRI1Y#'.$count_row, $item['ri1y']);
                $templateProcessor->setValue('allRowRI3Y#'.$count_row, $item['ri3y']);
                $templateProcessor->setValue('allRowHyb#'.$count_row, $item['hyb']);
                $count_row++;
            }
        }
        
        //Add Chart Data Image 
        $fileindocx = array();
        
        if ($word_template == "TEMPLATE_End-Customer_Proposal_CloudLab_AUG18_v21.docx"){
            $fileindocx['8_0']  =  "word/media/image2.jpg";// 1
            $fileindocx['9_0']  =  "word/media/image3.jpg";// 11
            $fileindocx['11_0'] =  "word/media/image4.jpg";// 14
            $fileindocx['10_2'] =  "word/media/image5.jpg";// 13
            $fileindocx['10_1'] =  "word/media/image6.jpg";// 12
            $fileindocx['13_1'] =  "word/media/image7.jpg";// 19
            $fileindocx['13_2'] =  "word/media/image8.jpg";// 31
            $fileindocx['14_2'] =  "word/media/image9.jpg";// 21
            $fileindocx['18_0'] =  "word/media/image11.jpg";// 15
            $fileindocx['19_1'] =  "word/media/image12.jpg";// 16
            $fileindocx['19_2'] =  "word/media/image13.jpg";// 17
            $fileindocx['21_0'] =  "word/media/image14.jpg";// 26
            $fileindocx['22_2'] =  "word/media/image15.jpg";// 34
            $fileindocx['23_0'] =  "word/media/image16.jpg";// 27
            $fileindocx['26_0'] =  "word/media/image18.jpg";// 35
            $fileindocx['32_1'] =  "word/media/image20.jpg";// 36
            $fileindocx['32_2'] =  "word/media/image21.jpg";// 37
            $fileindocx['32_3'] =  "word/media/image22.jpg";// 38 
            $fileindocx['32_4'] =  "word/media/image23.jpg";// 39
            $fileindocx['32_5'] =  "word/media/image24.jpg";// 40
            $fileindocx['32_6'] =  "word/media/image25.jpg";// 41

            $fileindocx['33_1'] =  "word/media/image26.jpg";// 41
            $fileindocx['33_2'] =  "word/media/image27.jpg";// 42
            $fileindocx['33_3'] =  "word/media/image28.jpg";// 43
        }

        if ($word_template == "TEMPLATE_ViabilityStudy_AUG18_v21.docx"){
            $fileindocx['8_0']  =  "word/media/image4.jpg";// 1
            $fileindocx['9_0']  =  "word/media/image5.jpg";// 11
            $fileindocx['11_0'] =  "word/media/image6.jpg";// 14
            $fileindocx['10_2'] =  "word/media/image7.jpg";// 13
            $fileindocx['10_1'] =  "word/media/image8.jpg";// 12
            $fileindocx['13_1'] =  "word/media/image9.jpg";// 19
            $fileindocx['13_2'] =  "word/media/image10.jpg";// 31
            $fileindocx['14_2'] =  "word/media/image11.jpg";// 21
            $fileindocx['18_0'] =  "word/media/image13.jpg";// 15
            $fileindocx['19_1'] =  "word/media/image14.jpg";// 16
            $fileindocx['19_2'] =  "word/media/image15.jpg";// 17
            $fileindocx['21_0'] =  "word/media/image16.jpg";// 26
            $fileindocx['22_2'] =  "word/media/image17.jpg";// 34
            $fileindocx['23_0'] =  "word/media/image18.jpg";// 27
            $fileindocx['26_0'] =  "word/media/image20.jpg";// 35
            $fileindocx['32_1'] =  "word/media/image22.jpg";// 36
            $fileindocx['32_2'] =  "word/media/image23.jpg";// 37
            $fileindocx['32_3'] =  "word/media/image24.jpg";// 38 
            $fileindocx['32_4'] =  "word/media/image25.jpg";// 39
            $fileindocx['32_5'] =  "word/media/image26.jpg";// 40
            $fileindocx['32_6'] =  "word/media/image27.jpg";// 41

            $fileindocx['33_1'] =  "word/media/image28.jpg";// 41
            $fileindocx['33_2'] =  "word/media/image29.jpg";// 42
            $fileindocx['33_3'] =  "word/media/image30.jpg";// 43
        }
        
        if ($word_template == "TEMPLATE_Bid-Evalaution_AUG18_V1.docx"){
            $fileindocx['18_0'] =  "word/media/image1.jpg";
            $fileindocx['26_0'] =  "word/media/image2.jpg";
        }

        foreach($word_image as $item){ 
            $key_image = $item->slide_number.'_'.$item->locate_number;
            foreach ($fileindocx as $key=>$value){
                if ($key_image == $key)
                {
                    $base64 = gzdecode($item->image_source);
                    $needle = strpos($base64, ',' );

                    if ($needle !== false) {
                        $base64  = substr($base64, $needle + 1);
                        $data    = base64_decode($base64);
                    }
                
                    $templateProcessor->zipClass->AddFromString($value,$data);
                    
                }
            }
        }

        $tempfile = $templateProcessor->save();
        
        //Writer
        $fsize = filesize($tempfile);
        
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename= ".$word_output);
        header("Content-Length: ".$fsize);

        //ob_clean();
        flush();
        readfile($tempfile);
        unlink($tempfile);
    }
}
?>