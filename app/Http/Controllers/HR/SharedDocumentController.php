<?php

namespace App\Http\Controllers\HR;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\RoleEnum;
use App\Enums\Core\VisibilityEnum;
use App\Helpers\SystemHelper;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\DMS\Document;
use App\Models\HR\Employee;
use App\Models\HR\JobRole;
use App\Models\HR\SharedDocument;
use App\Models\HR\SharedDocumentAcknowledgement;
use App\Models\HR\SharedDocumentCategory;
use App\Models\HRM\Department;
use App\Services\DMS\DocumentService;
use App\Services\DMS\RepositoryService;
use Illuminate\Http\Request;

class SharedDocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = SharedDocument::with(['category', 'ownerDepartment'])->orderByDesc('CreatedOn');

        if ($request->filled('category_id')) {
            $query->where('CategoryID', $request->category_id);
        }
        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }
        if ($request->filled('access')) {
            $query->where('AccessLevel', $request->access);
        }
        if ($request->filled('mandatory')) {
            $query->where('IsMandatory', (int)$request->mandatory);
        }

        $documents = $query->paginate(30);
        $categories = SharedDocumentCategory::orderBy('Name')->get();
        $accessLevels = ['Public', 'Department', 'Role'];
        $statusList = ['Draft', 'Published', 'Archived'];

        return view('hr.shared-docs.index', compact('documents', 'categories', 'accessLevels', 'statusList'));
    }

    public function create()
    {
        $categories = SharedDocumentCategory::orderBy('Name')->get();
        $departments = Department::orderBy('Name')->get(['Id', 'Name']);
        $roles = JobRole::orderBy('Name')->get(['Id', 'Name']);
        $accessLevels = ['Public', 'Department', 'Role'];

        return view('hr.shared-docs.create', compact('categories', 'departments', 'roles', 'accessLevels'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'CategoryID' => ['nullable', 'exists:t_HRSharedDocumentCategories,Id'],
            'Title' => ['required', 'string', 'max:200'],
            'Description' => ['nullable', 'string'],
            'Version' => ['nullable', 'string', 'max:20'],
            'EffectiveDate' => ['nullable', 'date'],
            'ExpiryDate' => ['nullable', 'date', 'after_or_equal:EffectiveDate'],
            'AcknowledgementDueOn' => ['nullable', 'date'],
            'OwnerDepartmentID' => ['nullable', 'exists:t_Departments,Id'],
            'AccessLevel' => ['required', 'in:Public,Department,Role'],
            'IsDownloadable' => ['sometimes', 'boolean'],
            'IsMandatory' => ['sometimes', 'boolean'],
            'Language' => ['nullable', 'string', 'max:30'],
            'Status' => ['nullable', 'string', 'max:30'],
            'DocumentFile' => ['nullable', 'file', 'max:5120'],
            'Departments' => ['array'],
            'Departments.*' => ['integer', 'exists:t_Departments,Id'],
            'Roles' => ['array'],
            'Roles.*' => ['integer', 'exists:t_HRJobRoles,Id'],
        ]);

        $data['IsDownloadable'] = $request->boolean('IsDownloadable', true);
        $data['IsMandatory'] = $request->boolean('IsMandatory', false);
        $data['Status'] = $data['Status'] ?? 'Draft';
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        $document = SharedDocument::create($data);

        $document->departments()->sync($request->input('Departments', []));
        $document->roles()->sync($request->input('Roles', []));

        $this->storeDocumentFile($document, $request);

        $employeeIds = $this->resolveTargetEmployeeIds(
            $document->AccessLevel,
            $request->input('Departments', []),
            $request->input('Roles', [])
        );

        $this->syncDocumentPermissions($document, $employeeIds);
        $this->syncAcknowledgements($document, $employeeIds);

        return redirect()->route('hr.shared-docs.index')
            ->with('success', 'Document saved.');
    }

    public function show($id)
    {
        if (! ctype_digit((string)$id)) {
            return redirect()->route('hr.shared-docs.categories.index');
        }

        $document = SharedDocument::with(['category', 'ownerDepartment', 'departments', 'roles', 'document'])->findOrFail($id);
        $acknowledgements = SharedDocumentAcknowledgement::with('employee')
            ->where('DocumentID', $document->Id)
            ->orderBy('Status')
            ->get();

        $employeeId = $this->resolveEmployeeId(auth()->user());
        $myAck = $employeeId
            ? SharedDocumentAcknowledgement::where('DocumentID', $document->Id)->where('EmployeeID', $employeeId)->first()
            : null;

        return view('hr.shared-docs.show', compact('document', 'acknowledgements', 'myAck'));
    }

    public function edit($id)
    {
        if (! ctype_digit((string)$id)) {
            return redirect()->route('hr.shared-docs.categories.index');
        }

        $document = SharedDocument::with(['departments', 'roles'])->findOrFail($id);
        $categories = SharedDocumentCategory::orderBy('Name')->get();
        $departments = Department::orderBy('Name')->get(['Id', 'Name']);
        $roles = JobRole::orderBy('Name')->get(['Id', 'Name']);
        $accessLevels = ['Public', 'Department', 'Role'];

        $selectedDepartments = $document->departments->pluck('Id')->all();
        $selectedRoles = $document->roles->pluck('Id')->all();

        return view('hr.shared-docs.edit', compact(
            'document',
            'categories',
            'departments',
            'roles',
            'accessLevels',
            'selectedDepartments',
            'selectedRoles'
        ));
    }

    public function update(Request $request, $id)
    {
        $document = SharedDocument::findOrFail($id);

        $data = $request->validate([
            'CategoryID' => ['nullable', 'exists:t_HRSharedDocumentCategories,Id'],
            'Title' => ['required', 'string', 'max:200'],
            'Description' => ['nullable', 'string'],
            'Version' => ['nullable', 'string', 'max:20'],
            'EffectiveDate' => ['nullable', 'date'],
            'ExpiryDate' => ['nullable', 'date', 'after_or_equal:EffectiveDate'],
            'AcknowledgementDueOn' => ['nullable', 'date'],
            'OwnerDepartmentID' => ['nullable', 'exists:t_Departments,Id'],
            'AccessLevel' => ['required', 'in:Public,Department,Role'],
            'IsDownloadable' => ['sometimes', 'boolean'],
            'IsMandatory' => ['sometimes', 'boolean'],
            'Language' => ['nullable', 'string', 'max:30'],
            'Status' => ['nullable', 'string', 'max:30'],
            'DocumentFile' => ['nullable', 'file', 'max:5120'],
            'Departments' => ['array'],
            'Departments.*' => ['integer', 'exists:t_Departments,Id'],
            'Roles' => ['array'],
            'Roles.*' => ['integer', 'exists:t_HRJobRoles,Id'],
        ]);

        $data['IsDownloadable'] = $request->boolean('IsDownloadable', true);
        $data['IsMandatory'] = $request->boolean('IsMandatory', false);
        $data['Status'] = $data['Status'] ?? $document->Status;
        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();

        $document->update($data);

        $document->departments()->sync($request->input('Departments', []));
        $document->roles()->sync($request->input('Roles', []));

        $this->storeDocumentFile($document, $request);

        $employeeIds = $this->resolveTargetEmployeeIds(
            $document->AccessLevel,
            $request->input('Departments', []),
            $request->input('Roles', [])
        );

        $this->syncDocumentPermissions($document, $employeeIds);
        $this->syncAcknowledgements($document, $employeeIds);

        return redirect()->route('hr.shared-docs.index')
            ->with('success', 'Document updated.');
    }

    public function acknowledge($id, Request $request)
    {
        $document = SharedDocument::findOrFail($id);
        $employeeId = $this->resolveEmployeeId(auth()->user());

        if (! $employeeId) {
            return back()->withErrors(['ack' => 'Unable to link your user to an employee record.']);
        }

        $ack = SharedDocumentAcknowledgement::firstOrNew([
            'DocumentID' => $document->Id,
            'EmployeeID' => $employeeId,
        ]);

        $ack->fill([
            'Status' => 'Acknowledged',
            'AcknowledgedOn' => now(),
            'AcknowledgedBy' => auth()->id(),
            'AcknowledgedIP' => $request->ip(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        if (! $ack->exists) {
            $ack->CreatedBy = auth()->id();
            $ack->CreatedOn = now();
            $ack->DueOn = $document->AcknowledgementDueOn ?? $document->EffectiveDate;
        }

        $ack->save();

        return back()->with('success', 'Document acknowledged.');
    }

    private function storeDocumentFile(SharedDocument $document, Request $request): void
    {
        if (! $request->hasFile('DocumentFile')) {
            return;
        }

        $doc = DocumentService::createUpload(
            RepositoryService::module(ModulesEnum::HRM),
            $request->file('DocumentFile'),
            auth()->user()
        )->document;

        $document->update(['DocumentId' => $doc->Id]);
    }

    private function resolveTargetEmployeeIds(string $accessLevel, array $departmentIds, array $roleIds): array
    {
        $query = Employee::query()->where('IsActive', 1);

        if ($accessLevel === 'Department') {
            if (empty($departmentIds)) {
                return [];
            }
            $query->whereIn('DepartmentID', $departmentIds);
        } elseif ($accessLevel === 'Role') {
            if (empty($roleIds)) {
                return [];
            }
            $query->whereIn('RoleID', $roleIds);
        }

        return $query->pluck('Id')->toArray();
    }

    private function syncAcknowledgements(SharedDocument $document, array $employeeIds): void
    {
        if (! $document->IsMandatory || empty($employeeIds)) {
            return;
        }

        $existing = SharedDocumentAcknowledgement::where('DocumentID', $document->Id)
            ->pluck('EmployeeID')
            ->toArray();

        $newIds = array_diff($employeeIds, $existing);
        if (empty($newIds)) {
            return;
        }

        $now = now();
        $dueOn = $document->AcknowledgementDueOn ?? $document->EffectiveDate;
        $rows = [];
        foreach ($newIds as $employeeId) {
            $rows[] = [
                'DocumentID' => $document->Id,
                'EmployeeID' => $employeeId,
                'Status' => 'Pending',
                'DueOn' => $dueOn,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => $now,
            ];
        }

        SharedDocumentAcknowledgement::insert($rows);
    }

    private function syncDocumentPermissions(SharedDocument $document, array $employeeIds): void
    {
        if (! $document->DocumentId) {
            return;
        }

        $doc = Document::find($document->DocumentId);
        if (! $doc) {
            return;
        }

        $system = SystemHelper::user();
        $service = new DocumentService($doc);

        if ($document->AccessLevel === 'Public') {
            $service->visibility(VisibilityEnum::Public, $system);

            return;
        }

        $service->visibility(VisibilityEnum::Private, $system);
        if (empty($employeeIds)) {
            return;
        }

        $users = User::whereIn('EmployeeId', $employeeIds)->get(['Id', 'UserID', 'Name', 'Email']);
        foreach ($users as $user) {
            try {
                $service->addPermission($user, RoleEnum::Read, $system, false);
            } catch (\Throwable $e) {
                // Ignore permission failures to avoid blocking document creation.
            }
        }
    }

    private function resolveEmployeeId(?User $user): ?int
    {
        if (! $user) {
            return null;
        }
        if ($user->EmployeeId) {
            return $user->EmployeeId;
        }

        if ($user->Email) {
            return Employee::where('Email', $user->Email)->value('Id');
        }

        return null;
    }
}
