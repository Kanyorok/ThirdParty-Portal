<?php

namespace App\Http\Controllers\DMS\Tags;

use App\Http\Controllers\Controller;
use App\Http\Requests\DMS\TaggingRuleRequest;
use App\Models\DMS\DMSTags;
use App\Models\DMS\DocumentTaggingRules;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\DataTables;

class TaggingRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(DMSTags $dMSTags): JsonResponse
    {
        try {
            return Datatables::of($dMSTags->rules()->select('*'))->addIndexColumn()
                ->addColumn('action', function (DocumentTaggingRules $rule) use ($dMSTags) {
                    return '<button type="button" data-click_url="' . route('tagging-rules.destroy', [$dMSTags->TagID, $rule->Id]) . '"
                     data-info="When ' . $rule->Content->description() . ' ' . $rule->Comparison->description() . ' ' . $rule->Value . '"
                    class="btn btn-danger btn-sm modal-trash-rule"><i class="fas fa-trash-alt"></i></button>';
                })->editColumn('CreatedOn', function (DocumentTaggingRules $rule) {
                    return $rule->CreatedOn?->format('d M, Y H:i');
                })->editColumn('Content', function (DocumentTaggingRules $rule) {
                    return $rule->Content->description();
                })->editColumn('Comparison', function (DocumentTaggingRules $rule) {
                    return $rule->Comparison->description();
                })->editColumn('Value', function (DocumentTaggingRules $rule) {
                    if (strlen($rule->Value) > 70) {
                        return '<span title="' . $rule->Value . '">' . substr($rule->Value, 0, 70) . '...</span>';
                    }

                    return $rule->Value;
                })->rawColumns(['Value', 'action'])->make();
        } catch (Exception $e) {
            return $this->errored('an error occurred fetching related documents');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaggingRuleRequest $request, DMSTags $dMSTags): JsonResponse
    {
        $content = $request->getContentType();
        $comparison = $request->getComparisonType();
        $actor = $request->user();

        try {
            return DB::transaction(function () use ($request, $dMSTags, $content, $comparison, $actor) {
                $rule = DocumentTaggingRules::create([
                    'TagId' => $dMSTags->Id,
                    "Content" => $content->value,
                    "Comparison" => $comparison->value,
                    "Value" => $request->validated('Value'),
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]);

                activity()->causedBy($actor)->performedOn($rule)->event('create')->log('Created tagging rule : ' . $rule->Value);

                return $this->succeeded('Tagging rule created successfully');
            });
        } catch (Throwable | Exception $e) {
            Log::error('Error creating tagging rule: ' . $e);
        }

        return $this->errored('an error occurred while creating tagging rule');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(DMSTags $dMSTags): View
    {
        return view('dms.tags.rules.create', ['tag' => $dMSTags]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, DMSTags $dMSTags, DocumentTaggingRules $documentTaggingRules)
    {
        if ($dMSTags->Id !== $documentTaggingRules->TagId) {
            return $this->errored('Invalid request');
        }

        $actor = $request->user();

        try {
            return DB::transaction(function () use ($documentTaggingRules, $actor, $request) {
                $documentTaggingRules->forceFill([
                    'DeletedOn' => now(),
                    'DeletedBy' => $request->user()->Id,
                ])->save();

                activity()->causedBy($actor)->performedOn($documentTaggingRules)->event('delete')->log('trashed tagging  rule : ' . $documentTaggingRules->Value);

                return $this->succeeded('tagging  rule deleted');
            });
        } catch (Throwable | Exception $e) {
            Log::error('trash tagging  rule : ' . $e);

            return $this->errored('an unexpected error occurred');
        }
    }
}
