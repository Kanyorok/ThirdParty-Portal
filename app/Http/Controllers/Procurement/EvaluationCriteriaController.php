<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Section;
use Illuminate\Http\Request;

class EvaluationCriteriaController extends Controller
{
    //
        public function index()
        {
            $sections = Section::all();
            return view('procurement.tendering.settings.sections', compact('sections'));
    }

    public function create(){
        return view('procurement.tendering.tendersetup.evaluationcriteriasetup.create');
    }

    public function viewCriteria()
    {
        return view('procurement.tendering.tendersetup.evaluationcriteriasetup.criterias');
    }

}
