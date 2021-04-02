<?php

namespace App\Http\Controllers\Trcd;

// Requests
use App\Http\Requests\Trcd\TrcdAlcoholCheckRecordGetRequest;
// Usecases
use App\Usecases\Trcd\TrcdAlcoholCheckRecord\SearchTrcdAlcoholCheckRecordsUsecase;

class TrcdAlcoholCheckRecordsController extends TrcdBaseController {

        public function index(TrcdAlcoholCheckRecordGetRequest $request, SearchTrcdAlcoholCheckRecordsUsecase $usecase) {
                return $usecase->search($request, $this->_getLoginUser());
        }

        public function download(TrcdAlcoholCheckRecordGetRequest $request, SearchTrcdAlcoholCheckRecordsUsecase $usecase) {
                return $usecase->download($request, $this->_getLoginUser());
        }
}