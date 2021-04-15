<?php

namespace App\Services\CsvImport;

use App\Services\ServiceBase;
use App\Exhibition;
use App\Exhibitor;
use App\Prefecture;

class ExhibitorCsvImportService extends ServiceBase
{

    /*
		csvデータを抜き取りとDBに登録処理
	*/
    public function expoJoinExhibitorCsvImport($csv_path)
    {
        // ファイルの存在確認
        if ((fopen($csv_path, "r")) === FALSE)  return false;
        // ファイルの拡張子がcsvもしくはxlsxかを確認
        if (!preg_match("/.csv/", $csv_path)) return false;

        $fp = new \SplFileObject($csv_path);
        $fp->setFlags(
            \SplFileObject::READ_CSV |
                \SplFileObject::READ_AHEAD |
                \SplFileObject::SKIP_EMPTY |
                \SplFileObject::DROP_NEW_LINE
        );

        $i = 0;
        $body = [];
        foreach ($fp as $line) {
            // 空行削除
            if (empty($line[0])) continue;

            if ($i === 0) {
                $headers = $line;
                $i++;
                continue;
            }
            $body[] = $line;
        }
        /*
		foreach( $body as $columns ) {
			foreach( $columns as $key => $column ) {
				$Exhibitor = new Exhibitor();

				$headr_name = $headers[$key];

				//if ( $headr_name === '展示会名')
			}
		}
*/
        logger($headers);
        logger($body);
    }
}
