<?php

namespace App\Services\CsvImport;

use App\Services\ServiceBase;
use App\Exhibition;
use App\ExhibitionZone;
use App\Exhibitor;
use App\Prefecture;
use App\Company;
use App\Plan;

use DB;

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

        $headers = array_flip($headers);

        DB::beginTransaction();

        try {
            foreach ($body as $line) {
                //$Exhibitor = new Exhibitor();

                $zip_code = $line[$headers['顧客郵便番号']];
                $zip_code_array = explode('-', $zip_code);
                $zip_code1 = $zip_code_array[0];
                $zip_code2 = $zip_code_array[1];
                $prefecture_name = $line[$headers['顧客都道府県']];
                $Prefecture = Prefecture::where('name', $prefecture_name)->first();

                // companyの登録
                $company_data = [];
                $company_data['name'] = $line[$headers['顧客名']];
                $company_data['name_kana'] = $line[$headers['顧客カナ']];
                $company_data['zip_code1'] = $zip_code1;
                $company_data['zip_code2'] = $zip_code2;
                $company_data['prefecture_id'] = $Prefecture->id ?? null;
                $company_data['address'] = $line[$headers['顧客市区町村丁目番地']];
                $company_data['building_name'] = $line[$headers['顧客建物']];
                $company_data['url'] = $line[$headers['顧客URL']];
                $company_data['foreign_sync_key'] = $line[$headers['顧客ID※']];
                $company_data['phone_number'] = $line[$headers['顧客TEL']];

                $company_foreign_sync_key = $line[$headers['顧客ID※']];
                $Company = Company::where('foreign_sync_key', $company_foreign_sync_key)->first();
                if (empty($Company)) {
                    $Company = new Company();
                    $Company->fill($company_data);
                    $Company->save();
                } else {
                    $Company->fill($company_data);
                    $Company->update();
                }

                $import_code = $line[$headers['展示会名']];
                $Exhibition = Exhibition::where('import_code', $import_code)->first();
                $zone_name = $line[$headers['ゾーン']];
                $ExhibitionZone = ExhibitionZone::where('exhibition_id', $Exhibition->id)
                    ->where('name', $zone_name)
                    ->first();
                $plan_name = $line[$headers['プラン']];
                $Plan = Plan::where('display_name', 'like', "%$plan_name%")->first();

                // exhibitorの登録
                $exhibitor_data = [];
                $exhibitor_data['exhibition_id'] = $Exhibition->id;
                $exhibitor_data['exhibition_zone_id'] = $ExhibitionZone->id ?? null;
                $exhibitor_data['company_id'] = $Company->id;
                $exhibitor_data['name'] = $line[$headers['顧客名']];
                $exhibitor_data['name_kana'] = $line[$headers['顧客カナ']];
                $exhibitor_data['name_kana_for_sort'] = $line[$headers['顧客カナ']];
                $exhibitor_data['zip_code1'] = $zip_code1;
                $exhibitor_data['zip_code2'] = $zip_code2;
                $exhibitor_data['prefecture_id'] = $Prefecture->id ?? null;
                $exhibitor_data['address'] = $line[$headers['顧客市区町村丁目番地']];
                $exhibitor_data['building_name'] = $line[$headers['顧客建物']];
                $exhibitor_data['tel'] = $line[$headers['顧客TEL']];
                $exhibitor_data['url'] = $line[$headers['顧客URL']];
                $exhibitor_data['foreign_sync_key'] = $line[$headers['出展社ID']];
                $exhibitor_data['plan_id'] = $Plan->id;

                $exhibitor_foreign_sync_key = $line[$headers['出展社ID']];
                $Exhibitor = Exhibitor::where('foreign_sync_key', $exhibitor_foreign_sync_key)->first();
                if (empty($Exhibitor)) {
                    $Exhibitor = new Exhibitor();
                    $Exhibitor->fill($exhibitor_data);
                    $Exhibitor->save();
                } else {
                    $Exhibitor->fill($exhibitor_data);
                    $Exhibitor->update();
                }
                logger($Exhibitor->toArray());
            }
            throw new \RunTimeException("Missing csv file");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return false;
        }

        return true;
    }
}
