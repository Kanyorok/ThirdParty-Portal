<div class="d-flex flex-column" style="height: 80%">
    <ul class="list-group list-group-flush">
        <li class="list-group-item"><b>Product ID </b><span class="float-end"> {{ $leadProduct->ProductID }} </span>
        </li>
        <li class="list-group-item"><b>Product </b><span class="float-end">{{ $leadProduct->ProductName }}</span></li>
    </ul>
    <hr class="mx-0 my-2">
    @include('snippets.lead_summary', ['lead'=>$lead])


    <hr class="mx-0 my-2">
    <p class="mb-1 h6">Notes</p>
    <p class="justify-content-around">
        {{ $leadProduct->Notes }}
    </p>
</div>
<div class="m-auto">
    @include('snippets.behind_scenes',['model'=>$leadProduct])
    <p class="mb-0">actions</p>
    <hr class="mt-0">
    <div class="form-buttons- row">
        <div class="col-md-6 col-12">
            <button class="btn btn-danger w-100 action-button"
                    onclick="triggerTrashProduct()"
                    type="button"><i
                    class="fas fa-trash-alt"></i> remove
            </button>
        </div>
        <div class="col-md-6 col-12">
            &nbsp;
        </div>
    </div>
</div>

<div class="modal fade" id="leadProductActionsModal" tabindex="-1" role="dialog" aria-hidden="true"
     data-bs-backdrop="false"
     data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Trash Lead Product {{ $leadProduct->ProductName}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content text-center">
                    <h4 class="text-danger">
                        Removed Product Interest <b>{{ $leadProduct->ProductName}}</b> ?
                    </h4>
                    <div class="mt-2 mb-2">
                        You are about to remove this product, confirm below ?
                    </div>
                    <hr>
                    <form id="deleteCallScheduleForm"
                          action="{{ route('lead-products.destroy',[$lead->LeadID, $leadProduct->Id])  }}"
                          method="post"> @csrf
                        <div class="mt-4">@method('delete')
                            <button type="button" class="btn btn-success float-start"
                                    data-bs-dismiss="modal">
                                no, keep
                            </button>
                            <button class="btn btn-danger float-end" id="deleteLeadProductBtn" type="submit"><i
                                    class="fas fa-trash"></i> yes, trash
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function triggerTrashProduct() {
        $("#leadProductActionsModal").modal('show');
    }

    $(function () {
        $('form#deleteCallScheduleForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#deleteLeadProductBtn'), false, true, true)) {
                $("#leadProductActionsModal").modal('hide');
                window.bsOffcanvas.hide();
                if (typeof fetchProductsTable === "function") {
                    fetchProductsTable();
                }
            }
        });
    });

</script>
