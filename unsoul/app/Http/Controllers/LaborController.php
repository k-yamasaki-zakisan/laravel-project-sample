<?php

namespace App\Http\Controllers;

// Services
use App\Services\LaborService;
// Requests
use App\Http\Requests\Labors\SearchLaborRequest;
use Illuminate\Http\Request;
// Usecases
use App\Usecases\Labors\SearchLaborUsecase;
use App\Usecases\Labors\CreateLaborUsecase;


class LaborController extends UnsoulBaseController
{

    protected $LaborService;

    public function __construct(
            LaborService $LaborService
    ) {
            $this->LaborService = $LaborService;
    }


    public function index(SearchLaborRequest $request, SearchLaborUsecase $usecase){
    
	$employees = $usecase($request);
	$search = $request->search ?? [];


        return view('unsoul.labors.index', compact(
                        'search',
                        'employees'
                ));

    }

}
