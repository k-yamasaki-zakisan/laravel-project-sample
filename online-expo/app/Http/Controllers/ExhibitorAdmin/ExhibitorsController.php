<?php

namespace App\Http\Controllers\ExhibitorAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
//use Auth;
// validation
use App\Http\Requests\ExhibitorsRequest;
// Model
use App\Exhibitor;
use App\Prefecture;
// DB
use DB;

use App\Http\HttpCommonLib;

class ExhibitorsController extends ExhibitorAdminBaseController
{

    /**
     * 編集ページ
     */
    public function edit(Request $request)
    {
        $this->_checkCanView();

        $objExhibitor = $this->_GetExhibitor();

        // 出展展示会の取得
        $objExhibition = $objExhibitor->exhibition;
        $objExhibitorVideos = $objExhibitor->exhibitor_videos->sortBy('sort_index');
        $objExhibitorImages = $objExhibitor->exhibitor_images->sortBy('sort_index');

        // TODO: 出展社が自分でゾーンを選択する仕様に変更する事。選択方式はプルダウンで実装する。

        return view('exhibitor_admin.exhibitors.edit', [
            'exhibition' => $objExhibition,
            'exhibition_zones' => $objExhibition->exhibition_zones()->pluck('name', 'id'),
            'exhibitor' => $objExhibitor,
            'prefectures' => Prefecture::select('id', 'name')->get()->pluck('name', 'id'),
            'exhibitor_videos' => $objExhibitorVideos->toArray(),
            'exhibitor_images' => $objExhibitorImages->toArray(),
        ]);
    }

    /**
     * 更新
     */
    public function update(ExhibitorsRequest $request)
    {

        $this->_checkCanView();


        // TODO: 画像や動画保存用のロジックを全く考慮していません。開発を進める際に実装してください。

        // 出展社レコードの取得
        $objExhibitor = $this->_GetExhibitor();
        if (!$objExhibitor->fill($request->all())->save()) {
            // TODO: エラー処理を書く
            print "失敗";
            exit();
        }

        // TODO: 他のモデルも保存。そしてトランザクション

        return back()->with('flash_message', '登録が完了しました。');




        // 会社情報の取得
        $exhibitor_info = $request->all();
        // 出展社レコードの取得
        $objExhibitor = $this->_GetExhibitor();

        $exhibitor_data = [
            'zip_code1' => $exhibitor_info['zip_code1'],
            'zip_code2' => $exhibitor_info['zip_code2'],
            'prefecture_id' => $exhibitor_info['prefecture_id'],
            'address' => $exhibitor_info['address'],
            'building_name' => $exhibitor_info['building_name'],
            'tel' => $exhibitor_info['tel'],
            'url' => $exhibitor_info['url'],
            'profile_text' => $exhibitor_info['profile_text']
        ];

        // 登録処理開始
        DB::beginTransaction();

        try {
            $exhibitor_data = \App\Exhibitor::find($objExhibitor['id'])->fill($exhibitor_data);
            if (empty($exhibitor_data->save())) throw new \RunTimeException("Failed to save exhibitor.");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        // 	スラッグの取得
        $slug =    HttpCommonLib::GetSlug();

        return redirect(route('exhibitor_admin.exhibitors.edit', [$slug]))->with('flash_message', '登録が完了しました。');
    }

    //
    protected function _GetExhibitor()
    {
        return HttpCommonLib::GetExhibitorBySlugAndLoginUser();
    }
}
