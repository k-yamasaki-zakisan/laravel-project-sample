<?php
namespace App\Usecases\Corporations;

// Services
use App\Services\CorporationService;
// Requests
use App\Http\Requests\Corporations\SearchCorporationRequest;
// 統合API用
use App\Libraries\IntegratedAPI\SearchCondition;
// Pagination
use Illuminate\Pagination\LengthAwarePaginator;
// Utilities
use Carbon\Carbon;
use Illuminate\Support\Arr;

class SearchCorporationUsecase {
	protected $per_page = 50; // 画面に表示する件数
	protected $SearchCondition;
	protected $CorporationService;

	public function __construct(
		SearchCondition $SearchCondition,
		CorporationService $CorporationService
	) {
		$this->SearchCondition = $SearchCondition;
		$this->CorporationService = $CorporationService;
	}

	public function __invoke(SearchCorporationRequest $request) {
		$validated = $request->validated();
		$search = $validated['search'] ?? [];
		$page = $validated['page'] ?? null;

		// 統合API用検索データ生成
		$conditions = $this->buildConditions($search);
		// 検索結果取得
		$search_result = $this->CorporationService->search($conditions);


		// データ整形（暫定）
		foreach( $search_result['data'] as $key => $corporation ) {
			if ( empty($corporation['head_office']) ) continue;

			$head_office = Arr::only($corporation['head_office'], ['name', 'address']);
			$head_office['tel']  = collect(Arr::where($corporation['head_office']['office_contacts'], function($value, $key) {
				// 固定電話で制限
				return $value['contact_type']['slug'] === 'tel';
			}))->sortBy(function($value, $key) {
				// nullは後ろ、indexが同じ場合はidで判定
				return ($value['sort_index'] ?? 'Z') . "_{$value['office_contact_id']}";
			})->pluck('value')
			->first();

			$search_result['data'][$key]['head_office'] = $head_office;
		}


		// Paginator生成
		$Paginator = new LengthAwarePaginator(
			$search_result['data'],
			$search_result['summary']['total'],
			$this->per_page,
			$page,
			['path' => $request->url()]
		);
		$Paginator->appends(['search' => $search]);

		return $Paginator;
	}

	/*
		統合API用検索データ生成
		@params Array $search_conditions リクエストされた検索条件
		@return Array
	*/
	protected function buildConditions(Array $search_conditions) {
		$this->SearchCondition->addFields([
			'corporation_id',
			'name',
		])->addWith([
			'head_office',
			'head_office.office_contacts',
			'head_office.office_contacts.contact_type',
		])->setLimit($this->per_page);

		foreach( $search_conditions as $key => $value ) {
			switch($key) {
				case('corporation_id'):
					if ( isset($value) ) $this->SearchCondition->addWhereEqual($key, $value);
					break;
				case('name'):
					if ( isset($value) ) $this->SearchCondition->addWhereLike($key, $value);
					break;
				default:
					break;
			}
		}

		return $this->SearchCondition->toArray();
	}
}


