<?php
namespace App\Usecases\Trcd\TrcdAlcoholCheckRecord;

// Models
use App\ClientEmployee;

// Services
use App\Services\Trcd\TrcdAlcoholCheckRecordService;
use App\Services\Trcd\TrcdTerminalService;
use App\Services\ClientEmployeeService;
use App\Services\ClientGroupService;

// Requests
use App\Http\Requests\Trcd\TrcdAlcoholCheckRecordGetRequest;

// Utilities
use App\Facades\Csv;
use Carbon\Carbon;

class SearchTrcdAlcoholCheckRecordsUsecase {

	protected $TrcdAlcoholCheckRecordService;
	protected $TrcdTerminalService;
	protected $ClientEmployeeService;
	protected $ClientGroupService;

	public function __construct(
		TrcdAlcoholCheckRecordService $TrcdAlcoholCheckRecordService,
		TrcdTerminalService $TrcdTerminalService,
		ClientEmployeeService $ClientEmployeeService,
		ClientGroupService $ClientGroupService
	) {
		$this->TrcdAlcoholCheckRecordService = $TrcdAlcoholCheckRecordService;
		$this->TrcdTerminalService = $TrcdTerminalService;
		$this->ClientEmployeeService = $ClientEmployeeService;
		$this->ClientGroupService = $ClientGroupService;
	}

	/*
		一覧画面用
	*/
	public function search(TrcdAlcoholCheckRecordGetRequest $request, ClientEmployee $LoginUser) {
		// 選択可能な勤怠所属グループ取得
		$client_group_list = $this->getSelectableClientGroupOf($LoginUser)->pluck('name', 'id');
		// 選択可能な社員取得
		$client_employees = $this->getSelectableClientEmployeeOf($LoginUser, $client_group_list);
		// 保有TRCD端末取得
		$trcd_terminal_list = $this->getTrcdTerminalIdsByClientId($LoginUser->client_id)->pluck('name', 'id');

		// 検索条件生成処理
		$search_conditions = $request->validated();
		$conditions = $this->buildCondition($search_conditions, $LoginUser->client_id, $client_employees, $trcd_terminal_list->keys()->toArray());

		// 検査結果取得
		$trcd_alcohol_check_records = $this->TrcdAlcoholCheckRecordService->buildSearchQuery($conditions)
			->orderBy('checked_datetime', 'desc')
			->paginate()
		;

		// 打刻種別リスト
		$attendance_types = $this->getAttendanceTypeConstants();

		return view('trcd.trcd_alcohol_check_records.index', compact(
			'search_conditions',
			'trcd_alcohol_check_records',
			'client_group_list',
			'client_employees',
			'attendance_types',
			'trcd_terminal_list'
		));
	}

	/*
		ダウンロード
	*/
	public function download(TrcdAlcoholCheckRecordGetRequest $request, ClientEmployee $LoginUser) {
		// 選択可能な勤怠所属グループ取得
		$client_group_list = $this->getSelectableClientGroupOf($LoginUser)->pluck('name', 'id');
		// 選択可能な社員取得
		$client_employees = $this->getSelectableClientEmployeeOf($LoginUser, $client_group_list)->keyBy('id');
		// 保有TRCD端末取得
		$trcd_terminal_list = $this->getTrcdTerminalIdsByClientId($LoginUser->client_id)->pluck('name', 'id');

		// 検索条件生成処理
		$search_conditions = $request->validated();
		$conditions = $this->buildCondition($search_conditions, $LoginUser->client_id, $client_employees, $trcd_terminal_list->keys()->toArray());

		// 検査結果取得
		$trcd_alcohol_check_records = $this->TrcdAlcoholCheckRecordService->buildSearchQuery($conditions)
			->orderBy('checked_datetime', 'desc')
			->get();

		// 打刻種別リスト
		$attendance_types = $this->getAttendanceTypeConstants();

		$include_trcd_terminal_name = $trcd_terminal_list->count() > 1;
		// 集計結果取得
		$csv_headers = [
			'測定日時',
			'測定者',
			'測定値',
			'測定結果',
			'出勤時/退勤時',
		];
		if ( $include_trcd_terminal_name ) array_splice($csv_headers, 1, 0, '端末名');

		$csv_rows = [];

		foreach( $trcd_alcohol_check_records as $trcd_alcohol_check_record ) {
			$tmp_row = [
				$trcd_alcohol_check_record['checked_datetime'],
				$client_employees[$trcd_alcohol_check_record['client_employee_id']]['name'] ?? null,
				$trcd_alcohol_check_record['measured_value'],
				$trcd_alcohol_check_record['result_flag'] ? 'OK' : 'NG',
			];

			switch($trcd_alcohol_check_record['attendance_raw']['attendance_type_id']) {
				case($attendance_types['STAMP_WORK_BEGIN']):
					$tmp_row[] = '出勤';
					break;
				case($attendance_types['STAMP_WORK_FINISH']):
					$tmp_row[] = '退勤';
					break;
				default:
					$tmp_row[] = null;
					break;
			}

			if ( $include_trcd_terminal_name ) array_splice($tmp_row, 1, 0, $trcd_terminal_list[$trcd_alcohol_check_record['trcd_terminal_id']] ?? null);

			$csv_rows[] = $tmp_row;
		}

		$file_name = "アルコールチェック履歴.csv";
		$options = [
			'line_feed_code_id' => config('database.trcd.line_feed_types.LF'),
			'delimiter_id' => config('database.trcd.delimiter_types.CONST.COMMA'),
			'enclosure_id' => config('database.trcd.enclosure_types.CONST.DOUBLE_QUOTATION'),
		];

		return Csv::download(
			$csv_headers,
			$csv_rows,
			$file_name,
			$options
		);
	}

