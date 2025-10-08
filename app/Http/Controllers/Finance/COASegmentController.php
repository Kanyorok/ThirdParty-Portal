<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\CodeDetail;
use App\Models\Finance\FinanceGLAccounts;
use App\Models\Finance\FinanceGLSubAccountTypes;
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
        $accountTypes = CodeDetail::select('ID','CodeID','Value','Description','DisplayOrder')->where('CodeID','GLAccountType')->get();
        $segments = SegmentOrder::select('Id', 'SegmentType')->get();
        $glDigits=SegmentOrder::where('SegmentType','GLDigits')->pluck('Description')->first();
        $data=[];

        foreach ($accountTypes as $accountType) {
            // Ensure the account type node is initialized
            if (!isset($data[$accountType->Description])) {
                $data[$accountType->Description] = [
                    'SegmentValue' => $accountType->DisplayOrder ?? null,
                    'Children' => []
                ];
            }

            $glTypeGroups = FinanceGLTypeGroup::select('Id','Description','GLAccountTypeId','SegmentValue')
                ->where('GLAccountTypeId', $accountType->Value)
                ->get();

            foreach ($glTypeGroups as $glTypeGroup) {
                // Ensure the type group node is initialized
                if (!isset($data[$accountType->Description]['Children'][$glTypeGroup->Description])) {
                    $data[$accountType->Description]['Children'][$glTypeGroup->Description] = [
                        'SegmentValue' => $glTypeGroup->SegmentValue ?? null,
                        'Children' => []
                    ];
                }

                $glSubTypeGroups = FinanceGLSubAccountTypes::select('Id','Description','SegmentValue')
                    ->where('GLTypeGroupId', $glTypeGroup->Id)
                    ->get();

                foreach ($glSubTypeGroups as $glSubTypeGroup) {
                    $data[$accountType->Description]['Children'][$glTypeGroup->Description]['Children'][$glSubTypeGroup->Id] = [
                        'Id' => $glSubTypeGroup->Id,
                        'Description' => $glSubTypeGroup->Description,
                        'SegmentValue' => $glSubTypeGroup->SegmentValue,
                    ];
                }
            }
        }


        return view('finance.chartofaccounts.segmentconfiguration.index', compact(
            'segments',
            'glDigits',
            'accountTypes',
            'glTypeGroups',
            'glSubTypeGroups',
            'data'
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

            //Update GlCode
            $this->insertGLCodes();

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
            //Update the Major GL Table
            FinanceGLAccounts::query()->update(['GLDigits' => $validated['glDigits']]);
            activity()
                ->causedBy(Auth::id())
                ->performedOn(new SegmentOrder())
                ->withProperties(['segment' => $update])
                ->log('GL Digits updated');
            //Update GlCode
            $this->insertGLCodes();

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
                        //Update the Major GL Table
                        FinanceGLAccounts::where('GLAccountTypeID',$key)->update(['GLAccountTypeValue'=>$value]);
                    }else{
                        $update=CodeDetail::where('CodeID','GLAccountType')->where('Value',$key)->update(['DisplayOrder'=>0]);
                        //Update the Major GL Table
                        FinanceGLAccounts::where('GLAccountTypeID',$key)->update(['GLAccountTypeValue'=>0]);
                    }
                }
                //Update GlCode
                $this->insertGLCodes();

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
                //Update the Major GL Table
                FinanceGLAccounts::where('GLTypeGroupID',$request->GLTypeGroupID)->update(['GLTypeGroupIDValue'=>$validated['value']]);
                //Update GlCode
                $this->insertGLCodes();
                activity()
                    ->causedBy(Auth::id())
                    ->performedOn(new FinanceGLTypeGroup())
                    ->withProperties(['action' => 'update'])
                    ->log('GL Sub Type Group updated segment value');
                DB::commit();
                return back()->with('success', 'GL Account Sub Type Segment updated successfully.');
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
            $update=FinanceGLSubAccountTypes::where('Id',$request->GLSubAccountTypeID)->update(['SegmentValue'=>$validated['value']]);
            //Update the Major GL Table
            FinanceGLAccounts::where('GLSubAccountTypeID',$request->GLSubAccountTypeID)->update(['GLSubAccountTypeIDValue'=>$validated['value']]);
            //Update GlCode
            $this->insertGLCodes();
            activity()
                ->causedBy(Auth::id())
                ->performedOn(new FinanceGLTypeGroup())
                ->withProperties(['action' => 'update'])
                ->log('GL Type Group updated segment value');
            DB::commit();
            return back()->with('success', 'GL Sub Account Type Segment updated successfully.');
        }catch(\Throwable $th){
            DB::rollBack();
            Log::error('Failed to update GL Sub Type Segment Value:' . $th->getMessage());
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function insertGLCodes(): void
    {
        // Pull segment order once (order by something deterministic)
        $segments = SegmentOrder::select('Id', 'SegmentType', 'Description')
            ->orderBy('Id')
            ->get();

        // Process accounts in chunks if table is large
        FinanceGLAccounts::query()->orderBy('Id')->chunkById(500, function ($accounts) use ($segments) {
            foreach ($accounts as $glAccount) {
                $parts = []; // reset per account

                foreach ($segments as $segment) {
                    if ($segment->SegmentType === 'GLDigits') {
                        // Pad account Id to the digits specified in segment Description (fallback to account->GLDigits if you keep it)
                        $digits = (int)($segment->Description ?? $glAccount->GLDigits ?? 1);
                        $digits = max($digits, 1);
                        $parts[] = str_pad((string)$glAccount->Id, $digits, '0', STR_PAD_LEFT);
                    } else {
                        // Use the segment name as a column on FinanceGLAccounts
                        $column = $segment->SegmentType;             // e.g. 'BranchCode', 'Major', etc.
                        $value = data_get($glAccount, $column, ''); // safe accessor
                        $parts[] = (string)$value;
                    }
                }

                // Join with dashes; drop empty parts
                $parts = array_values(array_filter($parts, fn($v) => $v !== null && $v !== ''));
                $glCode = implode('-', $parts);

                // Save the final GL code
                $glAccount->GLCode = $glCode;
                $glAccount->save();
            }
        });
    }


}
