<?php

namespace App\Http\Controllers\Visitor;

use App\Http\Controllers\Controller;

// request
use App\Http\Requests\Visitor\StoreContactsRequest;
use Illuminate\Http\Request;
// model
use App\Contact;
use App\Exposition;
use App\Exhibitor;
use App\ContactRequestType;
use App\UserActionLog;
// service
use App\Services\MailService;
// MailClass
//use App\Mail\NotifyPostContact;

use App\Services\UserActionLogsService;

use DB;
use Auth;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    protected $MailService;

    public function __construct(
        MailService $MailService
    ) {
        $this->MailService = $MailService;
    }

    public function store(
        StoreContactsRequest $request,
        Contact $Contact,
        UserActionLog $UserActionLog,
        UserActionLogsService $objUserActionLogsService,
        $expo_slug
    ) {
        $validated = $request->all();

        DB::beginTransaction();

        try {
            $Exhibitor = Exhibitor::with([
                'exhibition:id,exposition_id',
                'exhibition.exposition:id,slug,name',
                'users:id,email'
            ])->findOrFail($validated['exhibitor_id'])->toArray();

            if ($Exhibitor['exhibition']['exposition']['slug'] !== $expo_slug) throw new \RunTimeException("expo_slug is an invalid value.");

            $AuthUser = Auth::user();

            $Contact->fill($validated);
            $Contact->user_id = $AuthUser->id;
            $Contact->user_name = $AuthUser->name;
            $Contact->company_name = $AuthUser->company->name;
            // 電話番号、メールどちらかがnullで送信された場合は既存のuserテーブルの設定を記録
            if (empty($Contact->email)) $Contact->email = $AuthUser->email;
            if (empty($Contact->phone_number)) $Contact->phone_number = $AuthUser->phone_number;

            if (empty($Contact->save())) throw new \RunTimeException("Failed to save Contact.");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return redirect(route('visitor.exhibitors.show', [$expo_slug, $Exhibitor['id']]))->with('flash_message', 'お問い合わせに失敗しました。');
        }

        $this->MailService->sendMailContact($Contact->id);

        /*
		// 出展企業社員とお問い合わせ者へのメール通知
		$users = $Exhibitor['users'];
		$users[] = [
			'email' => $validated['email'] ?? $AuthUser->email,
		];

		try {
			foreach( $users as $user )
			{
				Mail::to( $user['email'] )
					->send(new NotifyPostContact([
						'subject' => "[{$Exhibitor['exhibition']['exposition']['name']}/{$Exhibitor['name']}]お問い合わせ完了",
						'contact_type' => ContactRequestType::find($Contact->contact_request_type_id)->name,
						'body' => $Contact->body
					]));
			}
		} catch (\Exception $e) {
			logger()->error("{$e->getMessage()} in {$e->getFile()} at Line:{$e->getLine()}");
			logger()->error('お問い合わせ通知メール送信失敗:' . print_r($tmp_summary, true));
			return redirect(route('visitor.exhibitors.show', [$expo_slug, $Exhibitor['id']]))->with('flash_message', 'お問い合わせに失敗しました。');
		}
*/

        $exposition = $Exhibitor['exhibition']['exposition'];

        // ログ
        $objUserActionLogsService->storeLog(
            $exposition['id'],
            'お問い合わせSUBMIT',
            [
                'exhibitor_id' => $Exhibitor['id'],
                'contact_id' => $Contact->id
            ],
            $UserActionLog
        );

        return redirect(route('visitor.exhibitors.show', [$expo_slug, $Exhibitor['id']]))->with('flash_message', 'お問い合わせが完了しました。');
    }
}
