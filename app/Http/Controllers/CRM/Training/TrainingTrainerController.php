<?php

namespace App\Http\Controllers\CRM\Training;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\CRM\Training\TrainingTrainer;
use Illuminate\Http\Request;

class TrainingTrainerController extends Controller
{
    public function index()
    {
        $trainers = TrainingTrainer::with('user')->orderBy('Name')->paginate(30);

        return view('crm.training.trainers.index', compact('trainers'));
    }

    public function create()
    {
        $users = User::orderBy('Name')->get(['Id', 'UserID', 'Name', 'Email', 'Phone']);

        return view('crm.training.trainers.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'TrainerType' => ['required', 'in:Internal,External'],
            'UserID' => ['nullable', 'exists:t_Users,Id'],
            'Name' => ['nullable', 'string', 'max:150'],
            'Email' => ['nullable', 'email', 'max:150'],
            'Phone' => ['nullable', 'string', 'max:50'],
            'Expertise' => ['nullable', 'string'],
            'Certifications' => ['nullable', 'string'],
            'Rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($data['TrainerType'] === 'Internal' && empty($data['UserID']) && empty($data['Name'])) {
            return back()->withErrors(['UserID' => 'Select a user or provide a trainer name for internal trainers.'])->withInput();
        }

        if ($data['TrainerType'] === 'External' && empty($data['Name'])) {
            return back()->withErrors(['Name' => 'Enter the trainer name for external trainers.'])->withInput();
        }

        if (! empty($data['UserID'])) {
            $user = User::find($data['UserID']);
            if ($user) {
                $data['Name'] = $data['Name'] ?: $user->Name;
                $data['Email'] = $data['Email'] ?: $user->Email;
                $data['Phone'] = $data['Phone'] ?: $user->Phone;
            }
        }

        $data['IsActive'] = 1;
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        TrainingTrainer::create($data);

        return redirect()->route('crm.training.trainers.index')
            ->with('success', 'Trainer added.');
    }

    public function edit($id)
    {
        $trainer = TrainingTrainer::findOrFail($id);
        $users = User::orderBy('Name')->get(['Id', 'UserID', 'Name', 'Email', 'Phone']);

        return view('crm.training.trainers.edit', compact('trainer', 'users'));
    }

    public function update(Request $request, $id)
    {
        $trainer = TrainingTrainer::findOrFail($id);

        $data = $request->validate([
            'TrainerType' => ['required', 'in:Internal,External'],
            'UserID' => ['nullable', 'exists:t_Users,Id'],
            'Name' => ['nullable', 'string', 'max:150'],
            'Email' => ['nullable', 'email', 'max:150'],
            'Phone' => ['nullable', 'string', 'max:50'],
            'Expertise' => ['nullable', 'string'],
            'Certifications' => ['nullable', 'string'],
            'Rate' => ['nullable', 'numeric', 'min:0'],
            'IsActive' => ['nullable', 'boolean'],
        ]);

        if ($data['TrainerType'] === 'Internal' && empty($data['UserID']) && empty($data['Name'])) {
            return back()->withErrors(['UserID' => 'Select a user or provide a trainer name for internal trainers.'])->withInput();
        }

        if ($data['TrainerType'] === 'External' && empty($data['Name'])) {
            return back()->withErrors(['Name' => 'Enter the trainer name for external trainers.'])->withInput();
        }

        if (! empty($data['UserID'])) {
            $user = User::find($data['UserID']);
            if ($user) {
                $data['Name'] = $data['Name'] ?: $user->Name;
                $data['Email'] = $data['Email'] ?: $user->Email;
                $data['Phone'] = $data['Phone'] ?: $user->Phone;
            }
        }

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $trainer->IsActive;
        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();

        $trainer->update($data);

        return redirect()->route('crm.training.trainers.index')
            ->with('success', 'Trainer updated.');
    }

    public function destroy($id)
    {
        $trainer = TrainingTrainer::findOrFail($id);
        $trainer->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('crm.training.trainers.index')
            ->with('success', 'Trainer deactivated.');
    }
}
