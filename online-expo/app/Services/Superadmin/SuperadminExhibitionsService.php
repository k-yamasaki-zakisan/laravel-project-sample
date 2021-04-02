<?php

namespace App\Services\Superadmin;

use App\Services\ServiceBase;

use App\Exhibition;
use App\ExhibitionZone;

use DB;

class SuperadminExhibitionsService extends ServiceBase
{
    /*
		Array $sort_exhibitions
		key is exhibition_id
		values is exhibition_zone_ids

		$expo_id is $expotion_id
	*/
    public function sortUpdate($sort_exhibitions, $expo_id)
    {
        DB::beginTransaction();

        try {
            $this->exhibitionSortUpdate($sort_exhibitions, $expo_id);
            $this->exhibitionZoneSortUpdate($sort_exhibitions, $expo_id);

            DB::commit();
        } catch (\Exception $e) {
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            DB::rollBack();
            throw new \RunTimeException("sort_index update fail");
        }

        return true;
    }

    private function exhibitionSortUpdate($sort_exhibitions, $expo_id)
    {
        $exhibition_ids = array_keys($sort_exhibitions);

        $ColExhibitions = Exhibition::whereIn('id', $exhibition_ids)->get()->keyBy('id');

        $sort_index = 1;

        foreach ($sort_exhibitions as $exhibition_id => $exhibition_zone_ids) {
            $Exhibition = $ColExhibitions[$exhibition_id];

            $Exhibition->sort_index = $sort_index;

            if ($Exhibition->exposition_id !== $expo_id) throw new \RunTimeException("This Exhibition->exhibition_id is not same emposition_id");

            if (empty($Exhibition->update())) throw new \RunTimeException("Failed to update Exhibition sort_index.");

            $sort_index += 1;
        }

        return true;
    }

    private function exhibitionZoneSortUpdate($sort_exhibitions, $expo_id)
    {
        // exhibitionZoneが選択しexpoに所属しているか確認用
        $ColExhibitions = Exhibition::where('exposition_id', $expo_id)->get()->keyBy('id');

        $exhibition_ids = array_keys($sort_exhibitions);

        $ColExhibitionZones = ExhibitionZone::whereIn('exhibition_id', $exhibition_ids)->get()->keyBy('id');

        foreach ($sort_exhibitions as $exhibition_id => $exhibition_zone_ids) {
            $sort_index = 1;
            foreach ($exhibition_zone_ids as $exhibition_zone_id) {
                $ExhibitionZone = $ColExhibitionZones[$exhibition_zone_id];

                $ExhibitionZone->sort_index = $sort_index;

                if (empty($ColExhibitions[$ExhibitionZone->exhibition_id])) throw new \RunTimeException("This ExhibitionZone is not same group selected  expo");

                if (empty($ExhibitionZone->update())) throw new \RunTimeException("Failed to update ExhibitionZone sort_index.");

                $sort_index += 1;
            }
        }

        return true;
    }
}
