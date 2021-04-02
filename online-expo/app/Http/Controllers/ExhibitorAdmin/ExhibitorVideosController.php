<?php

namespace App\Http\Controllers\ExhibitorAdmin;

use App\Http\Controllers\Controller;

// Request
use App\Http\Requests\ExhibitorAdmin\ExhibitorVideos\StoreExhibitorVideosRequest;
use App\Http\Requests\ExhibitorAdmin\ExhibitorVideos\UpdateExhibitorVideosSortRequest;
use Illuminate\Http\Request;
// Model
use App\ExhibitorVideo;
// DB
use DB;

use Illuminate\Http\Exceptions\HttpResponseException;

use App\Http\HttpCommonLib;

class ExhibitorVideosController extends ExhibitorAdminBaseController
{
    public function store(StoreExhibitorVideosRequest $request, ExhibitorVideo $ExhibitorVideo)
    {
        $ExhibitorVideo->fill($request->validated());

        // 出展社レコードの取得
        $objExhibitor = $this->_GetExhibitor();

        // sort_indexの最大値+1を取得
        $sort_index = ExhibitorVideo::where('exhibitor_id', $objExhibitor->id)->max('sort_index') + 1;

        $ExhibitorVideo->exhibitor_id = $objExhibitor->id;
        $ExhibitorVideo->sort_index = $sort_index;

        DB::beginTransaction();

        try {
            if (empty($ExhibitorVideo->save())) throw new \RunTimeException("Failed to save ExhibitorVideo.");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            $response = [
                'data'    => [],
                'status'  => 400,
                'summary' => 'Failed Save',
                'errors'  => ['message' => ['動画の登録に失敗しました']]
            ];

            throw new HttpResponseException(
                response()->json($response, 400)
            );
        }

        // スラッグの取得
        //$slug = $this->_GetSlug();

        return response()->json([
            'exhibitor_video' => $ExhibitorVideo->toArray()
        ]);
        //return redirect()->route('exhibitor_admin.exhibitors.edit', [$slug])->with('flash_message', '登録が完了しました。');
    }

    public function destroy($slug, $id)
    {
        // 出展社レコードの取得
        $objExhibitor = $this->_GetExhibitor();

        $slug = $this->_GetSlug();

        DB::beginTransaction();

        try {
            $ExhibitorVideo = ExhibitorVideo::findOrFail($id);

            if ($ExhibitorVideo->exhibitor_id !== $objExhibitor->id) throw new \RunTimeException("Expositor do not connected to ExhibitorVideo");

            if (empty($ExhibitorVideo->delete())) throw new \RunTimeException("ExhibitorVideo = {$id} Failed to delete ExhibitorVideo.");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            $response = [
                'data'    => [],
                'status'  => 400,
                'summary' => 'Failed Save',
                'errors'  => ['message' => ['動画の削除に失敗しました']]
            ];

            throw new HttpResponseException(
                response()->json($response, 400)
            );
        }

        return response()->json([
            'message' => '削除が完了しました'
        ]);
    }

    public function updateSort(UpdateExhibitorVideosSortRequest $request)
    {

        $slug = HttpCommonLib::GetSlug();
        $objExhibitor = $this->_GetExhibitor();

        $exhibitor_video_ids = array_values($request->validated());

        $ColExhibitorVideos = ExhibitorVideo::whereIn('id', $exhibitor_video_ids)->get()->keyBy('id');

        $sort_index = 1;

        DB::beginTransaction();

        try {
            foreach ($exhibitor_video_ids as $exhibitor_video_id) {
                $ExhibitorVideo = $ColExhibitorVideos[$exhibitor_video_id];

                $ExhibitorVideo->sort_index = $sort_index;

                if ($objExhibitor->id !== $ExhibitorVideo->exhibitor_id) throw new \RunTimeException("This ExhibitorVideo->id is not same ExhibitorVideo->exhibitor_id");

                if (empty($ExhibitorVideo->update())) throw new \RunTimeException("Failed to update ExhibitorVideo sort_index.");

                $sort_index += 1;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return redirect()->route('exhibitor_admin.exhibitors.edit', [$slug])->with('flash_message', 'ソート順変更に失敗しました');
        }

        return redirect()->route('exhibitor_admin.exhibitors.edit', [$slug])->with('flash_message', 'ソート順変更が完了しました');
    }

    protected function _GetSlug()
    {
        return HttpCommonLib::GetSlug();
    }

    protected function _GetExhibitor()
    {
        return HttpCommonLib::GetExhibitorBySlugAndLoginUser();
    }
}
