<?php
namespace app\index\service;

class Excel{
    public static function excel_data($path){
        $inputFileName = $path;
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($inputFileName);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow(); // e.g. 10
        $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5

        for ($row = 2; $row <= $highestRow; ++$row) {
            for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                $data[$row][] = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
            }
        }
        $data = array_values($data);
        return $data;
    }


    public static function excl($strexport,$title)
    {
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:filename=".$title.".xls");
//        $strexport = "材料编号\t材料名称\t价格\t预算\t已采购\t结余调拨数量\t工程调拨数量\r";
//        foreach ($list as $row) {
//            $strexport .= $row['number'] . "\t";
//            $strexport .= $row['name'] . "\t";
//            $strexport .= $row['need'] . "\t";
//            $strexport .= $row['buy_num'] . "\t";
//            $strexport .= $row['have_num'] . "\t";
//            $strexport .= $row['project_num'] . "\r";
//        }

        $strexport = iconv('UTF-8', "GB2312//IGNORE", $strexport);
        exit($strexport);
    }

} 