<?php

namespace App\Http\Controllers\CRM\Training;

use App\Http\Controllers\Controller;
use App\Models\BR\Client;
use App\Models\CRM\Training\TrainingCategory;
use App\Models\CRM\Training\TrainingProgram;
use App\Models\CRM\Training\TrainingProgramTarget;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrainingProgramController extends Controller
{
    public function index(Request $request)
    {
        $query = TrainingProgram::with('category');

        if ($request->filled('category_id')) {
            $query->where('CategoryID', $request->category_id);
        }
        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }
        if ($request->filled('mandatory')) {
            $query->where('IsMandatory', (int)$request->mandatory);
        }

        $programs = $query->orderBy('Title')->paginate(30);
        $categories = TrainingCategory::orderBy('Name')->get();
        $statusList = ['Active', 'Inactive'];

        return view('crm.training.programs.index', compact('programs', 'categories', 'statusList'));
    }

    public function create()
    {
        $categories = TrainingCategory::orderBy('Name')->get();
        $clients = Client::query()->orderBy('Name')->limit(2000)->get(['ClientID', 'Name']);
        $deliveryModes = ['Classroom', 'Online', 'Blended'];

        return view('crm.training.programs.create', compact('categories', 'clients', 'deliveryModes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:30', 'unique:t_CRMTrainingPrograms,Code'],
            'Title' => ['required', 'string', 'max:200'],
            'CategoryID' => ['nullable', 'exists:t_CRMTrainingCategories,Id'],
            'DeliveryMode' => ['nullable', 'string', 'max:50'],
            'DurationHours' => ['nullable', 'numeric', 'min:0'],
            'Objectives' => ['nullable', 'string'],
            'TargetAudience' => ['nullable', 'string'],
            'BudgetedCost' => ['nullable', 'numeric', 'min:0'],
            'ActualCost' => ['nullable', 'numeric', 'min:0'],
            'IsMandatory' => ['sometimes', 'boolean'],
            'HasCertification' => ['sometimes', 'boolean'],
            'Status' => ['nullable', 'string', 'max:30'],
            'TargetClients' => ['array'],
            'TargetClients.*' => ['string', 'max:50'],
        ]);

        $data['IsMandatory'] = $request->boolean('IsMandatory', false);
        $data['HasCertification'] = $request->boolean('HasCertification', false);
        $data['Status'] = $data['Status'] ?? 'Active';
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        $program = TrainingProgram::create($data);

        $this->syncTargets($program, $request);

        return redirect()->route('crm.training.programs.index')
            ->with('success', 'Training program created.');
    }

    public function show($id)
    {
        $program = TrainingProgram::with(['category', 'targets', 'sessions'])->findOrFail($id);
        $targetClientIds = $program->targets->where('TargetType', 'Client')->pluck('TargetID')->map(fn ($id) => (string)$id)->all();

        $targetNames = [
            'clients' => $targetClientIds
                ? Client::query()->whereIn('ClientID', $targetClientIds)->pluck('Name')->implode(', ')
                : '',
        ];

        return view('crm.training.programs.show', compact('program', 'targetNames'));
    }

    public function edit($id)
    {
        $program = TrainingProgram::with('targets')->findOrFail($id);
        $categories = TrainingCategory::orderBy('Name')->get();
        $clients = Client::query()->orderBy('Name')->limit(2000)->get(['ClientID', 'Name']);
        $deliveryModes = ['Classroom', 'Online', 'Blended'];

        $targetClients = $program->targets->where('TargetType', 'Client')->pluck('TargetID')->map(fn ($id) => (string)$id)->all();

        return view('crm.training.programs.edit', compact(
            'program',
            'categories',
            'clients',
            'deliveryModes',
            'targetClients'
        ));
    }

    public function update(Request $request, $id)
    {
        $program = TrainingProgram::findOrFail($id);

        $data = $request->validate([
            'Code' => ['required', 'string', 'max:30', Rule::unique('t_CRMTrainingPrograms', 'Code')->ignore($program->Id, 'Id')],
            'Title' => ['required', 'string', 'max:200'],
            'CategoryID' => ['nullable', 'exists:t_CRMTrainingCategories,Id'],
            'DeliveryMode' => ['nullable', 'string', 'max:50'],
            'DurationHours' => ['nullable', 'numeric', 'min:0'],
            'Objectives' => ['nullable', 'string'],
            'TargetAudience' => ['nullable', 'string'],
            'BudgetedCost' => ['nullable', 'numeric', 'min:0'],
            'ActualCost' => ['nullable', 'numeric', 'min:0'],
            'IsMandatory' => ['sometimes', 'boolean'],
            'HasCertification' => ['sometimes', 'boolean'],
            'Status' => ['nullable', 'string', 'max:30'],
            'TargetClients' => ['array'],
            'TargetClients.*' => ['string', 'max:50'],
        ]);

        $data['IsMandatory'] = $request->boolean('IsMandatory', false);
        $data['HasCertification'] = $request->boolean('HasCertification', false);
        $data['Status'] = $data['Status'] ?? $program->Status;
        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();

        $program->update($data);
        $this->syncTargets($program, $request, true);

        return redirect()->route('crm.training.programs.index')
            ->with('success', 'Training program updated.');
    }

    private function syncTargets(TrainingProgram $program, Request $request, bool $refresh = false): void
    {
        if ($refresh) {
            TrainingProgramTarget::where('ProgramID', $program->Id)->delete();
        }

        $targetClientIds = collect((array)$request->input('TargetClients', []))
            ->map(fn ($id) => trim((string)$id))
            ->filter()
            ->unique()
            ->values();

        if ($targetClientIds->isEmpty()) {
            return;
        }

        $validClientIds = Client::query()
            ->whereIn('ClientID', $targetClientIds->all())
            ->pluck('ClientID')
            ->map(fn ($id) => (string)$id)
            ->unique()
            ->values();

        if ($validClientIds->isEmpty()) {
            return;
        }

        $now = now();
        $insert = $validClientIds->map(function (string $clientId) use ($program, $now) {
            return [
                'ProgramID' => $program->Id,
                'TargetType' => 'Client',
                'TargetID' => $clientId,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => $now,
            ];
        })->all();

        TrainingProgramTarget::insert($insert);
    }
}
