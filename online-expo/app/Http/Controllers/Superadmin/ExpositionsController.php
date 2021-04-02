<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;

// モデル
use App\Exposition;

// リクエスト
use App\Http\Requests\Superadmin\Expositions\StoreExpositionsRequest;
use App\Http\Requests\Superadmin\Expositions\UpdateExpositionsRequest;
use Illuminate\Http\Request;

use DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ExpositionsController extends SuperadminBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $expositions = Exposition::orderBy('start_date', 'DESC')->get();
        //dd($exposition->toArray());

        return view('superadmin.expositions.index', ['expositions' => $expositions->toArray()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('superadmin.expositions.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreExpositionsRequest $request, Exposition $Exposition)
    {
        $validated = $request->validated();

        $image_name = $validated['main_visual_path'];

        $Exposition->fill($validated);

        DB::beginTransaction();

        try {
            if (empty($Exposition->save())) throw new \RunTimeException("Failed to save Exposition.");

            // メイン画像の登録
            if (!empty($image_name)) {
                // idを作成してからファイルパスを作成
                $image_path = 'Exhibition/' . $Exposition->id . '/' . $Exposition->main_visual_path;

                // ファイルパスをセット
                $Exposition->main_visual_path = $image_path;

                if (empty($Exposition->save())) throw new \RunTimeException("Failed to Second save Exposition.");

                //画像の登録
                $this->mainVisualPut($Exposition->id, $request, $image_name);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return redirect()->route('superadmin.expositions.create')->with('flash_message', '登録に失敗しました');
        }

        return redirect()->route('superadmin.expositions.index')->with('flash_message', '登録が完了しました');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $exposition = Exposition::findOrFail($id);

        return view('superadmin.expositions.edit', ['exposition' => $exposition->toArray()]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateExpositionsRequest $request, $id)
    {
        $validated = $request->validated();
        $Exposition = Exposition::findOrFail($id);

        $Exposition->fill($validated);

        DB::beginTransaction();

        try {
            if (empty($Exposition->update())) throw new \RunTimeException("Failed to update Exposition.");

            // 画像のリクエストが来ていたら登録
            if (!empty($validated['main_visual_path'])) {
                $image_name = 'main_visual.' . $request->file('exposition_main_visual')->getClientOriginalExtension();

                $this->mainVisualPut($Exposition->id, $request, $image_name);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return redirect()->route('superadmin.expositions.edit', $id)->with('flash_message', '更新に失敗しました');
        }

        return redirect()->route('superadmin.expositions.index')->with('flash_message', '更新が完了しました');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $Exposition = Exposition::findOrFail($id);

        DB::beginTransaction();

        try {
            if (empty($Exposition->delete())) throw new \RunTimeException("Exposition_id = {$id} Failed to delete Exposition.");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return redirect()->route('superadmin.expositions.index')->with('flash_message', '削除に失敗しました');
        }

        return redirect()->route('superadmin.expositions.index')->with('flash_message', '削除が完了しました');
    }

    public function mainvisualDelete($id)
    {
        $Exposition = Exposition::findOrFail($id);
        $Exposition->main_visual_path = null;

        DB::beginTransaction();
        try {
            if (empty($Exposition->update())) throw new \RunTimeException("Exposition_id = {$id} Failed to delete Exposition main_visual.");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return redirect()->route('superadmin.expositions.edit', $id)->with('flash_message', 'EXPOトップ画像の削除に失敗しました');
        }

        return redirect()->route('superadmin.expositions.edit', $id)->with('flash_message', 'EXPOトップ画像の削除が完了しました');
    }

    private function mainVisualPut($id, $request, $image_name)
    {
        $Disk = Storage::disk('public');
        $dirpath = 'Exhibition/' . $id;

        // 格納先がない場合は作成
        if (!$Disk->exists($dirpath)) {
            $Disk->makeDirectory($dirpath, 0775, true);
        }

        $Disk->putFileAs($dirpath, $request->file('exposition_main_visual'), $image_name);
    }
}
