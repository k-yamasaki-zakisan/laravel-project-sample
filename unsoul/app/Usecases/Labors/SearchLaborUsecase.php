<?php
namespace App\Usecases\Labors;

// Services
use App\Services\LaborService;
// Requests
use App\Http\Requests\Labors\SearchLaborRequest;
// 統合API用
use App\Libraries\IntegratedAPI\SearchCondition;
// Utilities
use Carbon\Carbon;

class SearchLaborUsecase {
	protected $per_page = 50; // 画面に表示する件数
	protected $SearchCondition;
	protected $LaborService;

	public function __construct(
		SearchCondition $SearchCondition,
		LaborService $LaborService
	) {
		$this->SearchCondition = $SearchCondition;
		$this->LaborService = $LaborService;
	}

	public function __invoke(SearchLaborRequest $request) {
		$validated = $request->validated();
		$search = $validated['search'] ?? [];
		$page = $validated['page'] ?? null;

		// 統合API用検索データ生成
		$conditions = $this->buildConditions($search);

		// ページネーション
		$corporations = $this->LaborService->paginate(
			$conditions,
			$this->per_page,
			$page,
			['path' => $request->url()]
		);
		$corporations->appends(['search' => $search]);

		return $corporations;
	}

	/*
		統合API用検索データ生成
		@params Array $search_conditions リクエストされた検索条件
		@return Array
	*/
	protected function buildConditions(Array $search_conditions) {
		$this->SearchCondition->addFields([
			'employee_id',
			'last_name',
			'birthday',
		])->setLimit($this->per_page);

		foreach( $search_conditions as $key => $value ) {
			switch($key) {
				case('employee_id'):
					if ( isset($value) ) $this->SearchCondition->addWhereLike($key, $value);
					break;
				default:
					break;
			}
		}

		return $this->SearchCondition->toArray();
	}
}