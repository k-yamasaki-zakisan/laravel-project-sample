<?php
namespace App\Usecases\Persons;

// Services
use App\Services\PersonService;
// Requests
use App\Http\Requests\Persons\SearchPersonRequest;
// 統合API用
use App\Libraries\IntegratedAPI\SearchCondition;
// Utilities
use Carbon\Carbon;

class SearchPersonUsecase {
	protected $per_page = 50; // 画面に表示する件数
	protected $SearchCondition;
	protected $PersonService;

	public function __construct(
		SearchCondition $SearchCondition,
		PersonService $PersonService
	) {
		$this->SearchCondition = $SearchCondition;
		$this->PersonService = $PersonService;
	}

	public function __invoke(SearchPersonRequest $request) {
		$validated = $request->validated();
		$search = $validated['search'] ?? [];
		$page = $validated['page'] ?? null;

		// 統合API用検索データ生成
		$conditions = $this->buildConditions($search);
		
		// ページネーション
		$persons = $this->PersonService->paginate(
			$conditions,
			$this->per_page,
			$page,
			['path' => $request->url()]
		);
		$persons->appends(['search' => $search]);
		
		return $persons;
	}

	/*
		統合API用検索データ生成
		@params Array $search_conditions リクエストされた検索条件
		@return Array
	*/
	protected function buildConditions(Array $search_conditions) {
		$this->SearchCondition->addFields([
			'person_id',
			'full_name',
			'corpration_name',
			'birthday',
		])->setLimit($this->per_page);
		
	foreach( $search_conditions as $key => $value ) {
			switch($key) {
				case('full_name'):
				case('corporation_name');
					if ( isset($value) ) $this->SearchCondition->addWhereLike($key, $value);
					break;
				default:
					break;
			}
		}
		return $this->SearchCondition->toArray();
	}
}
