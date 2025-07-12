@php use App\Enums\Core\VisibilityEnum;use App\Enums\DMS\ContentEnum;use App\Enums\DMS\StringComparisonEnum; @endphp
<div>
    <form action="{{ route('tagging-rules.store', [$tag->TagID]) }}" method="post" class="row"
          id="createTaggingRuleForm">
        @csrf

        <div class="col-4 mb-3">
            <label class="form-label" for="Content">When <span class="text-danger">*</span></label>
        </div>
        <div class="col-8 mb-3">
            <select class="form-control w-100" name="Content" id="Content" required>
                @foreach(ContentEnum::cases() as $content)
                    <option value="{{ $content->value }}">{{ $content->description() }}</option>
                @endforeach
            </select>
            <p id="Content_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>

        <div class="mb-3 col-12">
            {{--   <label class="form-label" for="Visibility">Visibility <span class="text-danger">*</span></label>--}}
            <select class="form-control" name="Comparison" id="Comparison" required>
                @foreach(StringComparisonEnum::cases() as $comparison)
                    <option value="{{ $comparison->value }}"> {{ $comparison->description() }}</option>
                @endforeach
            </select>
            <p id="Comparison_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <textarea name="Value" id="Value" rows="2" class="form-control" required
                      maxlength="250" placeholder="Content"></textarea>
            <p id="Value_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <hr>
        <div class="mt-4">
            <button type="button" class="btn btn-secondary float-start"
                    data-bs-dismiss="modal">
                cancel
            </button>
            <button class="btn btn-primary float-end" id="createTaggingRuleBtn" type="submit"><i
                    class="fas fa-save"></i> add a rule
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('form#createTaggingRuleForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#createTaggingRuleBtn'), false, true, true)) {
                window.bsOffcanvas.hide();
                if (typeof fetchTaggingRulesTable === "function") {
                    fetchTaggingRulesTable();
                }
            }
        });
    });

</script>
