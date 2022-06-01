<?php

namespace chanyu\Tools;

class OfficeTool
{
    protected static $cellName = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ'];

    /**
     * 导出 Excel
     * @param $expTitle string 导出文件的名称
     * @param $expCellName array 导出字段的名称
     * @param $expTableData array 导出的数据
     * @param null $meta 设置第一行的内容
     */
    public static function exportExcel($expTitle, $expCellName, $expTableData, $meta = null)
    {
        $xlsTitle = iconv('utf-8', 'gbk', $expTitle); //字符编码来转换
        $fileName = $expTitle;
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        $objPHPExcel = new \PHPExcel();
        $cellName = self::$cellName;

        // 隐藏excel表格里面的title
        /*$objPHPExcel->getActiveSheet(0)->mergeCells('A1:' . $cellName[$cellNum - 1] . '1');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle);*/

        // 默认表头从第一行开始
        $begin_title_index = 1;
        // 默认数据从第二行开始
        $begin_data_index = 2;

        if ($meta) {
            $formatMeta = '';
            foreach ($meta as $val) {
                $val_value = trim($val['value']);
                if (isset($val['key']) && $val['key']) {
                    $val_key = trim($val['key']);
                    $formatMeta .= $val_key . ': ' . $val_value . "\n";
                } else {
                    $formatMeta .= $val_value . "\n";
                }
            }

            // 设置表头字体是否加粗
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
            // 合并单元格
            $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $cellName[$cellNum - 1] . '1');
            // 填充第一行数据
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $formatMeta);
            // 设置行高
            $objPHPExcel->getActiveSheet()->getRowDimension()->setRowHeight(self::getExcelRowCount($formatMeta));
            // 配合单元格内换行（单元格自动换行属性 1.单元格内容必须包含换行符 2.必须激活单元格换行属性）
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);

            // 表头开始行索引
            $begin_title_index = 2;
            // 数据开始行索引
            $begin_data_index = 3;
        }

        // 设置表头
        for ($i = 0; $i < $cellNum; $i++) {
            // 数据的标题行设置
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . $begin_title_index, $expCellName[$i][1]);
            // 单元格设置自适应宽度
            $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setAutoSize(true);
        }

        // 遍历数据
        for ($i = 0; $i < $dataNum; $i++) {
            for ($j = 0; $j < $cellNum; $j++) {
                // 是否存在数据
                $tableData = isset($expTableData[$i][$expCellName[$j][0]]) ? $expTableData[$i][$expCellName[$j][0]] : '';
                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$j] . ($i + $begin_data_index), $tableData);
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }

    /**
     * 生成 Excel文件
     * @param $expTitle string 导出文件的名称
     * @param $expCellName array 导出字段的名称
     * @param $expTableData array 导出的数据
     * @param null $meta 设置第一行的内容
     * @param string $path 文件保存路径
     * @return string
     */
    public static function createExcel($expTitle, $expCellName, $expTableData, $meta = null, $path = '')
    {
        if (!$path) {
            throw new \Exception('文件路径不能为空！');
        }

        //$expTitle = iconv('utf-8', 'gbk//IGNORE', $expTitle);//文件名称
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        $objPHPExcel = new \PHPExcel();
        $cellName = self::$cellName;

        // 默认表头从第一行开始
        $begin_title_index = 1;
        // 默认数据从第二行开始
        $begin_data_index = 2;

        if ($meta) {
            $formatMeta = '';
            foreach ($meta as $val) {
                $val_value = trim($val['value']);
                if (isset($val['key']) && $val['key']) {
                    $val_key = trim($val['key']);
                    $formatMeta .= $val_key . ': ' . $val_value . "\n";
                } else {
                    $formatMeta .= $val_value . "\n";
                }
            }

            // 设置表头字体是否加粗
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
            // 合并单元格
            $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $cellName[$cellNum - 1] . '1');
            // 填充第一行数据
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $formatMeta);
            // 设置行高
            $objPHPExcel->getActiveSheet()->getRowDimension()->setRowHeight(self::getExcelRowCount($formatMeta));
            // 配合单元格内换行（单元格自动换行属性 1.单元格内容必须包含换行符 2.必须激活单元格换行属性）
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);

            // 表头开始行索引
            $begin_title_index = 2;
            // 数据开始行索引
            $begin_data_index = 3;
        }

        // 设置表头
        for ($i = 0; $i < $cellNum; $i++) {
            // 数据的标题行设置
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . $begin_title_index, $expCellName[$i][1]);
            // 单元格设置自适应宽度
            $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setAutoSize(true);
        }

        // 遍历数据
        for ($i = 0; $i < $dataNum; $i++) {
            for ($j = 0; $j < $cellNum; $j++) {
                // 是否存在数据
                $tableData = isset($expTableData[$i][$expCellName[$j][0]]) ? $expTableData[$i][$expCellName[$j][0]] : '';
                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$j] . ($i + $begin_data_index), $tableData);
            }
        }

        // 保存文件名
        $fileName = $path . $expTitle . '.xls';
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save($fileName);
        return $fileName;
    }


    /**
     * 读取Excel数据
     * @functionName : readExcel
     * @param string $file Excel文件路径
     */
    public static function readExcel($file)
    {
        //vendor("phpoffice.phpexcel");
        $fileType = \PHPExcel_IOFactory::identify($file);
        $PHPReader = \PHPExcel_IOFactory::createReader($fileType);
        $PHPReader->setReadDataOnly(true);              //只读取数据，去除其他格式
        $objPHPExcel = $PHPReader->load($file);         //读取Excel文件
        $currentSheet = $objPHPExcel->getSheet(0);      //获取第一个工作表
        $allColumn = $currentSheet->getHighestColumn(); //Excel所有列数最大值
        $allRow = $currentSheet->getHighestRow();       //Excel总行数
        $content = $currentSheet->toArray();
        return ['row' => $allRow, 'col' => $allColumn, 'data' => $content];
    }


    /**
     * 根据单元格内容获取单元格行高
     * @author yc
     * @param $text
     * @param int $width
     * @return float|int
     */
    public static function getExcelRowCount($text, $width = 55)
    {
        $rc = 0;
        $line = explode("\n", $text);
        foreach ($line as $source) {
            $rc += intval((strlen($source) / $width) + 1);
        }
        return $rc * 12.75 + 2.25;
    }

    /**
     * 文件压缩成zip
     *
     * @author yc
     * @param $zipfilename
     * @param $filenameArr
     * @param string $cacheDir
     * @return array
     */
    public static function createZipByFile($zipfilename, $filenameArr, $cacheDir = 'wordFileTmp')
    {
        if (!empty($filenameArr)) {
            $zip = new \ZipArchive();
            //打开压缩包
            if ($zip->open($zipfilename, \ZipArchive::CREATE) !== true) {
                return [false, '打开压缩文件失败！'];
            }
            //向压缩包中添加文件
            foreach ($filenameArr as $file) {
                //$zip->addFromString($file,file_get_contents($file)); //向压缩包中添加文件
                if (file_exists($file)) {
                    $fileExplode = explode(DIRECTORY_SEPARATOR . $cacheDir . DIRECTORY_SEPARATOR, $file);
                    $localname = $fileExplode[1];
                    $zip->addFile($file, $localname);  //向压缩包中添加文件
                }
            }
            $zip->close();  //关闭压缩包
            foreach ($filenameArr as $file) {
                if (file_exists($file)) {
                    unlink($file); //删除临时文件
                }
            }
            // 输出压缩文件提供下载
            header("Cache-Control: max-age=0");
            header("Content-Description: File Transfer");
            header('Content-disposition: attachment; filename=' . $zipfilename); // 文件名
            header("Content-Type: application/zip"); // zip格式的
            header("Content-Transfer-Encoding: binary"); //
            header('Content-Length: ' . filesize($zipfilename)); //
            ob_clean();
            flush();
            readfile($zipfilename);//输出文件;
            unlink($zipfilename); //删除压缩包临时文件
            die();
        }
        return [false, '未找到本地文件'];
    }
}