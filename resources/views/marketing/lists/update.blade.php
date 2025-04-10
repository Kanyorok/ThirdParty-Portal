<div>
    @if($party instanceof \App\Models\BR\Client)
        @include('snippets.client_summary', ['client'=>$party])
        @php $updateUrl = route('client-marketing-lists.store',[$party->ClientID]); @endphp
    @elseif($party instanceof \App\Models\Lead)
        @include('snippets.lead_summary', ['lead'=>$party])
        @php $updateUrl = route('lead-marketing-lists.store',$party->LeadID); @endphp
    @else
        <h3>Unknown party</h3>
        @php $updateUrl = ''; @endphp
    @endif
    <hr class="mx-0 my-2">
    <form action="{{ $updateUrl }}" method="post" id="updateMarketingListForm"> @csrf
        <div class="mb-3">
            <label for="MarketingList" class="form-label">Marketing List </label>
            <select class="form-control-lg" name="MarketingList[]" id="MarketingList" multiple>
                @foreach($MarketingLists as $MarketingList)
                    <option value="{{ $MarketingList->slug }}"
                        {{ (in_array($MarketingList->slug,$MarketingListMember,true))?'selected':'' }}
                    >{{ $MarketingList->Label }}</option>
                @endforeach
            </select>
            <p id="MarketingList_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <hr>
        <div class="mt-4">
            <button class="btn btn-primary float-end" id="updateMarketingListBtn" type="submit"><i
                    class="fas fa-save"></i>
                update Lists
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('#MarketingList').select2();
        $('form#updateMarketingListForm').submit(async function (e) {
            e.preventDefault();
            @if(empty($updateUrl))
            nError('unknown party.');
            window.bsOffcanvas.hide();
            @else
            if (await saveForm($(this), $('#updateMarketingListBtn'), true, true, true, true)) {
                window.bsOffcanvas.hide();
            }
            @endif
        });
    });

</script>
