@php
    use App\Enums\Employee\GenderEnum;
@endphp

<div>
    <form action="{{ route('users.update', [$user->UserID]) }}" method="post" id="updateUserForm">
        @csrf
        @method('put')

        <div class="mb-2">
            <label class="form-label" for="UserID">UserID <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="UserID" name="UserID" required
                   style="text-transform: uppercase;" placeholder="UserID"
                   value="{{ $user->UserID }}" readonly>
            <p id="UserID_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>

        <div class="mb-2">
            <label class="form-label" for="Name">Full Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="Name" name="Name" required
                   placeholder="Name" value="{{ $user->Name }}">
            <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>

        <div class="mb-2">
            <label class="form-label" for="Gender">Gender <span class="text-danger">*</span></label>
            <select class="form-control" name="Gender" id="Gender" required>
                <option disabled>Select Gender</option>
                @foreach(GenderEnum::cases() as $gender)
                    <option value="{{ $gender->value }}"
                        {{ $user->Gender === $gender->value ? 'selected' : '' }}>
                        {{ ucfirst(strtolower($gender->name)) }}
                    </option>
                @endforeach
            </select>
            <p id="Gender_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>

        <div class="mb-2">
            <label class="form-label" for="Branch">Branch <span class="text-danger">*</span></label>
            <select class="form-control" name="Branch" id="Branch" required>
                @foreach($branches as $branch)
                    <option value="{{ $branch->BranchID }}"
                        {{ ($branch->BranchID === $user->BranchId) ? 'selected' : '' }}>
                        {{ $branch->Name }}
                    </option>
                @endforeach
            </select>
            <p id="Branch_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>

        <div class="mb-2">
            <label class="form-label" for="Phone">Phone Number <span class="text-danger">*</span></label>
            <input type="tel" class="form-control" id="Phone" name="Phone"
                   placeholder="e.g., +12025550123"
                   pattern="^\+[1-9]\d{7,14}$" inputmode="tel" autocomplete="tel"
                   title="Use international format (E.164), e.g., +12025550123"
                   value="{{ $user->Phone }}" required>
            <small class="form-text text-muted">Use international format (E.164), e.g., +12025550123</small>
            <p id="Phone_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>

        <div class="mb-2">
            <label class="form-label" for="Email">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="Email" name="Email"
                   placeholder="Email" value="{{ $user->Email }}">
            <p id="Email_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>

        <div class="mb-2">
            <label class="form-label" for="Notes">Notes</label>
            <textarea name="Notes" id="Notes" rows="2" class="form-control">{{ $user->Notes }}</textarea>
            <p id="Notes_end_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>

        <hr>
        <div class="mt-4">
            <button type="button" class="btn btn-secondary float-start" onclick="window.bsOffcanvas.hide()">
                cancel
            </button>

            <button class="btn btn-primary float-end" id="updateUserBtn" type="submit">
                <i class="fas fa-save"></i> update {{ $user->UserID }}
            </button>
        </div>
    </form>
</div>

<script>
    $(function () {
        $('form#updateUserForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#updateUserBtn'), true, true, true)) {
                window.bsOffcanvas.hide();
            }
        });
    });
</script>
