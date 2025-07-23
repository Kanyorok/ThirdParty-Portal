<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\CodeDetail;
use App\Models\Finance\FinanceGLTypeGroup;
use App\Models\Finance\SegmentOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class COASegmentController extends Controller
{
    //
    public function index()
    {
        $this->authorize(PermissionEnum::FinanceCOAView, SegmentOrder::class);
        $accountTypes = CodeDetail::select('CodeID','Value','Description','DisplayOrder')->where('CodeID','GLAccountType')->get();
        $segments = SegmentOrder::select('Id', 'SegmentType')->get();
        $glDigits=SegmentOrder::where('SegmentType','GLDigits')->pluck('Description')->first();
        $glTypeGroups=FinanceGLTypeGroup::select('Id','Description','GLAccountTypeId')->get();
        return view('finance.chartofaccounts.segmentconfiguration.index', compact(
            'segments',
            'glDigits',
            'accountTypes',
            'glTypeGroups',
        ));
    }

    public function create()
    {
        return view('finance.chartofaccounts.segmentconfiguration.create');
    }

    public function segmentOrder(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceCOAUpdate, SegmentOrder::class);
        // Validate input
        $validated = $request->validate([
            'segment_order' => ['required', 'string'],
        ]);
        try {
            DB::beginTransaction();

            $order = json_decode($request->input('segment_order'), true);
            $userId = Auth::id();

            //Get the GL digits value
            $glDigits=SegmentOrder::where('SegmentType','GLDigits')->pluck('Description')->first();

            // Step 1: Remove old rows
            SegmentOrder::truncate();

            // Step 2: Re-insert in new order
            foreach ($order as $segment) {
                $create=SegmentOrder::create([
                    'SegmentType' => $segment,
                    'Description' => null, // or use a default if needed
                    'CreatedBy' => $userId,
                    'CreatedOn' => Carbon::now(),
                    'ModifiedBy' => $userId,
                    'ModifiedOn' => Carbon::now(),
                ]);
            }
            //Update the description for the gl digits
            SegmentOrder::where('SegmentType','GLDigits')->update(['Description'=>$glDigits]);

            activity()
                ->causedBy($userId)
                ->performedOn(new SegmentOrder())
                ->withProperties(['segment' => $create])
                ->log('Segment order updated');

            DB::commit();
            return redirect()->back()->with('success', 'Segment order saved successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th->getMessage();
            Log::error('Failed to re-order segments:' . $th->getMessage());
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function editGlDigit(Request $request){
        $this->authorize(PermissionEnum::FinanceCOAUpdate, SegmentOrder::class);
        $validated=$request->validate([
            'glDigits' => ['required', 'integer','min:1'],
        ]);
        try {
            DB::beginTransaction();
            $update=SegmentOrder::where('SegmentType','GLDigits')->update(['Description'=>$validated['glDigits']]);
            activity()
                ->causedBy(Auth::id())
                ->performedOn(new SegmentOrder())
                ->withProperties(['segment' => $update])
                ->log('GL Digits updated');
            DB::commit();
            return back()->with('success', 'GL Digits updated successfully.');
        }catch(\Throwable $th){
            DB::rollBack();
            Log::error('Failed to update GL Digits:' . $th->getMessage());
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function saveGLTypeSegment(Request $request){
        $this->authorize(PermissionEnum::FinanceCOAUpdate, SegmentOrder::class);
        $values=$request->segment_values;

            //Update the Code details where I will store the segment value
            try {
                DB::beginTransaction();
                foreach ($values as $key=>$value) {
                    if (!is_null($value)) {
                        $update=CodeDetail::where('CodeID','GLAccountType')->where('Value',$key)->update(['DisplayOrder'=>$value]);
                    }else{
                        $update=CodeDetail::where('CodeID','GLAccountType')->where('Value',$key)->update(['DisplayOrder'=>0]);
                    }
                }
                activity()
                    ->causedBy(Auth::id())
                    ->performedOn(new CodeDetail())
                    ->withProperties(['CodeID'=>'GLAccountType'])
                    ->log('GL Account Type updated segment value');
                DB::commit();
                return back()->with('success', 'GL Type Segment updated successfully.');
            }catch(\Throwable $th){
                DB::rollBack();
                Log::error('Failed to update GL Type Segment Value:' . $th->getMessage());
                return back()->with('error', 'Something went wrong. Please try again.');
            }
        }

        public function saveGLAccountTypeSegment(Request $request){
            $this->authorize(PermissionEnum::FinanceCOAUpdate, SegmentOrder::class);
            $validated=$request->validate(['value'=>'required|integer|min:1']);

            try {
                DB::beginTransaction();
                $update=FinanceGLTypeGroup::where('Id',$request->GLTypeGroupID)->update(['SegmentValue'=>$validated['value']]);
                activity()
                    ->causedBy(Auth::id())
                    ->performedOn(new FinanceGLTypeGroup())
                    ->withProperties(['action' => 'update'])
                    ->log('GL Type Group updated segment value');
                DB::commit();
                return back()->with('success', 'GL Account Type Segment updated successfully.');
            }catch(\Throwable $th){
                DB::rollBack();
                Log::error('Failed to update GL Type Segment Value:' . $th->getMessage());
                return back()->with('error', 'Something went wrong. Please try again.');
            }
        }

    public function saveSubGLAccountTypeSegment(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceCOAUpdate, SegmentOrder::class);
        $validated=$request->validate(['value'=>'required|integer|min:1']);

        try {
            DB::beginTransaction();
            $update=FinanceGLTypeGroup::where('Id',$request->GLTypeGroupID)->update(['SegmentValue'=>$validated['value']]);
            activity()
                ->causedBy(Auth::id())
                ->performedOn(new FinanceGLTypeGroup())
                ->withProperties(['action' => 'update'])
                ->log('GL Type Group updated segment value');
            DB::commit();
            return back()->with('success', 'GL Account Type Segment updated successfully.');
        }catch(\Throwable $th){
            DB::rollBack();
            Log::error('Failed to update GL Type Segment Value:' . $th->getMessage());
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

}
