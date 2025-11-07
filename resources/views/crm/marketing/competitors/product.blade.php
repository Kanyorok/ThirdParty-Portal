<div>
    <div class="row">
        <div class="col-12 col-md-4 text-center">
            {!! $competitor->getImage('alt=".." class="img-fluid me-2"',true) !!}
        </div>
        <div class="col-12 col-md-8">
            <h3>{{ $competitor->CompetitorName }}</h3>
            <p><a href="{{ ($competitor->Website)??'#' }}">{{ ($competitor->Website)??'www.' }}</a></p>
            <p>{{ ($competitor->Phone) }}
                <span class="float-end">{{ $competitor->Email }}</span>
            </p>
        </div>
    </div>
    <hr>
    <h2 class="text-center text-decoration-underline h2">{{ $product->Name }}</h2>
    <ul class="list-unstyled mb-0">
        <li class="mb-3 fs-4"><b>Clients</b> <span class="float-end">{{ number_format($product->Clients) }}</span></li>
        <li class="mb-3 fs-4"><b>Limit</b> <span class="float-end">{{ number_format($product->Limit,2) }}</span></li>
        <li class="mb-3"><b>Interest Rate</b> <span
                class="float-end">{{ number_format($product->InterestRate,2) }} % </span></li>
        <li class="mb-3"><b>Repayment Period</b> <span class="float-end">{{ $product->RepaymentPeriod }} Weeks </span>
        </li>
        <li class="mb-3"><b>Other Charges</b> <span
                class="float-end">{{ number_format($product->OtherCharges,2) }}</span></li>
        <li class="mb-3"><b>Security Required</b> <span class="float-end">{{ $product->SecurityRequired }}</span></li>
    </ul>
    <p class="mb-3">Notes: <i class="align-middle" data-feather="info"></i> <br>
        <span style="text-align: justify">{{ $product->Notes }}</span>
    </p>

    @include('snippets.behind_scenes',['model'=>$product])
    <p class="mb-0">actions</p>
    <hr class="mt-0">
    <div class="form-buttons- row">
        <div class="col-md-6 col-12">
            <button class="btn btn-danger w-100 delete-product-modal btn-sm" type="button"><i
                    class="fas fa-trash-alt"></i> trash
            </button>
        </div>
        <div class="col-md-6 col-12">
            <button class="btn btn-success w-100 update-product-modal btn-sm" type="button"><i
                    class="fas  fa-edit"></i> update
            </button>
        </div>
    </div>
</div>
<div class="modal fade" id="CompetitorProductActionsModal" data-bs-backdrop="false" tabindex="-1" role="dialog"
     aria-hidden="true" style="z-index: 2000;">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">..</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content with-gradient d-none modal-item" id="UpdateProductModal">
                    <form action="{{ route('competitor-products.update',[$competitor->CompetitorID,$product->Id]) }}"
                          method="post" id="UpdateProductForm">
                        @method('put')
                        <div class="mb-3"> @csrf
                            <label class="form-label" for="Name">Name <span class="text-danger">*</span></label>
                            <input type="text" id="Name" name="Name" required class="form-control"
                                   value="{{ $product->Name }}">
                            <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="Limit">Limit <span class="text-danger">*</span></label>
                            <input type="number" id="Limit" name="Limit" required class="form-control"
                                   value="{{ $product->Limit }}">
                            <p id="Limit_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class=" mb-3">
                            <label class="form-label" for="InterestRate">Interest Rate <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" id="InterestRate" name="InterestRate" required step="0.01" min="0"
                                       max="100" class="form-control" value="{{ $product->InterestRate }}">
                                <span class="input-group-text">%</span></div>
                            <p id="InterestRate_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="OtherCharges">Other Charges <span
                                    class="text-danger">*</span></label>
                            <input type="number" id="OtherCharges" name="OtherCharges" required class="form-control"
                                   min="0" value="{{ $product->OtherCharges }}">
                            <p id="OtherCharges_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class=" mb-3">
                            <label class="form-label" for="RepaymentPeriod">Repayment Period <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" id="RepaymentPeriod" name="RepaymentPeriod" required step="1"
                                       min="1" value="{{ $product->RepaymentPeriod }}" class="form-control">
                                <span class="input-group-text">Weeks</span></div>
                            <p id="RepaymentPeriod_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="SecurityRequired">Security Required </label>
                            <input type="text" id="SecurityRequired" name="SecurityRequired" class="form-control"
                                   value="{{ $product->SecurityRequired }}">
                            <p id="SecurityRequired_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="Clients"> Clients <span class="text-danger">*</span></label>
                            <input type="number" id="Clients" name="Clients" required class="form-control" min="0"
                                   value="{{ $product->Clients }}">
                            <p id="Clients_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="Notes">Notes </label>
                            <textarea name="Notes" id="Notes" class="form-control" rows="2"
                                      maxlength="5000">{{ $product->Notes }}</textarea>
                            <p id="Notes_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <hr>
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary float-start"
                                    data-bs-dismiss="modal">
                                cancel
                            </button>
                            <button class="btn btn-primary float-end" id="UpdateProductBtn" type="submit"><i
                                    class="fas fa-save"></i> update product
                            </button>
                        </div>
                    </form>
                </div>
                <div class="onboarding-content with-gradient d-none modal-item text-center" id="trashProductModal">
                    <h4 class="text-danger">
                        Trash Competitor Product <b>{{ $product->Name }}</b> ?
                    </h4>

                    <form id="trashProductForm" method="post"
                          action="{{ route('competitor-products.destroy',[$competitor->CompetitorID,$product->Id]) }}"> @csrf @method('delete')
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary float-start"
                                    data-bs-dismiss="modal">
                                no, cancel
                            </button>
                            <button class="btn btn-danger float-end" id="trashProductBtn"
                                    type="submit"><i
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
    $(function () {
        $(document).on('click', '.update-product-modal', function () {

            $(".modal-item").addClass('d-none');
            $('#UpdateProductModal').removeClass('d-none');
            $('.modal-title').html('update a product - {{ $product->name }}.');
            $("#CompetitorProductActionsModal").modal('show');
        });

        $('form#UpdateProductForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#UpdateProductBtn'), false, true, true)) {
                window.bsOffcanvas.hide();
                $("#CompetitorProductActionsModal").modal('hide');
                fetchCompetitorProducts();
                window.setTimeout(function () {
                    showOffCanvasMain('Product Details', '{{ route('competitor-products.show', [$competitor->CompetitorID,$product->Id]) }}');
                }, 1000);
            }
        });

        $(document).on('click', '.delete-product-modal', function () {
            $(".modal-item").addClass('d-none');
            $('.modal-title').html('Trash {{ $product->Name }}');
            $('#trashProductModal').removeClass('d-none');
            $("#CompetitorProductActionsModal").modal('show');
        });
        $('form#trashProductForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#trashProductBtn'), false, true, true)) {
                window.bsOffcanvas.hide();
                $("#CompetitorProductActionsModal").modal('hide');
                fetchCompetitorProducts();
            }
        });
    });

</script>
