<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;

// モデル
use App\Exhibition;
// Request
use App\Http\Requests\Superadmin\Exhibitions\StoreExhibitionsRequest;
use App\Http\Requests\Superadmin\Exhibitions\UpdateExhibitionsRequest;
use App\Http\Requests\Superadmin\Exhibitions\UpdateExhibitionsSortRequest;
use Illuminate\Http\Request;
// Services
use App\Services\Superadmin\SuperadminExhibitionsService;
// Traits
use \App\Http\Controllers\Superadmin\Licenses\SelectExpotionTrait;

use DB;
use Illuminate\Support\Facades\Redirect;

class ExhibitionsController extends SuperadminBaseController
{
    use SelectExpotionTrait;

    protected $SuperadminExhibitionsService;

    public function __construct(
        SuperadminExhibitionsService $SuperadminExhibitionsService
    ) {
        $this->SuperadminExhibitionsService = $SuperadminExhibitionsService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $expo_id = $this->getSelectExpotionId($request);

        $Exhibitions = Exhibition::where('exposition_id', $expo_id)
            ->with(['exhibition_zones' => function ($query) {
                $query->orderBy('sort_index');
            }])
            ->orderBy('sort_index')
            ->get();

        return view('superadmin.exhibitions.index', [
            'exhibitions' => $Exhibitions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $expo_id = $this->getSelectExpotionId($request);

        return view('superadmin.exhibitions.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreExhibitionsRequest $request, Exhibition $Exhibition)
    {
        $expo_id = $this->getSelectExpotionId($request);

        $validated = $request->validated();

        $Exhibition->fill($validated);

        // exposition_idのsort_index+1を計算
        $sort_index = Exhibition::where('exposition_id', $expo_id)->max('sort_index') + 1;

        $Exhibition->exposition_id = $expo_id;
        $Exhibition->sort_index = $sort_index;

        DB::beginTransaction();

        try {
            if (empty($Exhibition->save())) throw new \RunTimeException("Failed to save Exhibitions.");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return redirect()->route('superadmin.exhibitions.create')->with('flash_message', '登録に失敗しました');
        }

        return redirect()->route('superadmin.exhibitions.index')->with('flash_message', '登録が完了しました');
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
    public function edit(Request $request, $id)
    {
        $expo_id = $this->getSelectExpotionId($request);
        $Exhibition = Exhibition::findOrFail($id);

        //selectされているexpoに所属していない展示会の場合
        if ($Exhibition->exposition_id !== $expo_id) return redirect()->route('superadmin.expositions.index');

        return view('superadmin.exhibitions.edit', ['exhibition' => $Exhibition]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateExhibitionsRequest $request, $id)
    {
        $expo_id = $this->getSelectExpotionId($request);

        $validated = $request->validated();
        $Exhibition = Exhibition::findOrFail($id);

        //selectされているexpoに所属していない展示会の場合
        if ($Exhibition->exposition_id !== $expo_id) return redirect()->route('superadmin.expositions.index');

        $Exhibition->fill($validated);

        DB::beginTransaction();

        try {
            if (empty($Exhibition->update())) throw new \RunTimeException("Failed to update Exhibition.");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return redirect()->route('superadmin.exhibitions.edit', $id)->with('flash_message', '更新に失敗しました');
        }

        return redirect()->route('superadmin.exhibitions.index')->with('flash_message', '更新が完了しました');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $expo_id = $this->getSelectExpotionId($request);

        $Exhibition = Exhibition::findOrFail($id);

        //selectされているexpoに所属していない展示会の場合
        if ($Exhibition->exposition_id !== $expo_id) return redirect()->route('superadmin.expositions.index');

        DB::beginTransaction();
        try {
            if (empty($Exhibition->delete())) throw new \RunTimeException("Exhibition = {$id} Failed to delete Exhibition.");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return redirect()->route('superadmin.exhibitions.index')->with('flash_message', '削除に失敗しました');
        }

        return redirect()->route('superadmin.exhibitions.index')->with('flash_message', '削除が完了しました');
    }

    public function updateSort(UpdateExhibitionsSortRequest $request)
    {
        $expo_id = $this->getSelectExpotionId($request);

        $validated = $request->validated();

        try {
            $this->SuperadminExhibitionsService->sortUpdate($validated['sort_indexs'], $expo_id);
        } catch (\Exception $e) {
            return redirect()->route('superadmin.exhibitions.index')->with('flash_message', 'ソート順変更に失敗しました');
        }

        return redirect()->route('superadmin.exhibitions.index')->with('flash_message', 'ソート順変更が完了しました');
    }
}
