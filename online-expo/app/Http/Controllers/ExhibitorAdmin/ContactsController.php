<?php

namespace App\Http\Controllers\ExhibitorAdmin;

use App\Http\Controllers\Controller;

// Request
use Illuminate\Http\Request;
use App\Http\Requests\ExhibitorAdmin\Contacts\UpdateContactsRequest;
// model
use App\Contact;
use App\Exhibition;

use App\Http\HttpCommonLib;
use Illuminate\Http\Exceptions\HttpResponseException;

use Carbon\Carbon;
use Auth;
use DB;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactsController extends Controller
{
    public function index()
    {
        $slug = HttpCommonLib::GetSlug();
        $objExposition = HttpCommonLib::GetExposition();

        // URLスラッグに指定された該当のEXPOデータがない
        if ($objExposition == null) {
            abort('404');
        }

        $AuthAdmin = Auth::user();

        $exhibitions = Exhibition::where('exposition_id', $objExposition->id)->with('exhibitors')->get()->toArray();

        $exhibitor_ids = [];
        foreach ($exhibitions as $exhibition) {
            foreach ($exhibition['exhibitors'] as $exhibitor) {
                $exhibitor_ids[] = $exhibitor['id'];
            }
        }

        $UserExhibitor = DB::table('user_exhibitor')
            ->where('user_id', $AuthAdmin->id)
            ->whereIn('exhibitor_id', $exhibitor_ids)
            ->first();

        $contacts = Contact::where('exhibitor_id', $UserExhibitor->exhibitor_id)
            ->with(['user', 'contact_request_type'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        foreach ($contacts as $key => $contact) {
            $now_time = Carbon::now();
            $day_lag = $now_time->diffInDays(Carbon::parse($contact['created_at']));
            // 表示時間の設定
            if (365 < $day_lag) $display_time = Carbon::parse($contact['created_at'])->format('Y/m/d');
            elseif (0 < $day_lag) $display_time = Carbon::parse($contact['created_at'])->format('m月d日');
            else $display_time = Carbon::parse($contact['created_at'])->format('H:i');

            $contacts[$key]['display_time'] = $display_time;
        }

        return view('exhibitor_admin.contacts.index', compact(
            'slug',
            'contacts'
        ));
    }

    public function update(UpdateContactsRequest $request)
    {
        logger($request->all());
        $contact_data = $request->all();

        DB::beginTransaction();

        try {
            $Contact = Contact::findOrFail($contact_data['id']);

            $Contact->status_text = $contact_data['status_text'];

            if (empty($Contact->save())) throw new \RunTimeException("Failed to save Contact.");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            $response = [
                'data'    => [],
                'status'  => 400,
                'summary' => 'Failed Save',
                'errors'  => ['message' => ['ステータス更新に失敗しました']]
            ];

            throw new HttpResponseException(
                response()->json($response, 400)
            );
        }

        return response()->json([
            'message' => 'ステータスの更新が完了しました'
        ]);
    }

    public function csvDownload()
    {
        $csv_header = ['名前', '内容', '問い合わせ方法', '問い合わせ日時'];

        $$response = new StreamedResponse(function () use ($request, $cvsList) {
        });
        dd('test');
    }
}
