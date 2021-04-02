<?php

namespace App\Http\Controllers\ExhibitorAdmin;

use App\Http\Controllers\Controller;

// Request
use App\Http\Requests\ExhibitorAdmin\ProductImages\StoreProductImagesRequest;
use App\Http\Requests\ExhibitorAdmin\ProductImages\UpdateProductImagesSortRequest;
use Illuminate\Http\Request;
// Model
use App\ProductImage;
use App\Product;
// DB
use DB;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

use App\Http\HttpCommonLib;

class ProductImagesController extends Controller
{
    public function store(StoreProductImagesRequest $request, ProductImage $ProductImage, $slug, $product_id)
    {
        // 出展社レコードの取得
        $objExhibitor = $this->_GetExhibitor();

        // ファイルパスとファイル名の作成
        $image_dir_path = 'Product/' . $product_id . '/images';
        $image_name = Str::random(32) . '.' . $request->file('product_image')->getClientOriginalExtension();

        $ProductImage->product_id = $product_id;
        $ProductImage->image_path = $image_dir_path . '/' . $image_name;

        DB::beginTransaction();

        try {
            // sort_indexの最大値+1を取得
            $ColProductImages = DB::table('product_images')->where('product_id', $product_id)->lockForUpdate()->get();
            $sort_index = collect($ColProductImages->toArray())->max('sort_index') + 1;
            $ProductImage->sort_index = $sort_index;

            // Product->exhibitor_idとobjExhibitor->idが一致するか確認
            $Product = Product::findOrFail($product_id);
            if ($Product->exhibitor_id !== $objExhibitor->id) throw new \RunTimeException("Product_id do not connect objExhibitor.");

            if (empty($ProductImage->save())) throw new \RunTimeException("Failed to save ProductImage.");

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
            'product_image' => $ProductImage->toArray()
        ]);
    }

    public function destroy($slug, $product_id, $product_image_id)
    {
        // 出展社レコードの取得
        $objExhibitor = $this->_GetExhibitor();

        $ProductImage = ProductImage::findOrFail($product_image_id);

        DB::beginTransaction();

        try {
            $Product = Product::findOrFail($product_id);

            if ($ProductImage->product_id !== $Product->id) throw new \RunTimeException("ProductImage do not connected to Product");

            if ($Product->exhibitor_id !== $objExhibitor->id) throw new \RunTimeException("Product do not connected to objExhibitor");

            if (empty($ProductImage->delete())) throw new \RunTimeException("ProductImage = {$id} Failed to delete ProductImage.");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            $response = [
                'data'    => [],
                'status'  => 400,
                'summary' => 'Failed delete',
                'errors'  => ['message' => ['画像の削除に失敗しました']]
            ];

            throw new HttpResponseException(
                response()->json($response, 400)
            );
        }

        return response()->json([
            'message' => '画像の削除が完了しました'
        ]);
    }

    public function updateSort(UpdateProductImagesSortRequest $request, $slug, $product_id)
    {
        $slug = $this->_GetSlug();
        $objExhibitor = $this->_GetExhibitor();
        $Product = Product::findOrFail($product_id);

        $product_image_ids = array_values($request->validated());

        $ColProductImages = ProductImage::whereIn('id', $product_image_ids)->get()->keyBy('id');

        $sort_index = 1;

        DB::beginTransaction();

        try {
            if ($objExhibitor->id !== $Product->exhibitor_id) throw new \RunTimeException("This Exhibitor->id is not same Product->exhibitor_id");

            foreach ($product_image_ids as $product_image_id) {
                $ProductImage = $ColProductImages[$product_image_id];

                $ProductImage->sort_index = $sort_index;

                if ($Product->id !== $ProductImage->product_id) throw new \RunTimeException("This Product->id is not same ProductImage->product_id");

                if (empty($ProductImage->update())) throw new \RunTimeException("Failed to update ProductImage sort_index.");

                $sort_index += 1;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return redirect()->route('exhibitor_admin.products.edit', [$slug, $product_id])->with('flash_message', 'ソート順変更に失敗しました');
        }

        return redirect()->route('exhibitor_admin.products.edit', [$slug, $product_id])->with('flash_message', 'ソート順変更が完了しました');
    }

    private function imagePut($image_dir_path, $image_name, $request)
    {
        $Disk = Storage::disk('public');

        // 格納先がない場合は作成
        if (!$Disk->exists($image_dir_path)) {
            $Disk->makeDirectory($image_dir_path, 0775, true);
        }

        $Disk->putFileAs($image_dir_path, $request->file('product_image'), $image_name);
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
