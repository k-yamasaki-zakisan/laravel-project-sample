<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;

// モデル
use App\Exhibitor;
use App\User;
use App\Plan;

// リクエスト
use App\Http\Requests\Superadmin\Exhibitors\StoreExhibitorsRequest;
use App\Http\Requests\Superadmin\Exhibitors\UpdateExhibitorsRequest;
use App\Http\Requests\Superadmin\Exhibitors\SaveExhibitorUsersRequest;
use Illuminate\Http\Request;
// Traits
use \App\Http\Controllers\Superadmin\Licenses\SelectExpotionTrait;

use Illuminate\Support\Facades\Redirect;
use DB;


class ExhibitorsController extends SuperadminBaseController
{
    use SelectExpotionTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $expo_id = $this->getSelectExpotionId($request);

        $exhibitors = Exhibitor::with(['exhibition_zone', 'exhibition', 'prefecture'])->orderBy('name_kana', 'ASC')->get()->toArray();

        // EXPOセレクタ対象のレコードを取得
        foreach ($exhibitors as $exhibitor) {
            if ($exhibitor['exhibition']['exposition_id'] == $expo_id) {
                $new_exhibitor[]    = $exhibitor;
            }
        }

        if (empty($new_exhibitor)) {
            $new_exhibitor = [];
        }

        return view('superadmin.exhibitors.index', ['exhibitors' => $new_exhibitor]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $expo_id = $this->getSelectExpotionId($request);

        $exhibitions = \App\Exhibition::where('exposition_id', $expo_id)->get();

        // 該当の展示会がない場合
        if ($exhibitions->isEmpty()) {
            return redirect()->route('superadmin.exhibitions.index')->with('flash_message', '展示会を登録して下さい');
        }

        foreach ($exhibitions as $exhibition) {
            $exhibition_arr[] = $exhibition['id'];
        }

        $exhibition_zones = \App\ExhibitionZone::whereIn('exhibition_id', $exhibition_arr)->get()->keyBy('id')->toArray();

        $prefectures = \App\Prefecture::get()->toArray();
        $companies = \App\Company::orderBy('name_kana', 'asc')->get()->toArray();
        $plans = Plan::orderBy('id', 'asc')->get()->toArray();

        return view('superadmin.exhibitors.create', [
            'prefectures' => $prefectures,
            'exhibition_zones' => $exhibition_zones,
            'companies' => $companies,
            'exhibitions' => $exhibitions,
            'plans' => $plans
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreExhibitorsRequest $request, Exhibitor $Exhibitor)
    {
        $validated = $request->validated();
        $Exhibitor->fill($validated);

        DB::beginTransaction();

        try {
            if (empty($Exhibitor->save())) throw new \RunTimeException("Failed to store Exhibitor.");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");

            return redirect()->route('superadmin.exhibitors.create')->with('flash_message', '登録に失敗しました');
        }

        return redirect()->route('superadmin.exhibitors.index')->with('flash_message', '登録が完了しました');
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
        if (!empty($request->old('selecte_exposition_id'))) return redirect()->route('superadmin.exhibitors.index')->with('flash_message', '編集画面ではEXPOセレクタを選択しないで下さい');

        $expo_id = $this->getSelectExpotionId($request);
        $exhibitor = \App\Exhibitor::findOrFail($id);

        $exhibitions = \App\Exhibition::where('exposition_id', $expo_id)->get();

        // 該当の展示会がない場合
        if ($exhibitions->isEmpty()) {
            return redirect()->route('superadmin.exhibitions.index')->with('flash_message', '展示会を登録して下さい');
        }

        foreach ($exhibitions as $exhibition) {
            $exhibition_arr[] = $exhibition['id'];
        }

        $exhibition_zones = \App\ExhibitionZone::whereIn('exhibition_id', $exhibition_arr)->get()->toArray();

        $prefectures = \App\Prefecture::get()->toArray();
        $companies = \App\Company::orderBy('name_kana', 'asc')->get()->toArray();
        $plans = Plan::orderBy('id', 'asc')->get()->toArray();

        return view('superadmin.exhibitors.edit', [
            'exhibitor' => $exhibitor,
            'prefectures' => $prefectures,
            'exhibition_zones' => $exhibition_zones,
            'companies' => $companies,
            'exhibitions' => $exhibitions,
            'plans' => $plans
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateExhibitorsRequest $request, $id)
    {
        $expo_id = $this->getSelectExpotionId($request);
        $validated = $request->validated();
        $Exhibitor = \App\Exhibitor::findOrFail($id);

        $Exhibitor->fill($validated);

        DB::beginTransaction();

        try {
            if (empty($Exhibitor->update())) throw new \RunTimeException("Failed to update Exhibitor.");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");

            return redirect()->route('superadmin.exhibitors.edit', $id)->with('flash_message', '更新に失敗しました');
        }

        return redirect()->route('superadmin.exhibitors.index', $id)->with('flash_message', '更新が完了しました');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $Exhibitor = \App\Exhibitor::findOrFail($id);

        DB::beginTransaction();

        try {
            if (empty($Exhibitor->delete())) throw new \RunTimeException("Exhibitor_id = {$id} Failed to delete Exhibitor.");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");

            return redirect()->route('superadmin.exhibitors.index')->with('flash_message', '削除に失敗しました');
        }

        return redirect()->route('superadmin.exhibitors.index')->with('flash_message', '削除が完了しました');
    }

    public function selectExhibitorUsers(Request $request, $exhibitor_id, $search_name = null, $search_email = null)
    {
        $expo_id = $this->getSelectExpotionId($request);

        if (!empty($request->search_name)) $search_name = $request->search_name;
        if (!empty($request->search_email)) $search_email = $request->search_email;

        $query = User::query();
        if (!empty($search_name)) $query->where('name', 'like', "%$search_name%");
        if (!empty($search_email)) $query->where('email', 'like', "%$search_email%");
        if (!empty($search_name) || !empty($search_email)) {
            $users = $query->orderBy('name')->get()->keyBy('id')->toArray();
        } else {
            $users = [];
        }

        $exhibitor = Exhibitor::with('users:id')->findOrFail($exhibitor_id)->toArray();

        // 選択されているユーザーにflagをつける
        foreach ($exhibitor['users'] as $user) {
            if (!empty($users[$user['id']])) $users[$user['id']]['selected'] = true;
        }

        return view('superadmin.exhibitors.select_users', [
            'exhibitor' => $exhibitor,
            'users' => $users,
            'search_name' => $search_name,
            'search_email' => $search_email,
        ]);
    }

    public function saveExhibitorUser(SaveExhibitorUsersRequest $request, $exhibitor_id)
    {
        $user_id = $request->user_id;

        $Exhibitor = Exhibitor::findOrFail($exhibitor_id);

        $Exhibitor->users()->attach($user_id);

        return redirect()->route('superadmin.exhibitors.select_exhibitor_users', [
            'exhibitor_id' => $exhibitor_id,
            'search_name' => $request->search_name,
            'search_email' => $request->search_email,
        ])
            ->with('flash_message', '登録が完了しました');
    }

    public function deleteExhibitorUser(SaveExhibitorUsersRequest $request, $exhibitor_id)
    {
        $user_id = $request->user_id;

        $Exhibitor = Exhibitor::findOrFail($exhibitor_id);

        $Exhibitor->users()->detach($user_id);

        return redirect()->route('superadmin.exhibitors.select_exhibitor_users', [
            'exhibitor_id' => $exhibitor_id,
            'search_name' => $request->search_name,
            'search_email' => $request->search_email,
        ])
            ->with('flash_message', '解除が完了しました');
    }
}
