<?php

namespace App\Http\Controllers\Visitor;

use Illuminate\Http\Request;

// model
use App\Exposition;
use App\Exhibition;
use App\Exhibitor;
use App\SeminarCategory;
use App\SeminarType;
use App\Seminar;
use App\ContactRequestType;

use Illuminate\Support\Facades\Auth;

use App\Http\HttpCommonLib;
// user_log
use App\Services\UserActionLogsService;

use Carbon\Carbon;

class HomeController extends VisitorBaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    /*
	public function __construct()
	{
		parent::__construct();
		$this->middleware('auth');
	}
*/

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request, $expo_slug)
    {
        $this->_checkCanView();

        $Exposition = Exposition::where('slug', $expo_slug)->with([
            'exhibitions' => function ($query) {
                $query->orderBy('sort_index', 'asc');
            },
            'exhibitions.exhibitors',
            'exhibitions.exhibitors.exhibitor_images' => function ($query) {
                $query->orderBy('sort_index', 'asc');
            },
            'exhibitions.exhibition_zones' => function ($query) {
                $query->orderBy('sort_index', 'asc');
            },
            'exhibitions.exhibition_zones.exhibitors',
            'exhibitions.exhibition_zones.exhibitors.exhibitor_images' => function ($query) {
                $query->orderBy('sort_index', 'asc');
            },
        ])->first()->toArray();

        //開催終了日の文字列実装
        $Exposition['end_date'] = Carbon::parse($Exposition['start_date'])->addDays($Exposition['exposition_days'] - 1)->format('Y年m月d日');
        $Exposition['start_date'] = Carbon::parse($Exposition['start_date'])->format('Y年m月d日');

        $Exhibition = Exhibition::where('exposition_id', $Exposition['id'])->get()->keyBy('id');
        $exhibition_ids = $Exhibition->pluck('id');
        $Exhibitors = Exhibitor::whereIn('exhibition_id', $exhibition_ids)->orderBy('name_kana_for_sort')->get();

        $exhibitor_ids = $Exhibitors->pluck('id');
        $exhibitors = $Exhibitors->toArray();

        $SeminarTypes = SeminarType::all()->keyBy('name')->toArray();

        $SeminarCategorys = SeminarCategory::where('exposition_id', $Exposition['id'])
            ->where('seminar_type_id', $SeminarTypes['展示会セミナー']['id'])
            ->where('active_flag', true)
            ->with([
                'seminars' => function ($query) {
                    $query->where('active_flag', true);
                    $query->orderBy('sort_index', 'asc');
                },
            ])
            ->orderBy('sort_index')
            ->get();

        // 展示会セミナーのデータ構造作成
        $ExhibitionWithSeminars = $Exhibition->toArray();
        foreach ($ExhibitionWithSeminars as $key => $value) {
            $ExhibitionWithSeminars[$key]['seminar_categories'] = [];
        }

        foreach ($SeminarCategorys as $seminar_category) {
            $ExhibitionWithSeminars[$seminar_category->exhibition_id]['seminar_categories'][] = $seminar_category->toArray();
        }

        // カテゴリ検索作成
        $exhibitor_classifications = [];
        $exhibitor_classifications['あ'] = [];
        $exhibitor_classifications['か'] = [];
        $exhibitor_classifications['さ'] = [];
        $exhibitor_classifications['た'] = [];
        $exhibitor_classifications['な'] = [];
        $exhibitor_classifications['は'] = [];
        $exhibitor_classifications['ま'] = [];
        $exhibitor_classifications['や'] = [];
        $exhibitor_classifications['ら'] = [];
        $exhibitor_classifications['わ'] = [];
        foreach ($exhibitors as $exhibitor) {
            $head_word = mb_substr($exhibitor['name_kana_for_sort'], 0, 1);

            if (in_array($head_word, ['ア', 'イ', 'ウ', 'エ', 'オ'])) array_push($exhibitor_classifications['あ'], $exhibitor);
            elseif (in_array($head_word, ['カ', 'キ', 'ク', 'ケ', 'コ'])) array_push($exhibitor_classifications['か'], $exhibitor);
            elseif (in_array($head_word, ['サ', 'シ', 'ス', 'セ', 'ソ'])) array_push($exhibitor_classifications['さ'], $exhibitor);
            elseif (in_array($head_word, ['タ', 'チ', 'ツ', 'テ', 'ト'])) array_push($exhibitor_classifications['た'], $exhibitor);
            elseif (in_array($head_word, ['ナ', 'ニ', 'ヌ', 'ネ', 'ノ'])) array_push($exhibitor_classifications['な'], $exhibitor);
            elseif (in_array($head_word, ['ハ', 'ヒ', 'フ', 'ヘ', 'ホ'])) array_push($exhibitor_classifications['は'], $exhibitor);
            elseif (in_array($head_word, ['マ', 'ミ', 'ム', 'メ', 'モ'])) array_push($exhibitor_classifications['ま'], $exhibitor);
            elseif (in_array($head_word, ['ヤ', 'ユ', 'ヨ'])) array_push($exhibitor_classifications['や'], $exhibitor);
            else array_push($exhibitor_classifications['わ'], $exhibitor);
        }

        // 専門セミナーデータ
        $SpecializedSeminarCategories = SeminarCategory::where('exposition_id', $Exposition['id'])
            ->whereNull('exhibition_id')
            ->where('seminar_type_id', $SeminarTypes['専門セミナー']['id'])
            ->where('active_flag', true)
            ->with([
                'seminars' => function ($query) {
                    $query->where('active_flag', true);
                    $query->orderBy('sort_index', 'asc');
                }
            ])
            ->orderBy('sort_index')
            ->get();

        // 出展者アカウントか判定
        $UserExhibitor = Exhibitor::whereHas('users', function ($query) {
            $query->where('users.id', Auth::user()->id);
        })->whereIn('id', $exhibitor_ids)
            ->select('id')
            ->get()->toArray();
        $is_exhibitor_admin = !empty($UserExhibitor);

        return view('visitor.home', [
            'exposition' => $Exposition,
            'exhibitions' => $Exposition['exhibitions'],
            'exhibitors' => $exhibitors,
            'exhibitor_classifications' => $exhibitor_classifications,
            'exhibitions_with_seminars' => $ExhibitionWithSeminars,
            'specialized_seminar_categories' => $SpecializedSeminarCategories->toArray(),
            'is_exhibitor_admin' => $is_exhibitor_admin,
            'is_preview' => $request->mode === 'preview',
        ]);
    }

    public function seminarVideo(
        $expo_slug,
        $seminar_id,
        UserActionLogsService $objUserActionLogsService,
        UserActionLog $UserActionLog
    ) {
        // $seminar_idが整数でなければ404
        if (!is_numeric($seminar_id)) abort(404);

        $this->_checkCanView();

        $Seminar = Seminar::with([
            'seminar_category:id,exposition_id,seminar_type_id'
        ])->findOrFail($seminar_id);

        $SeminarCategory = SeminarCategory::select(['id', 'exposition_id'])
            ->with('exposition:id')
            ->findOrFail($Seminar->seminar_category_id);

        $Exposition = Exposition::select(['id', 'slug'])->where('slug', $expo_slug)->firstOrFail();

        // expositionのidが合致しなければ404
        if ($SeminarCategory->exposition->id !== $Exposition->id) abort(404);

        // ログを登録
        $objUserActionLogsService->storeLog(
            $Exposition->id,
            'セミナーモーダルOPEN',
            [
                'seminar_id' => $Seminar->id,
                'seminar_category_id' => $Seminar->seminar_category_id,
                'seminar_type_id' => $Seminar->seminar_category->seminar_type_id
            ],
            $UserActionLog
        );

        return view('visitor.iframe.video', [
            'slug' => $expo_slug,
            'seminar' => $Seminar->toArray()
        ]);
    }

    public function exhibitorShow(
        $expo_slug,
        $exhibitor_id,
        UserActionLogsService $objUserActionLogsService,
        UserActionLog $UserActionLog
    ) {
        $this->_checkCanView();

        $Exhibitor = Exhibitor::with([
            'prefecture',
            'exhibition',
            'exhibition_zone',
            'exhibitor_images' => function ($query) {
                $query->orderBy('sort_index', 'asc');
            },
            'exhibitor_videos' => function ($query) {
                $query->orderBy('sort_index', 'asc');
            },
            'exhibitor_booth_images' => function ($query) {
                $query->orderBy('sort_index', 'asc');
            },
            'products' => function ($query) {
                $query->where('view_flag', true);
            },
            'products.product_images' => function ($query) {
                $query->orderBy('sort_index', 'asc');
            },
            'products.product_videos' => function ($query) {
                $query->orderBy('sort_index', 'asc');
            },
            'products.product_attachment_files' => function ($query) {
                $query->orderBy('sort_index', 'asc');
            },
            'exhibition.exposition:id,slug'
        ])->findOrFail($exhibitor_id)->toArray();

        // ファイルの拡張取得
        foreach ($Exhibitor['products'] as $product_key => $product) {
            foreach ($product['product_attachment_files'] as $file_key => $product_attachment_file) {
                $file_extension = substr($product_attachment_file['file_path'], strcspn($product_attachment_file['file_path'], '.'), mb_strlen($product_attachment_file['file_path']));

                $Exhibitor['products'][$product_key]['product_attachment_files'][$file_key]['file_extension'] = $file_extension;
            }
        }

        $ContactRequestTypes = ContactRequestType::orderBy('id')->get()->toArray();

        $Exposition = Exposition::select(['id', 'slug'])->where('slug', $expo_slug)->firstOrFail();

        // ログを登録
        $this->modalLog(
            $objUserActionLogsService,
            $Exposition->id,
            '出展社モーダルOPEN',
            ['exhibitor_id' => $exhibitor_id]
        );

        $this->modalLog(
            $objUserActionLogsService,
            $Exposition->id,
            '出展社詳細ページOPEN',
            ['exhibitor_id' => $exhibitor_id]
        );

        return view('visitor.iframe.exhibitor_show', [
            'slug' => $expo_slug,
            'exhibitor' => $Exhibitor,
            'contact_request_types' => $ContactRequestTypes,
            'user' => Auth::user(),
        ]);
    }

    protected function _GetExhibitor()
    {
        return HttpCommonLib::GetExhibitorBySlugAndLoginUser();
    }

    /*
		$objUserActionLogsService obj
		$expo_id int
		$command string
		$option array
	*/
    private function modalLog($objUserActionLogsService, $expo_id, $command, $option)
    {
        $UserActionLog = new UserActionLog;

        $objUserActionLogsService->storeLog(
            $expo_id,
            $command,
            $option,
            $UserActionLog
        );
    }
}
