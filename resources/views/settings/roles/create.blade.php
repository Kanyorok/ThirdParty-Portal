<div>
    <form action="{{ route('roles.store') }}" method="post" id="createRoleForm"> @csrf
        <div class="col-12 mb-3">
            <label class="form-label" for="RoleName">Role Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="RoleName" name="RoleName" required placeholder="RoleName" value="{{ old('RoleName') }}">
            <p id="RoleName_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>

        <div class="col-12 mb-3">
            <label class="form-label" for="permSearch">Search modules</label>
            <input type="search" class="form-control" id="permSearch" placeholder="Type a module name e.g. Procurement, Inventory, DMS...">
        </div>

        <div class="d-flex gap-2 mb-3">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="expandAll">Expand all</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="collapseAll">Collapse all</button>
            <button type="button" class="btn btn-sm btn-outline-primary" id="selectVisible">Select all visible</button>
            <button type="button" class="btn btn-sm btn-outline-danger" id="clearVisible">Clear visible</button>
        </div>

        <p id="permissions_error" class="text-danger d-none error col-12" role="alert"></p>

        @php
            $moduleLabels = collect(\App\Enums\Core\ModulesEnum::cases())
                ->mapWithKeys(fn($m) => [$m->value => $m->description()]);
            $grouped = [];
            foreach (\App\Enums\Core\PermissionEnum::cases() as $perm) {
                $mod = $perm->module()->value;
                $section = $perm->title();
                $grouped[$mod] = $grouped[$mod] ?? [];
                $grouped[$mod][$section] = $grouped[$mod][$section] ?? [];
                $grouped[$mod][$section][] = $perm;
            }
            ksort($grouped);
        @endphp

        <div class="accordion" id="modulesAccordion">
            @foreach($grouped as $moduleId => $sections)
                @php $moduleName = $moduleLabels->get($moduleId, 'Module ' . $moduleId); @endphp
                <div class="card mb-2 module-card" data-module-name="{{ \Illuminate\Support\Str::lower($moduleName) }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <button class="btn btn-link text-start flex-grow-1 module-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#mod-{{ $moduleId }}" aria-expanded="false" aria-controls="mod-{{ $moduleId }}">
                            {{ $moduleName }}
                        </button>
                        <div class="form-check">
                            <input class="form-check-input select-all-module" type="checkbox" id="select-all-{{ $moduleId }}" data-target="#mod-{{ $moduleId }}">
                            <label class="form-check-label small" for="select-all-{{ $moduleId }}">Select all</label>
                        </div>
                    </div>
                    <div id="mod-{{ $moduleId }}" class="collapse" data-bs-parent="#modulesAccordion">
                        <div class="card-body">
                            <div class="accordion" id="sections-{{ $moduleId }}">
                                @foreach($sections as $sectionTitle => $perms)
                                    @php $secId = 'sec-' . $moduleId . '-' . \Illuminate\Support\Str::slug($sectionTitle, '-'); @endphp
                                    <div class="card mb-2 section-card" data-section-name="{{ \Illuminate\Support\Str::lower($sectionTitle) }}">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <button class="btn btn-sm btn-link text-start flex-grow-1" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $secId }}" aria-expanded="false" aria-controls="{{ $secId }}">
                                                {{ $sectionTitle }}
                                            </button>
                                            <div class="form-check">
                                                <input class="form-check-input select-all-section" type="checkbox" id="select-{{ $secId }}" data-target="#{{ $secId }}">
                                                <label class="form-check-label small" for="select-{{ $secId }}">Select all</label>
                                            </div>
                                        </div>
                                        <div id="{{ $secId }}" class="collapse" data-bs-parent="#sections-{{ $moduleId }}">
                                            <div class="card-body">
                                                <div class="row">
                                                    @foreach($perms as $permission)
                                                        <div class="col-sm-6 col-md-4 mb-3">
                                                            <div class="form-check form-switch mt-1">
                                                                <input class="form-check-input perm-checkbox" type="checkbox" id="{{ $permission->value }}" name="{{ $permission->value }}" {{ old($permission->value) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="{{ $permission->value }}">{{ $permission->subName() }}</label>
                                                            </div>
                                                            <p id="{{ $permission->value }}_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <hr>
        <div class="mt-4">
            <button type="button" class="btn btn-secondary float-start" onclick="window.bsOffcanvas.hide();">cancel</button>
            <button class="btn btn-primary float-end" id="createRoleBtn" type="submit"><i class="fas fa-save"></i> add role</button>
        </div>
    </form>
</div>
<script>
    $(function () {
        const $search = $('#permSearch');
        const normalize = (s) => (s||'').toString().trim().toLowerCase();

        $search.on('input', function(){
            const q = normalize($(this).val());
            $('.module-card').each(function(){
                const match = normalize($(this).data('module-name')).includes(q);
                $(this).toggle(match || q.length === 0);
                if (match && q.length > 0) {
                    const targetId = $(this).find('.collapse').attr('id');
                    if (targetId) {
                        const el = document.getElementById(targetId);
                        if (el && !$(el).hasClass('show')) {
                            try { new bootstrap.Collapse(el, { show: true }); } catch(e) {}
                        }
                    }
                }
            });
        });

        $(document).on('change', '.select-all-module', function(){
            const container = $($(this).data('target'));
            const checked = $(this).is(':checked');
            container.find('.perm-checkbox').prop('checked', checked);
            container.find('.select-all-section').prop('checked', checked);
        });

        $(document).on('change', '.select-all-section', function(){
            const container = $($(this).data('target'));
            const checked = $(this).is(':checked');
            container.find('.perm-checkbox').prop('checked', checked);
        });

        $search.on('input', function(){
            const q = normalize($(this).val());
            $('.module-card').each(function(){
                const $mod = $(this);
                const moduleMatch = normalize($mod.data('module-name')).includes(q);
                let anySectionMatch = false;

                $mod.find('.section-card').each(function(){
                    const $sec = $(this);
                    const secMatch = normalize($sec.data('section-name')).includes(q);
                    anySectionMatch = anySectionMatch || secMatch;
                    $sec.toggle(moduleMatch || secMatch || q.length === 0);

                    if (secMatch && q.length > 0) {
                        const cid = $sec.find('.collapse').attr('id');
                        if (cid) {
                            const el = document.getElementById(cid);
                            if (el && !$(el).hasClass('show')) {
                                try { new bootstrap.Collapse(el, { show: true }); } catch(e) {}
                            }
                        }
                    }
                });

                const showModule = moduleMatch || anySectionMatch || q.length === 0;
                $mod.toggle(showModule);
                if (showModule && q.length > 0) {
                    const mid = $mod.find('> .card-header + .collapse').attr('id');
                    if (mid) {
                        const mel = document.getElementById(mid);
                        if (mel && !$(mel).hasClass('show')) {
                            try { new bootstrap.Collapse(mel, { show: true }); } catch(e) {}
                        }
                    }
                }
            });
        });

        function setAllCollapses(selector, show) {
            $(selector).each(function(){
                const id = $(this).attr('id');
                if (!id) return;
                const el = document.getElementById(id);
                if (!el) return;
                const isShown = $(el).hasClass('show');
                if (show && !isShown) { try { new bootstrap.Collapse(el, { show: true }); } catch(e) {} }
                if (!show && isShown) { try { new bootstrap.Collapse(el, { toggle: true }); } catch(e) {} }
            });
        }

        $('#expandAll').on('click', function(){
            setAllCollapses('#modulesAccordion .collapse', true);
        });
        $('#collapseAll').on('click', function(){
            setAllCollapses('#modulesAccordion .collapse', false);
        });
        $('#selectVisible').on('click', function(){
            $('.module-card:visible .section-card:visible .perm-checkbox').prop('checked', true);
        });
        $('#clearVisible').on('click', function(){
            $('.module-card:visible .section-card:visible .perm-checkbox').prop('checked', false);
        });

        $('form#createRoleForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#createRoleBtn'), false, true, true)) {
                window.bsOffcanvas.hide();
                if (typeof fetchRolesTable === "function") {
                    fetchRolesTable();
                }
            }
        });
    });
</script>
