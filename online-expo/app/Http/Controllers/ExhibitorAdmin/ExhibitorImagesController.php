<?php

namespace App\Http\Controllers\ExhibitorAdmin;

use App\Http\Controllers\Controller;

// Request
use App\Http\Requests\ExhibitorAdmin\ExhibitorImages\StoreExhibitorImagesRequest;
use App\Http\Requests\ExhibitorAdmin\ExhibitorImages\UpdateExhibitorImagesSortRequest;
use Illuminate\Http\Request;
// Model
use App\ExhibitorImage;
// DB
use DB;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

use App\Http\HttpCommonLib;

class ExhibitorImagesController extends Controller
{
    public function store(StoreExhibitorImagesRequest $request, ExhibitorImage $ExhibitorImage)
    {
        // 出展社レコードの取得
        $objExhibitor = $this->_GetExhibitor();

        // ファイルパスとファイル名の作成
        $image_dir_path = 'Exhibitor/' . $objExhibitor->id . '/images';
        $image_name = Str::random(32) . '.' . $request->file('exhibitor_image')->getClientOriginalExtension();;

        $ExhibitorImage->exhibitor_id = $objExhibitor->id;
        $ExhibitorImage->image_path = $image_dir_path . '/' . $image_name;

        DB::beginTransaction();

        try {
            // sort_indexの最大値+1を取得
            $ColExhibitorImages = DB::table('exhibitor_images')->where('exhibitor_id', $objExhibitor->id)->lockForUpdate()->get();
            $sort_index = collect($ColExhibitorImages->toArray())->max('sort_index') + 1;
            $ExhibitorImage->sort_index = $sort_index;

            if (empty($ExhibitorImage->save())) throw new \RunTimeException("Failed to save ExhibitorImage.");

            //画像の登録
            $this->imagePut($image_dir_path, $image_name, $request);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            $response = [
                'data'    => [],
                'status'  => 400,
                'summary' => 'Failed Save Exhibitor Image',
                'errors'  => ['message' => ['画像の登録に失敗しました']]
            ];

            throw new HttpResponseException(
                response()->json($response, 400)
            );
        }

        return response()->json([
            'exhibitor_image' => $ExhibitorImage->toArray()
        ]);
    }

    public function destroy($slug, $id)
    {
        // 出展社レコードの取得
        $objExhibitor = $this->_GetExhibitor();

        $slug = $this->_GetSlug();

        DB::beginTransaction();

        try {
            $ExhibitorImage = ExhibitorImage::findOrFail($id);

            if ($ExhibitorImage->exhibitor_id !== $objExhibitor->id) throw new \RunTimeException("Expositor do not connected to ExhibitorImage");

            if (empty($ExhibitorImage->delete())) throw new \RunTimeException("ExhibitorImage = {$id} Failed to delete ExhibitorImage.");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            $response = [
                'data'    => [],
                'status'  => 400,
                'summary' => 'Failed Save',
                'errors'  => ['message' => ['画像の削除に失敗しました']]
            ];

            throw new HttpResponseException(
                response()->json($response, 400)
            );
        }

        return response()->json([
            'message' => '削除が完了しました'
        ]);
    }

    public function updateSort(UpdateExhibitorImagesSortRequest $request)
    {
        $slug = HttpCommonLib::GetSlug();
        $objExhibitor = $this->_GetExhibitor();

        $exhibitor_image_ids = array_values($request->validated());

        $ColExhibitorImages = ExhibitorImage::whereIn('id', $exhibitor_image_ids)->get()->keyBy('id');

        $sort_index = 1;

        DB::beginTransaction();

        try {
            foreach ($exhibitor_image_ids as $exhibitor_image_id) {
                $ExhibitorImage = $ColExhibitorImages[$exhibitor_image_id];

                $ExhibitorImage->sort_index = $sort_index;

                if ($objExhibitor->id !== $ExhibitorImage->exhibitor_id) throw new \RunTimeException("This Exhibitor->id is not same ExhibitorImage->exhibitor_id");

                if (empty($ExhibitorImage->update())) throw new \RunTimeException("Failed to update ExhibitorImage sort_index.");

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

    private function imagePut($image_dir_path, $image_name, $request)
    {
        $Disk = Storage::disk('public');

        // 格納先がない場合は作成
        if (!$Disk->exists($image_dir_path)) {
            $Disk->makeDirectory($image_dir_path, 0775, true);
        }

        $Disk->putFileAs($image_dir_path, $request->file('exhibitor_image'), $image_name);
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
