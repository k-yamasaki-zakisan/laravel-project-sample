<?php

namespace App\Http\Controllers\Visitor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// model
use App\UserActionLog;
use App\Exposition;
use App\ExhibitorVideo;
use App\ProductAttachmentFile;
use App\ProductVideo;

use App\Services\UserActionLogsService;

class UserActionLogsController extends Controller
{
    public function seminarVideoPlay(
        $expo_slug,
        $seminar_id,
        UserActionLogsService $objUserActionLogsService,
        UserActionLog $UserActionLog
    ) {
        $Exposition = Exposition::where('slug', $expo_slug)->firstOrFail();

        $objUserActionLogsService->storeLog(
            $Exposition->id,
            'セミナー動画PLAY',
            ['seminar_id' => $seminar_id],
            $UserActionLog
        );
    }

    public function seminarVideoStop(
        $expo_slug,
        $seminar_id,
        UserActionLogsService $objUserActionLogsService,
        UserActionLog $UserActionLog
    ) {
        $Exposition = Exposition::where('slug', $expo_slug)->firstOrFail();

        $objUserActionLogsService->storeLog(
            $Exposition->id,
            'セミナーモーダルCLOSE',
            ['seminar_id' => $seminar_id],
            $UserActionLog
        );
    }

    public function modalMoveExhibitor(
        $expo_slug,
        $exhibitor_id,
        UserActionLogsService $objUserActionLogsService,
        UserActionLog $UserActionLog
    ) {
        $Exposition = Exposition::where('slug', $expo_slug)->firstOrFail();

        $objUserActionLogsService->storeLog(
            $Exposition->id,
            '出展社詳細ページOPEN',
            ['exhibitor_id' => $exhibitor_id],
            $UserActionLog
        );
    }

    public function modalMoveProducts(
        $expo_slug,
        $exhibitor_id,
        UserActionLogsService $objUserActionLogsService,
        UserActionLog $UserActionLog
    ) {
        $Exposition = Exposition::where('slug', $expo_slug)->firstOrFail();

        $objUserActionLogsService->storeLog(
            $Exposition->id,
            '製品ページOPEN',
            ['exhibitor_id' => $exhibitor_id],
            $UserActionLog
        );
    }

    public function modalMoveContact(
        $expo_slug,
        $exhibitor_id,
        UserActionLogsService $objUserActionLogsService,
        UserActionLog $UserActionLog
    ) {
        $Exposition = Exposition::where('slug', $expo_slug)->firstOrFail();

        $objUserActionLogsService->storeLog(
            $Exposition->id,
            'お問い合わせページOPEN',
            ['exhibitor_id' => $exhibitor_id],
            $UserActionLog
        );
    }

    public function modalMoveChat(
        $expo_slug,
        $exhibitor_id,
        UserActionLogsService $objUserActionLogsService,
        UserActionLog $UserActionLog
    ) {
        $Exposition = Exposition::where('slug', $expo_slug)->firstOrFail();

        $objUserActionLogsService->storeLog(
            $Exposition->id,
            'チャットページOPEN',
            ['exhibitor_id' => $exhibitor_id],
            $UserActionLog
        );
    }

    public function modalExhibitorVideoPlay(
        $expo_slug,
        $exhibitor_video_id,
        UserActionLogsService $objUserActionLogsService,
        UserActionLog $UserActionLog
    ) {
        $Exposition = Exposition::where('slug', $expo_slug)->firstOrFail();

        $ExhibitorVideo = ExhibitorVideo::findOrFail($exhibitor_video_id);

        $objUserActionLogsService->storeLog(
            $Exposition->id,
            '出展社動画PLAY',
            [
                'exhibitor_id' => $ExhibitorVideo->exhibitor_id,
                'exhibitor_video_id' => $ExhibitorVideo->id
            ],
            $UserActionLog
        );
    }

    public function modalExhibitorVideoStop(
        $expo_slug,
        $exhibitor_video_id,
        UserActionLogsService $objUserActionLogsService,
        UserActionLog $UserActionLog
    ) {
        $Exposition = Exposition::where('slug', $expo_slug)->firstOrFail();

        $ExhibitorVideo = ExhibitorVideo::findOrFail($exhibitor_video_id);

        $objUserActionLogsService->storeLog(
            $Exposition->id,
            '出展社動画STOP',
            [
                'exhibitor_id' => $ExhibitorVideo->exhibitor_id,
                'exhibitor_video_id' => $ExhibitorVideo->id
            ],
            $UserActionLog
        );
    }

    public function modalProductFileDownload(
        $expo_slug,
        $product_file_id,
        UserActionLogsService $objUserActionLogsService,
        UserActionLog $UserActionLog
    ) {
        $Exposition = Exposition::where('slug', $expo_slug)->firstOrFail();

        $ProductAttachmentFile = ProductAttachmentFile::with([
            'product:id,exhibitor_id',
            'product.exhibitor:id'
        ])->findOrFail($product_file_id);

        $objUserActionLogsService->storeLog(
            $Exposition->id,
            '製品ファイルDOWNLOAD',
            [
                'exhibitor_id' => $ProductAttachmentFile->product->exhibitor->id,
                'product_id' => $ProductAttachmentFile->product_id,
                'product_file_id' => $ProductAttachmentFile->id,
            ],
            $UserActionLog
        );
    }

    public function modalProductVideoPlay(
        $expo_slug,
        $product_video_id,
        UserActionLogsService $objUserActionLogsService,
        UserActionLog $UserActionLog
    ) {
        $Exposition = Exposition::where('slug', $expo_slug)->firstOrFail();

        $ProductVideo = ProductVideo::with([
            'product:id,exhibitor_id',
            'product.exhibitor:id'
        ])->findOrFail($product_video_id);

        $objUserActionLogsService->storeLog(
            $Exposition->id,
            '製品動画PLAY',
            [
                'exhibitor_id' => $ProductVideo->product->exhibitor->id,
                'product_id' => $ProductVideo->product_id,
                'product_video_id' => $ProductVideo->id
            ],
            $UserActionLog
        );
    }

    public function modalProductVideoStop(
        $expo_slug,
        $product_video_id,
        UserActionLogsService $objUserActionLogsService,
        UserActionLog $UserActionLog
    ) {
        $Exposition = Exposition::where('slug', $expo_slug)->firstOrFail();

        $ProductVideo = ProductVideo::with([
            'product:id,exhibitor_id',
            'product.exhibitor:id'
        ])->findOrFail($product_video_id);

        $objUserActionLogsService->storeLog(
            $Exposition->id,
            '製品動画STOP',
            [
                'exhibitor_id' => $ProductVideo->product->exhibitor->id,
                'product_id' => $ProductVideo->product_id,
                'product_video_id' => $ProductVideo->id
            ],
            $UserActionLog
        );
    }
}
