<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;

// Services
use App\Services\Trcd\TrcdTerminalService;

// Repositories
use App\Repositories\ClientRepositoryInterface AS ClientRepository;
use App\Repositories\ClientBranchRepositoryInterface AS ClientBranchRepository;
use App\Repositories\Trcd\TrcdTerminalRepositoryInterface AS TrcdTerminalRepository;

// Requests
use Illuminate\Http\Request;
use App\Http\Requests\SuperAdmin\TrcdTerminalPostRequest;

// Forms
use Kris\LaravelFormBuilder\FormBuilderTrait;
use Kris\LaravelFormBuilder\FormBuilder;
use App\Forms\TrcdTerminalForm;

class TrcdTerminalsController extends Controller {

	use FormBuilderTrait;

	/**
	 * 一覧ページ
	 */
	public function index(
		TrcdTerminalRepository $objTrcdTerminalRepo
	) {
		$trcd_terminals = $objTrcdTerminalRepo
			->orderby('id', 'ASC')
			->paginate(20);

		return view('trcd_terminals.index', compact('trcd_terminals'));
	}

	/**
	 * 登録フォーム
	 */
	public function create(
		ClientRepository $objClientRepo,
		ClientBranchRepository $objClientBranchRepo,
		Request $request
	) {
		$selected_client_branches = collect();
		// 全支店取得
		$client_branches = $objClientBranchRepo
			->select(['id', 'name', 'phonetic', 'client_id'])
			->orderBy('client_id', 'ASC')
			->orderBy('phonetic', 'ASC')
			->get()
			->keyBy('id');
		// 所属企業取得
		$clients = $objClientRepo
			->whereIn('id', $client_branches->pluck('client_id'))
			->pluck('name', 'id');
		// プルダウン項目名変換処理
		foreach($client_branches as $client_branch_id => $client_branch){
			if ( !isset($clients[$client_branch['client_id']]) ) continue;

			$client_branches[$client_branch_id]['name'] = $clients[$client_branch['client_id']] . '：' . $client_branch['name'];
		}

		$form = $this->form(TrcdTerminalForm::class, [
			'method' => 'POST',
			'url' => route('trcd_terminals.store')
		]);

		return view('trcd_terminals.form', compact(
			'form',
			'client_branches',
			'selected_client_branches'
		));
	}

	/*
		登録処理
	*/
	public function store(
		TrcdTerminalPostRequest $request,
		TrcdTerminalService $objTrcdTerminalService
	) {
		$data = $request->getOnlyAllowedFields();
		$client_branch_ids = Arr::pull($data, 'client_branch_ids');
		$objTrcdTerminal = $objTrcdTerminalService->saveWithAssociation($data, $client_branch_ids);

		if ( empty($objTrcdTerminal) ) {
			$validator = $objTrcdTerminalService->getLastValidator();
			$errors = empty($validator) ? ['db' => '保存に失敗しました。'] : $validator->errors()->toArray();
			logger()->error($errors);

			$request->session()->flash('error_message', '保存に失敗しました。');
			return redirect()->back()->withInput($request->input())->withErrors($errors);
		}

		$request->session()->flash('success_message', '保存が完了しました。');

		return redirect(route('trcd_terminals.index'));
	}

	/*
		編集ページ
	*/
	public function edit(
		Request $request,
		TrcdTerminalRepository $objTrcdTerminalRepo,
		ClientBranchRepository $objClientBranchRepo,
		ClientRepository $objClientRepo,
		$trcd_terminal_id
	) {
		$objTrcdTerminal = $objTrcdTerminalRepo->findOrFail($trcd_terminal_id);
		// 全支店取得
		$client_branches = $objClientBranchRepo
			->select(['id', 'name', 'phonetic', 'client_id'])
			->orderBy('client_id', 'ASC')
			->orderBy('phonetic', 'ASC')
			->get()
			->keyBy('id');
		// 所属企業取得
		$clients = $objClientRepo
			->whereIn('id', $client_branches->pluck('client_id'))
			->pluck('name', 'id');
		// プルダウン項目名変換処理
		foreach($client_branches as $client_branch_id => $client_branch){
			if ( !isset($clients[$client_branch['client_id']]) ) continue;

			$client_branches[$client_branch_id]['name'] = $clients[$client_branch['client_id']] . '：' . $client_branch['name'];
		}

		$selected_client_branches = collect();
		$tmp_selected_client_branches = $objTrcdTerminal
			->client_branches()
			->get()
			->pluck('name', 'id');
		foreach($tmp_selected_client_branches as $client_branch_id => $client_branch_name){
			if ( !isset($client_branches[$client_branch_id]) ) continue;

			$selected_client_branches[$client_branch_id] = $client_branches->pull($client_branch_id);
		}

		$form = $this->form(TrcdTerminalForm::class, [
			'method' => 'PUT',
			'url' => route('trcd_terminals.update', $trcd_terminal_id),
			'model' => $objTrcdTerminal
		]);

		return view('trcd_terminals.form', compact(
			'form',
			'client_branches',
			'selected_client_branches'
		));
	}

	/*
		更新処理
	*/
	public function update(
		TrcdTerminalPostRequest $request,
		TrcdTerminalService $objTrcdTerminalService,
		TrcdTerminalRepository $objTrcdTerminalRepo,
		$trcd_terminal_id
	) {
		$data = $request->getOnlyAllowedFields();
		$objTrcdTerminal = $objTrcdTerminalRepo->findOrFail($trcd_terminal_id);
		$data['id'] = $objTrcdTerminal->id;
		$client_branch_ids = Arr::pull($data, 'client_branch_ids');

		$objTrcdTerminal = $objTrcdTerminalService->saveWithAssociation($data, $client_branch_ids);

		if ( empty($objTrcdTerminal) ) {
			$validator = $objTrcdTerminalService->getLastValidator();
			$errors = empty($validator) ? ['db' => '保存に失敗しました。'] : $validator->errors()->toArray();
			logger()->error($errors);

			$request->session()->flash('error_message', '保存に失敗しました。');
			return redirect()->back()->withInput($request->input())->withErrors($errors);
		}

		$request->session()->flash('success_message', '保存が完了しました。');

		return redirect(route('trcd_terminals.index'));
	}

	/*
		削除
	*/
	public function delete(
		Request $request,
		TrcdTerminalService $objTrcdTerminalService,
		$trcd_terminal_id
	) {
		$result = $objTrcdTerminalService->delete($trcd_terminal_id);
		
		if ( empty($result) ) {
			$validator = $objTrcdTerminalService->getLastValidator();
			$errors = empty($validator) ? ['db' => '削除に失敗しました。'] : $validator->errors()->toArray();
			logger()->error($errors);

			$request->session()->flash('error_message', '削除に失敗しました。');
		} else {
			$request->session()->flash('success_message', '削除が完了しました。');
		}

		return redirect(route('trcd_terminals.index'));
	}
}
?>
