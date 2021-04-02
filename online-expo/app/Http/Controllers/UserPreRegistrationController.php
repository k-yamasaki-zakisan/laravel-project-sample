<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\HttpCommonLib;

// validation
use App\Http\Requests\UserPreRegistrationRequest;
// Hash
use Illuminate\Support\Facades\Hash;
// Model
use App\Exhibition;
use App\Prefecture;
// DB(トランザクションの為)
use DB;

class UserPreRegistrationController extends PublicExpositionBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //public function index()
    //{
    //}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $slug = HttpCommonLib::GetSlug();

        $objExposition = HttpCommonLib::GetExposition();
        // URLスラッグに指定された該当のEXPOデータがない
        if ($objExposition == null) {
            abort('404');
        }

        // 事前登録受付可能かを確認
        if ($objExposition->can_pre_registration_flag == false) {
            return view('user_pre_registration.can_not_pre_registration');
        }

        // 展示会情報取得
        $exhibitions = Exhibition::where('exposition_id', $objExposition->id)->get();
        // 都道府県の取得
        $prefectures = Prefecture::orderBy('id', 'asc')->get();

        $exhibitions = $exhibitions->toArray();
        $prefectures = $prefectures->toArray();

        return view('user_pre_registration.user_pre_registration', compact(
            'slug',
            'exhibitions',
            'prefectures'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserPreRegistrationRequest $request)
    {

        // FormRequestで取得データを検証
        $validated = $request->validated();
        //  スラッグの取得
        $slug = HttpCommonLib::GetSlug();

        // 登録処理開始
        DB::beginTransaction();

        try {
            // インスタンスの生成
            $user_pre_registrations = new \App\UserPreRegistration();
            $user_pre_registrations->fill($validated);
            if (empty($user_pre_registrations->save())) throw new \RunTimeException("Failed to save user_pre_registations_data.");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;

            return redirect()->route('user_pre_registration.create', $slug);
        }

        return redirect()->route('user_pre_registration.complete', $slug);
    }

    public function complete(Request $request)
    {
        //  スラッグの取得
        $slug = HttpCommonLib::GetSlug();
        return view('user_pre_registration.complete', compact('slug'));
    }
}