	/*
		検索条件生成
	*/
	private function buildCondition(Array $validated, $client_id, $selectable_client_employees, $trcd_terminal_ids) {
		$conditions = [
			'select' => [
				'id',
				'trcd_terminal_id',
				'checked_datetime',
				'client_employee_id',
				'measured_value',
				'result_flag',
				'attendance_raw_id',
			],
			'trcd_terminal_id' => $trcd_terminal_ids,
			'with' => [
				'attendance_raw:id,attendance_type_id'
			],
		];

		// 開始日・終了日
		if ( !empty($validated['from']) ) $conditions['from'] = Carbon::parse($validated['from'])->startOfDay()->format('Y-m-d H:i:s');
		if ( !empty($validated['until']) ) $conditions['until'] = Carbon::parse($validated['until'])->endOfDay()->format('Y-m-d H:i:s');

		// 勤怠所属グループが指定されている場合は社員をフィルタリング
		if ( !empty($validated['client_group_id']) ) $selectable_client_employees = $selectable_client_employees->where('client_group_id', $validated['client_group_id']);

		// 社員が指定されていない場合はフィルタリング社員で検索
		// 指定されている場合、選択可能社員であれば特定社員指定、選択可能社員でなければ空配列（マッチさせないようにする）
		if ( empty($validated['client_employee_id']) ) $conditions['client_employee_id'] = $selectable_client_employees->pluck('id')->toArray();
		else $conditions['client_employee_id'] = $selectable_client_employees[$validated['client_employee_id']]['id'] ?? [];

		return $conditions;
	}

	/*
		企業IDからTRCD端末取得
		@param int $client_id
		@return Collection
	*/
	private function getTrcdTerminalIdsByClientId($client_id) {
		return $this->TrcdTerminalService->buildSearchQueryByClientId($client_id)->get();
	}

	/*
		勤怠種別取得
	*/
	private function getAttendanceTypeConstants() {
		return config('database.trcd.attendance_types.CONST');
	}

	/*
		選択可能な勤怠所属グループ取得
		@param  App\ClientEmployee $ClientEmployee
		@return Collection
	*/
	private function getSelectableClientGroupOf(ClientEmployee $ClientEmployee) {
		$ClientGroups = $this->ClientGroupService->getByClientId($ClientEmployee->client_id)->sortBy('id');

		// 管理者であればすべて
		if ( $ClientEmployee->hasRole('ADMIN') ) return $ClientGroups;

		// 閲覧可能勤怠所属グループID
		$readable_client_group_ids = $ClientEmployee->readable_client_groups->pluck('id', 'id');

		// グループに所属していれば自グループも追加
		if ( !empty($ClientEmployee->client_group_id) ) $readable_client_group_ids[$ClientEmployee->client_group_id] = $ClientEmployee->client_group_id;

		// フィルタリング処理
		$ClientGroups = $ClientGroups->filter(function($item, $key) use($readable_client_group_ids) {
			return isset($readable_client_group_ids[$item->id]);
		});

		return $ClientGroups;
	}

	/*
		選択可能な社員取得（論理削除されている社員も含める）
		@param  App\ClientEmployee $ClientEmployee
		@param  $client_group_list
		@return Collection
	*/
	private function getSelectableClientEmployeeOf(ClientEmployee $ClientEmployee, $client_group_list) {
		$ClientEmployees = $this->ClientEmployeeService->getQuery()
			->select([
				'id',
				'code',
				'name',
				'client_group_id',
				'deleted_at',
			])
			->specificClient($ClientEmployee->client_id)
			->withTrashed()
			->get()
			->keyBy('id')
			->sortBy('code');

		// 管理者であればすべて
		if ( $ClientEmployee->hasRole('ADMIN') ) return $ClientEmployees;

		// フィルタリング処理
		$ClientEmployees = $ClientEmployees->filter(function($item, $key) use($client_group_list) {
			return (
				empty($item['client_group_id']) // 無所属社員
				|| !empty($item['deleted_at']) // 論理削除済み
				|| isset($client_group_list[$item['client_group_id']]) // 指定所属グループに所属している
			);
		});

		return $ClientEmployees;
	}
}