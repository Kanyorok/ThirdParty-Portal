<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class COASegmentController extends Controller
{
    //
public function index()
{
    $segments = COASegment::all(); // or paginate if needed

    return view('finance.chartofaccounts.segmentconfiguration.index', compact('segments'));
}

public function create()
{
    $accountTypes = COASegment::where('SegmentType', 'AccountType')->get();
    $subTypeGroups = COASegment::where('SegmentType', 'SubTypeGroup')->get();
    $subAccountTypes = COASegment::where('SegmentType', 'SubAccountType')->get();
    $branchCodes = COASegment::where('SegmentType', 'BranchCode')->get();

    return view('finance.chartofaccounts.segmentconfiguration.create', compact('accountTypes', 'subTypeGroups', 'subAccountTypes', 'branchCodes'));
}

}
class COASegment extends Model
{
    protected $table = 't_COASegments';

    protected $fillable = [
        'SegmentType',
        'SegmentCode',
        'SegmentName',
        'IsActive',
    ];
}