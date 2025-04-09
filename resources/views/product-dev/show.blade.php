@php use App\Enums\Core\ExtensionsEnum; @endphp
@extends('layouts.app')

@section('title')
    {{ \Illuminate\Support\Str::upper($product->ProductID) }}
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/summernote/summernote-bs5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/dropzone/dropzone.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }

        .updatable-field {
            cursor: pointer;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-8 col-xxl-9">
            <div class="card">
                <div class="card-body pb-0">
                    <div class="row">
                        <div class="col-9">
                            <h3 class="updatable-field set-Name" id="NameContent"
                                ondblclick="editField('Name','input')">{{ $product->Name }}</h3>
                            <input type="text" class="form-control d-none" id="Name" name="Name"
                                   value="{{ $product->Name }}">
                        </div>
                        <div class="col-3">
                            <a href="javascript:void(0);" onclick="editField('Name','input')" class="mx-1 d-none"
                               id="NameBtn"><i
                                    class="fas fa-edit"></i> </a>
                            <a href="javascript:void(0);" onclick="cancelFieldEdit('Name','input')" class="d-none mx-1"
                               id="NameCancelBtn"><i class="fas fa-times"></i> </a>
                        </div>
                        <div class="col-12"><p id="Name_error" class="text-danger d-none error col-12" role="alert"></p>
                        </div>
                    </div>
                    <div class="tab m-0">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item text-decoration-underline fw-bold fs-5">
                                <a class="nav-link active" href="#tab-Justification" data-bs-toggle="tab"
                                   role="tab" aria-selected="true">Justification</a>
                            </li>
                            <li class="nav-item text-decoration-underline fw-bold fs-5">
                                <a class="nav-link" href="#tab-Regulatory" data-bs-toggle="tab" role="tab"
                                   aria-selected="false">Regulatory</a>
                            </li>
                            <li class="nav-item text-decoration-underline fw-bold fs-5">
                                <a class="nav-link" href="#tab-Risk" data-bs-toggle="tab" role="tab"
                                   aria-selected="false">Risk Assessment</a>
                            </li>
                        </ul>
                        <div style="border-radius: unset;box-shadow: none;background: inherit;" class="tab-content p-0">
                            <div class="tab-pane active" id="tab-Justification" role="tabpanel">
                                <h4 class="tab-title">Justification
                                    <div class="float-end">
                                        <a href="javascript:void(0);" onclick="editField('Justification','summernote')"
                                           class="mx-1 d-none" id="JustificationBtn"><i class="fas fa-edit"></i> </a>
                                        <a href="javascript:void(0);"
                                           onclick="cancelFieldEdit('Justification','summernote')" class="d-none mx-1"
                                           id="JustificationCancelBtn"><i class="fas fa-times"></i> </a>
                                    </div>
                                </h4>
                                <div class="mb-2">
                                    <p id="Justification_error" class="text-danger d-none error col-12"
                                       role="alert"></p>
                                    <div class="updatable-field set-Justification"
                                         ondblclick="editField('Justification','summernote')" id="JustificationContent"
                                         style="min-height: 100px; max-height: 500px; overflow-y: scroll;">
                                        {!! ($product->Justification)??"<p class='my-2 text-center'>Not set create</p>" !!}
                                    </div>
                                    <textarea name="Justification" id="Justification" rows="3"
                                              class="form-control d-none"> {!! $product->Justification !!}</textarea>
                                </div>
                            </div>
                            <div class="tab-pane" id="tab-Regulatory" role="tabpanel">
                                <h4 class="tab-title">Regulatory
                                    <div class="float-end">
                                        <a href="javascript:void(0);" onclick="editField('Regulatory','summernote')"
                                           class="mx-1 d-none" id="RegulatoryBtn"><i class="fas fa-edit"></i> </a>
                                        <a href="javascript:void(0);"
                                           onclick="cancelFieldEdit('Regulatory','summernote')" class="d-none mx-1"
                                           id="RegulatoryCancelBtn"><i class="fas fa-times"></i> </a>
                                    </div>
                                </h4>
                                <div class="mb-2">
                                    <p id="Regulatory_error" class="text-danger d-none error col-12" role="alert"></p>
                                    <div class="updatable-field set-Regulatory" id="RegulatoryContent"
                                         ondblclick="editField('Regulatory','summernote')"
                                         style="min-height: 100px; max-height: 500px; overflow-y: scroll;">
                                        {!! ($product->Regulatory)??"<p class='my-2 text-center'>Not set create</p>" !!}
                                    </div>
                                    <textarea name="Regulatory" id="Regulatory" rows="3"
                                              class="form-control d-none"> {!! $product->Regulatory !!}</textarea>
                                </div>
                            </div>
                            <div class="tab-pane" id="tab-Risk" role="tabpanel">
                                <div class="row">
                                    <div class="col-sm-6 border-1 border-end">
                                        <h4 class="tab-title">Risks
                                            <div class="float-end">
                                                <a href="javascript:void(0);" onclick="editField('Risks','summernote')"
                                                   class="mx-1 d-none" id="RisksBtn"><i class="fas fa-edit"></i> </a>
                                                <a href="javascript:void(0);"
                                                   onclick="cancelFieldEdit('Risks','summernote')" class="d-none mx-1"
                                                   id="RisksCancelBtn"><i class="fas fa-times"></i> </a>
                                            </div>
                                        </h4>
                                        <p id="Risks_error" class="text-danger d-none error col-12" role="alert"></p>
                                        <div class="updatable-field set-Risks" id="RisksContent"
                                             ondblclick="editField('Risks','summernote')"
                                             style="min-height: 100px; max-height: 500px; overflow-y: scroll;">
                                            {!! ($product->Risks)??"<p class='my-2 text-center'>Not set create</p>" !!}
                                        </div>
                                        <textarea name="Risks" id="Risks" rows="3"
                                                  class="form-control d-none"> {!! $product->Risks !!}</textarea>
                                    </div>
                                    <div class="col-sm-6">
                                        <h4 class="tab-title">Management Strategies
                                            <div class="float-end">
                                                <a href="javascript:void(0);"
                                                   onclick="editField('RiskStrategies','summernote')"
                                                   class="mx-1 d-none"
                                                   id="RiskStrategiesBtn"><i class="fas fa-edit"></i> </a>
                                                <a href="javascript:void(0);"
                                                   onclick="cancelFieldEdit('RiskStrategies','summernote')"
                                                   class="d-none mx-1" id="RiskStrategiesCancelBtn"><i
                                                        class="fas fa-times"></i> </a>
                                            </div>
                                        </h4>
                                        <p id="RiskStrategies_error" class="text-danger d-none error col-12"
                                           role="alert"></p>
                                        <div class="updatable-field set-RiskStrategies" id="RiskStrategiesContent"
                                             ondblclick="editField('RiskStrategies','summernote')"
                                             style="min-height: 100px; max-height: 500px; overflow-y: scroll;">
                                            {!! ($product->RiskStrategies)??"<p class='my-2 text-center'>Not set create</p>" !!}
                                        </div>
                                        <textarea name="RiskStrategies" id="RiskStrategies" rows="3"
                                                  class="form-control d-none"> {!! $product->RiskStrategies !!}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer" id="productsAttachmentContents">
                    @if($canUpdate)
                        <button type="button" class="btn btn-secondary m-2" id="action-file-upload"><i
                                class="fas fa-cloud-upload"></i>&nbsp; upload files
                        </button>
                    @endif
                    @foreach($product->documents()->get(['ImageID','MIMEType','Name']) as $document)
                        {!! $document?->service()->summaryList() !!}
                    @endforeach
                </div>
            </div>

            <div class="card d-none" id="uploadCard">
                <div class="card-header pb-1 ">
                    <h3 class="card-title">Upload Document
                        <span class="float-end" style="cursor: pointer;" id="uploadCardClose"><i
                                class="fas fa-times"></i></span>
                    </h3>
                </div>
                <div class="card-body p-0 border border-top">
                    <form action="{{ route('product-development.upload',[$product->ProductID]) }}" class="dropzone"
                          id="upload-form">@csrf</form>
                </div>
            </div>

            <div class="card">
                <div class="card-header p-0">
                    <div class="nav nav-pills card-header py-2">
                        <ul class="nav" role="tablist">
                            <li class="nav-item"><a class="nav-link active" href="#tab-comments"
                                                    data-bs-toggle="tab" role="tab" aria-selected="false"
                                >comments</a></li>
                            <li class="nav-item"><a class="nav-link" href="#tab-activities" data-bs-toggle="tab"
                                                    role="tab" aria-selected="false" onclick="fetchActivitiesTable()"
                                >activities</a></li>
                            <li class="nav-item"><a class="nav-link" href="#tab-workflow" data-bs-toggle="tab"
                                                    role="tab" aria-selected="false" onclick="fetchWorkflowTable()"
                                >workflows</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="tab-content p-0">
                        <div class="tab-pane m-2 active show" id="tab-comments" role="tabpanel">
                            @if(is_null($product->CommentStart))
                                <div class="alert alert-info my-4 px-2" role="alert">
                                    <div class="alert-icon">
                                        <i class="far fa-fw fa-bell"></i>
                                    </div>
                                    <div class="alert-message">
                                        Commenting is not enabled. Click here to <a href="javascript:void(0);"
                                                                                    class="enable-commenting-modal">enable
                                            it</a>.
                                    </div>
                                </div>
                            @else
                                <div class="pb-1 mb-1 border-bottom">
                                    Comments
                                    @if($canComment)
                                        <span class="float-end">
                                          <button class="btn btn-primary btn-sm new-comment" data-parent="comments"
                                                  data-route="{{ route('product-development-comment.store',[$product->ProductID]) }}"
                                                  data-title="new comment" type="button">
                                        <i class="fas fa-plus-circle"></i> new comment
                                            </button>
                                        </span>
                                    @endif
                                </div>
                                <div id="comments" class="px-2 pt-0 w-100 comments" style="max-height: 100vh"
                                     data-url="{{ route('product-development-comment.index',[$product->ProductID]) }}"></div>
                                <div class="d-grid text-center" id="commentsMessage"></div>
                            @endif
                        </div>
                        <div class="tab-pane m-2" id="tab-workflow" role="tabpanel">
                            <table id="productWorkflowTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Stage</th>
                                    <th>Status</th>
                                    <th>Dated</th>
                                    <th>By</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="tab-pane m-2" id="tab-activities" role="tabpanel">
                            <table id="productActivitiesTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Event</th>
                                    <th>Description</th>
                                    <th>By</th>
                                    <th>Dated</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-xxl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-center">
                        <h3 class="fw-bold">{{ \Illuminate\Support\Str::upper($product->ProductID) }}</h3>
                    </div>
                    <ul class="list-group list-group-flush ">
                        <li class="list-group-item px-0">Stage :
                            <a href="javascript:void(0);" onclick="editField('StageId','select')" id="StageIdBtn"
                               class="d-none"><i
                                    class="fas fa-edit"></i> </a>
                            <a href="javascript:void(0);" onclick="cancelFieldEdit('StageId','select')"
                               class="d-none mx-2" id="StageIdCancelBtn"><i class="fas fa-times"></i> </a>
                            <span class="h5 float-end updatable-field set-StageId"
                                  ondblclick="editField('StageId','select')"
                                  id="StageIdContent">{{ $product?->stage->Description }}</span>
                            <select class="form-control d-none" id="StageId" name="StageId">
                                @foreach($stages as $stage)
                                    <option
                                        {{ ($product->StageId===$stage->ID)?'selected':'' }} value="{{ $stage->ID }}">{{ $stage->Description }}</option>
                                @endforeach
                            </select>
                            <p id="StageId_error" class="text-danger d-none error col-12" role="alert"></p>
                        </li>
                        <li class="list-group-item px-0">Target Group :
                            <a href="javascript:void(0);" onclick="editField('TargetGroup','input')" id="TargetGroupBtn"
                               class="d-none"><i
                                    class="fas fa-edit"></i> </a>
                            <a href="javascript:void(0);" onclick="cancelFieldEdit('TargetGroup','input')"
                               class="d-none mx-2" id="TargetGroupCancelBtn"><i class="fas fa-times"></i> </a>
                            <span class="h5 float-end updatable-field set-TargetGroup"
                                  ondblclick="editField('TargetGroup','input')"
                                  id="TargetGroupContent">{{ $product->TargetGroup }}</span>
                            <input type="text" class="form-control d-none" id="TargetGroup" name="TargetGroup"
                                   value="{{ ($product->TargetGroup)??0 }}">
                            <p id="TargetGroup_error" class="text-danger d-none error col-12" role="alert"></p>
                        </li>
                        <li class="list-group-item px-0">Income :
                            <a href="javascript:void(0);" onclick="editField('Income','input')" id="IncomeBtn"
                               class="d-none"><i
                                    class="fas fa-edit"></i> </a>
                            <a href="javascript:void(0);" onclick="cancelFieldEdit('Income','input')"
                               class="d-none mx-2" id="IncomeCancelBtn"><i class="fas fa-times"></i> </a>
                            <span class="h5 float-end updatable-field set-Income"
                                  ondblclick="editField('Income','input')"
                                  id="IncomeContent">{{Illuminate\Support\Number::abbreviate($product->Income??0,2) }}</span>
                            <input type="text" class="form-control d-none" id="Income" name="Income"
                                   value="{{ ($product->Income)??0 }}">
                            <p id="Income_error" class="text-danger d-none error col-12" role="alert"></p>
                        </li>
                        <li class="list-group-item px-0">Revenue :
                            <a href="javascript:void(0);" onclick="editField('Revenue','input')" id="RevenueBtn"
                               class="d-none"><i
                                    class="fas fa-edit"></i> </a>
                            <a href="javascript:void(0);" onclick="cancelFieldEdit('Revenue','input')"
                               class="d-none mx-2" id="RevenueCancelBtn"><i class="fas fa-times"></i> </a>
                            <span class="h5 float-end updatable-field set-Revenue"
                                  ondblclick="editField('Revenue','input')"
                                  id="RevenueContent">{{Illuminate\Support\Number::abbreviate($product->Revenue??0,2) }}</span>
                            <input type="text" class="form-control d-none" id="Revenue" name="Revenue"
                                   value="{{ ($product->Revenue)??0 }}">
                            <p id="Revenue_error" class="text-danger d-none error col-12" role="alert"></p>
                        </li>
                        <li class="list-group-item px-0">Commenting :
                            @if(is_null($product->CommentStart))
                                <span class="float-end"> disabled <a href="javascript:void(0);"
                                                                     class="enable-commenting-modal"> enable it.</a></span>
                            @elseif(is_null($product->CommentEnd))
                                <span class="float-end"> start: {{ $product->CommentStart->format('M D, Y H:i') }} <br>
                                    <a href="javascript:void(0);"
                                       class="disable-commenting-modal"> end commenting </a></span>
                            @else
                                <span class="float-end"> start: {{ $product->CommentStart->format('M D, Y H:i') }} <br>
                                    end: {{ $product->CommentEnd->format('M D, Y H:i') }}
                                </span>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card">
                <div class="card-header pb-1 border-bottom">
                    <h3 class="card-title">
                        Features
                        <span class="float-end">
                            <a href="javascript:void(0);" class="float-end btn btn-primary btn-sm add-feature-product">
                                <i class="fas fa-plus-circle"></i> add </a>
                        </span>
                    </h3>
                </div>
                <div class="card-body pt-0 pb-2 px-2">
                    <ul class="list-group list-group-flush " id="productFeatures">
                        @foreach($product->features()->get() as $feature)
                            <li class="list-group-item px-0 updatable-field"
                                ondblclick="updateFeature('{{$feature->Id}}','{{$feature->Feature}}','{{$feature->Description}}')"
                                id="Feature_{{$feature->Id}}">
                                {{ $feature->Feature }} : <b class="float-end">{{ $feature->Description }}</b>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @if($canUpdate)
                <div class="card">
                    <div class="card-header pb-0 border-bottom">
                        <h3 class="card-title">Actions</h3>
                    </div>
                    <div class="card-body pt-2 pb-2">
                        <button type="button" class="btn btn-secondary m-2 w-100 modal-update-product">
                            <i class="fas fa-edit"></i>&nbsp; update details
                        </button>
                        <button type="button" class="btn btn-primary m-2 w-100 product-submit-approval">
                            <i class="fas fa-check-double"></i>&nbsp;submit for review
                        </button>
                        <button type="button" class="btn btn-danger m-2 w-100 modal-cancel-product">
                            <i class="fas fa-trash-alt"></i>&nbsp;cancel
                        </button>
                    </div>
                </div>
            @endif
            <div class="card">
                <div class="card-body">
                    @include('snippets.behind_scenes',['model'=>$product])
                    <div class="px-0"><b>Notes</b> :
                        <a href="javascript:void(0);" onclick="editField('Notes','textarea')" id="NotesBtn"
                           class="d-none"><i
                                class="fas fa-edit"></i> </a>
                        <a href="javascript:void(0);" onclick="cancelFieldEdit('Notes','textarea')"
                           class="d-none mx-2" id="NotesCancelBtn"><i class="fas fa-times"></i> </a> <br>
                        <p class="updatable-field set-Notes mb-0" ondblclick="editField('Notes','input')"
                           id="NotesContent">{{ $product->Notes }}</p>
                        <textarea class="form-control d-none" id="Notes" name="Notes"
                                  rows="2">{{ $product->Notes }}</textarea>
                        <p id="Notes_error" class="text-danger d-none error col-12 mb-0" role="alert"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="productActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="addProductFeatureModal">
                        <form action="{{ route('product-feature.store',[$product->ProductID]) }}"
                              method="post" id="addProductFeatureForm" class="row">
                            <div class="col-md-6 col-12 mb-3"> @csrf
                                <label class="form-label" for="feature_title">Feature Title<span
                                        class="text-danger">*</span></label>
                                <input type="text" id="feature_title" name="feature_title" required
                                       class="form-control">
                                <p id="feature_title_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="col-md-6 col-12  mb-3">
                                <label class="form-label" for="feature_content">Feature Content<span
                                        class="text-danger">*</span></label>
                                <input type="text" id="feature_content" name="feature_content" required
                                       class="form-control">
                                <p id="feature_content_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="addProductFeatureBtn" type="submit"><i
                                        class="fas fa-plus-circle"></i> add product
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="updateProductFeatureModal">
                        <ul class="nav nav-tabs card-header-tabs pull-right" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#ProductFeatureUpdate">Update
                                    Feature</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#ProductFeatureDelete">Delete Feature</a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade active show" id="ProductFeatureUpdate" role="tabpanel">
                                <h3 class="mt-4 text-center">UPDATE Feature</h3>
                                <form method="post" id="updateProductFeatureForm" class="row"> @method('put')
                                    <div class="col-md-6 col-12 mb-3"> @csrf
                                        <label class="form-label" for="e_feature_title">Feature Title<span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="e_feature_title" name="feature_title" required
                                               class="form-control">
                                        <p id="e_feature_title_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="col-md-6 col-12  mb-3">
                                        <label class="form-label" for="e_feature_content">Feature Content<span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="e_feature_content" name="feature_content" required
                                               class="form-control">
                                        <p id="e_feature_content_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="mt-2 pt-2 border-top">
                                        <button type="button" class="btn btn-secondary float-start"
                                                data-bs-dismiss="modal">
                                            cancel
                                        </button>
                                        <button class="btn btn-primary float-end" id="updateProductFeatureBtn"
                                                type="submit"><i
                                                class="fas fa-plus-circle"></i> update feature
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane fade text-center" id="ProductFeatureDelete" role="tabpanel">
                                <h3 class="text-danger mt-4"> DELETE Product Feature</h3>
                                <div class="mt-2 mb-2">
                                    Are you sure you want to trash this contact ?
                                </div>
                                <form id="trashProductFeatureForm" method="post"> @csrf
                                    <div class="mt-2 pt-2 border-top">@method('delete')
                                        <button type="button" class="btn btn-success float-start"
                                                data-bs-dismiss="modal">
                                            no, keep
                                        </button>
                                        <button class="btn btn-danger float-end" id="trashProductFeatureBtn"
                                                type="submit"><i
                                                class="fas fa-trash"></i> yes, trash
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="updateProductModal">
                        @if($canUpdate)
                            <div class="alert alert-info" role="alert">
                                <div class="alert-icon">
                                    <i class="far fa-fw fa-bell"></i>
                                </div>
                                <div class="alert-message">
                                    To update the product, simply double-click on the element you wish to modify. This
                                    will allow you to edit the specific field or section directly. Once you’ve made the
                                    necessary changes, remember to save your updates to ensure they are applied
                                    successfully.
                                </div>
                            </div>
                        @else
                            <div class="alert alert-danger" role="alert">
                                <div class="alert-icon">
                                    <i class="far fa-fw fa-bell"></i>
                                </div>
                                <div class="alert-message">
                                    <strong> We're sorry !</strong>, but the product (@yield('title')) cannot be updated
                                    at
                                </div>
                            </div>
                        @endif
                        <div class="mt-2 pt-2 border-top">
                            <button type="button" class="btn btn-secondary float-start"
                                    data-bs-dismiss="modal">
                                cancel
                            </button>
                        </div>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="cancelProductModal">
                        <h4 class="text-danger text-center">
                            Cancel Product Development <b>@yield('title')</b> ?
                        </h4>
                        <div class="mt-2 mb-2">
                            You are about to cancel this product development, confirm below ?
                        </div>
                        <hr>
                        <form id="cancelProductForm" method="post"
                              action="{{ route('product-development.destroy',[$product->ProductID]) }}"> @csrf @method('delete')
                            <div class="mt-4">
                                <button type="button" class="btn btn-success float-start"
                                        data-bs-dismiss="modal">
                                    no, keep
                                </button>
                                <button class="btn btn-danger float-end" id="cancelProductBtn" type="submit"><i
                                        class="fas fa-trash"></i> yes, cancel
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="enableCommentingModal">
                        <h4 class="text-info text-center">
                            Enable commenting for this product development <b>@yield('title')</b> ?
                        </h4>
                        <div class="mt-2 mb-2">
                            By enabling commenting, users involved in the product development process will be notified
                            via email and invited to share their feedback.
                        </div>
                        <form id="enableCommentingForm" method="post"
                              action="{{ route('product-development.comments.enable',[$product->ProductID]) }}"> @csrf @method('put')
                            <div class="mt-2 pt-2 border-top">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-info float-end" id="enableCommentingBtn" type="submit"><i
                                        class="fas fa-comment"></i> yes, enable
                                </button>
                            </div>
                        </form>

                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="disableCommentingModal">
                        <h4 class="text-info text-warning text-center">
                            End commenting for this product development <b>@yield('title')</b> ?
                        </h4>
                        <div class="mt-2 mb-2">
                            By ending commenting, users will not be able to view and share their feedback.
                        </div>
                        <form id="disableCommentingForm" method="post"
                              action="{{ route('product-development.comments.disable',[$product->ProductID]) }}"> @csrf @method('put')
                            <div class="mt-2 pt-2 border-top">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-warning float-end" id="disableCommentingBtn" type="submit"><i
                                        class="fas fa-comment"></i> yes, end
                                </button>
                            </div>
                        </form>

                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item text-center" id="submitProductModal">
                        <h4 class="text-success">Submit Product <b>@yield('title')</b> for
                            Approval ? </h4>
                        <p class="text-muted">This action is non reversible, are you sure ?</p>
                        <form id="submitProductForm" method="post"
                              action="{{ route('product-development.submit',[$product->ProductID]) }}"> @csrf @method('put')
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-success float-end" id="submitProductBtn"
                                        type="submit"><i
                                        class="fas fa-check"></i> yes, submit
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="onboarding-content with-gradient d-none modal-item" id="previewDocumentModal"></div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')
    <script src="{{ asset('assets/plugins/dropzone/dropzone.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/summernote/summernote-bs5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script>const $Modal = $('#productActionsModal');
        window._commentPage = '{{ route('product-development-comment.index',[$product->ProductID]) }}';
        Dropzone.options.uploadForm = {
            maxFilesize: 9,//Mb
            acceptedFiles: "{{ implode(", ",ExtensionsEnum::getAllMimeTypes()) }}",
            success: function (file, response) {
                file.previewElement.remove();
                $('#productsAttachmentContents').append(response.html);
            }
        };
        $(function () {

            $(document).on('click', '.modal-update-product', function () {
                updateProduct();
            });

            $(document).on('click', '.disable-commenting-modal', function () {
                $(".modal-item").addClass('d-none');
                $('#disableCommentingModal').removeClass('d-none');
                $('.modal-title').html('End Commenting.');
                $('.modal-dialog').removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#disableCommentingForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#disableCommentingBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.enable-commenting-modal', function () {
                $(".modal-item").addClass('d-none');
                $('#enableCommentingModal').removeClass('d-none');
                $('.modal-title').html('Enable Commenting.');
                $('.modal-dialog').removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#enableCommentingForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#enableCommentingBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.add-feature-product', function () {
                $(".modal-item").addClass('d-none');
                $('#addProductFeatureModal').removeClass('d-none');
                $('.modal-title').html('Add a product feature.');
                $('.modal-dialog').removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#addProductFeatureForm').submit(async function (e) {
                e.preventDefault();
                let response = await saveForm($(this), $('#addProductFeatureBtn'), false, true, true)
                if (response) {
                    const val = "updateFeature('" + response.feature.Id + "','" + response.feature.Feature + "','" + response.feature.Description + "')";
                    $("#productFeatures").append('<li class="list-group-item px-0 updatable-field" ondblclick="' + val + '" id="Feature_' + response.feature.Id + '">' + response.feature.Feature +
                        ': <b class="float-end">' + response.feature.Description + '</b> </li>');
                    $Modal.modal('hide');
                }
            });

            $('form#updateProductFeatureForm').submit(async function (e) {
                e.preventDefault();
                let response = await saveForm($(this), $('#updateProductFeatureBtn'), false, true, true, true)
                if (response) {
                    $("#Feature_" + response.feature.Id).remove();
                    const val = "updateFeature('" + response.feature.Id + "','" + response.feature.Feature + "','" + response.feature.Description + "')";
                    $("#productFeatures").append('<li class="list-group-item px-0 updatable-field" ondblclick="' + val + '" id="Feature_' + response.feature.Id + '">' + response.feature.Feature +
                        ': <b class="float-end">' + response.feature.Description + '</b> </li>');
                    $Modal.modal('hide');
                }
            });

            $('form#trashProductFeatureForm').submit(async function (e) {
                e.preventDefault();
                let response = await saveForm($(this), $('#trashProductFeatureBtn'), false, true, true)
                if (response) {
                    $("#Feature_" + response.feature.Id).remove();
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '#action-file-upload', function () {
                $(this).addClass('disabled');
                $("#uploadCard").removeClass('d-none');
            });

            $(document).on('click', '#uploadCardClose', function () {
                $("#uploadCard").addClass('d-none');
                $("#action-file-upload").removeClass('disabled');
            });

            $(document).on('click', '.modal-preview-document', function () {
                $('.modal-title').html('File: ' + $(this).attr('title'));
                $(".modal-item").addClass('d-none');
                $('#previewDocumentModal').removeClass('d-none')
                    .html('<div class="text-center my-4"><div class="spinner-grow text-secondary me-2" role="status"><span class="visually-hidden">Loading...</span></div></div>');
                $Modal.children().first().addClass('modal-lg');
                $Modal.modal('show');
                $.get($(this).data('url'), function (data) {
                    $('#previewDocumentModal').html(data);
                }).fail(function (jqXHR) {
                    nError(jqXHR.responseJSON.message);
                    $Modal.modal('hide');
                });
            });

            $(document).on('click', '.modal-cancel-product', function () {
                $(".modal-item").addClass('d-none');
                $('#cancelProductModal').removeClass('d-none');
                $('.modal-title').html('Cancel Product Development');
                $('.modal-dialog').removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#cancelProductForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#cancelProductBtn'), true, true, true, true)) {
                    $Modal.modal('hide');

                }
            });
        });

        $(document).on('click', '.product-submit-approval', function () {
            $(".modal-title").html('Submit product : {{ $product->ProductID }}');
            $(".modal-item").addClass('d-none');
            $('#submitProductModal').removeClass('d-none');
            $Modal.modal('show');
        });
        $('form#submitProductForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#submitProductBtn'), true, true, true)) {
                $Modal.modal('hide');
            }
        });

        function updateProduct() {
            $(".modal-item").addClass('d-none');
            $('#updateProductModal').removeClass('d-none');
            $('.modal-title').html('Update product details.');
            $('.modal-dialog').removeClass('modal-lg');
            $Modal.modal('show');
        }

        function updateFeature(featureID, feature, description) {
            $(".modal-item").addClass('d-none');
            $('#updateProductFeatureModal').removeClass('d-none');
            $('.modal-title').html('Product Feature - ' + feature);
            $('.modal-dialog').removeClass('modal-lg');
            $("#e_feature_title").val(feature);
            $("#e_feature_content").val(description);
            $("#updateProductFeatureForm").attr('action', '{{ route('product-feature.store',[$product->ProductID]) }}/' + featureID);
            $("#trashProductFeatureForm").attr('action', '{{ route('product-feature.store',[$product->ProductID]) }}/' + featureID);
            $Modal.modal('show');
        }

        function editField(fieldID, type) {
            @if($canUpdate)
            $('#' + fieldID + 'Btn').removeClass('d-none').attr('onclick', 'updateField("' + fieldID + '","' + type + '");').html('<i class="fas fa-save"></i> save');
            $('#' + fieldID + 'Content').addClass('d-none');
            $('#' + fieldID + 'CancelBtn').removeClass('d-none');
            switch (type) {
                case 'summernote':
                    $('textarea#' + fieldID).removeClass('d-none').summernote({
                        placeholder: fieldID.toUpperCase() + ' Details.',
                        dialogsInBody: true,
                        tabsize: 2,
                        height: 100,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'underline', 'clear']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture' /*,'video'*/]],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ]
                    });
                    break;
                case 'textarea':
                case 'input':
                case 'select':
                    $('#' + fieldID).removeClass('d-none');
                    break;
            }
            @else updateProduct(); @endif
        }

        @if($canUpdate)
        function cancelFieldEdit(fieldID, type) {
            $('.error').addClass('d-none');
            $('#' + fieldID + 'CancelBtn').addClass('d-none');
            $('#' + fieldID + 'Content').removeClass('d-none');
            $('#' + fieldID + 'Btn').prop('disable', false).removeClass('disabled').addClass('d-none').attr('onclick', 'editField("' + fieldID + '","' + type + '");').html('<i class="fas fa-edit"></i> ');
            switch (type) {
                case 'summernote':
                    $('textarea#' + fieldID).addClass('d-none').summernote('destroy');
                    break;
                case 'input':
                case 'textarea':
                case 'select':
                    $('#' + fieldID).addClass('d-none');
                    break;
            }

        }

        function updateField(fieldID, type) {
            $('.error').addClass('d-none');
            const btn = $('#' + fieldID + 'Btn'), field = $('#' + fieldID);
            btn.addClass('disabled').attr('onclick', '').html('<i class="fas fa-spinner fa-spin"></i> please wait');
            let value = '';
            switch (type) {
                case 'summernote':
                    value = field.summernote('code');
                    break;
                case 'textarea':
                    value = field.html();
                    break;
                case 'select':
                case 'input':
                    value = field.val();
                    break;
            }
            $.ajax({
                url: "{{ route('product-development.update',[$product->ProductID]) }}",
                type: 'PUT',
                dataType: 'json',
                data: [{name: '_token', value: window.csrf_token}, {name: '_method', value: "PUT"},
                    {name: fieldID, value: value}],
                success: function (data) {
                    nSuccess(data.message);
                    updateFields(data.fields);
                    cancelFieldEdit(fieldID, type);
                }, error: function (request) {
                    formRequest(request, true);
                    btn.prop('disable', false).removeClass('disabled').attr('onclick', 'updateField("' + fieldID + '","' + type + '");').html('<i class="fas fa-save"></i> save');
                }
            }).always(function () {
                setTimeout(function () {
                    isBusy = false;
                }, 3000);
            });
        }
        @endif
        function fetchWorkflowTable() {
            if (!$.fn.DataTable.isDataTable('#productWorkflowTable')) {
                $('#productWorkflowTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'asc']],
                    /*"columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    */
                    ajax: {
                        url: '{{ route('product-development.workflows',[$product->ProductID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'Status', name: 'Status'},
                        {data: 'Stage', name: 'Stage'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                        {data: 'creator.Name', name: 'creator.Name'},
                    ], "oLanguage": {
                        "sEmptyTable": "no workflow under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading workflow.");
                    // console.log(er);
                });
            } else {
                $('#productWorkflowTable').DataTable().ajax.reload();
            }
        }

        function fetchActivitiesTable() {
            if (!$.fn.DataTable.isDataTable('#productActivitiesTable')) {
                $('#productActivitiesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[4, 'desc']],
                    columnDefs: [
                        // {"className": "text-center", "targets": [3]},
                        {
                            "render": function (data, type, row) {
                                return '<p><b>' + row.event + '</b><br/>' + data + '</p>';
                                //return data + " " + row.OtherNames;
                            },
                            "targets": 2 // the place of col2
                        },
                        {"visible": false, "targets": [0, 1]}
                    ],
                    ajax: {
                        url: '{{ route('product-development.activities',[$product->ProductID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'event', name: 'event'},
                        {data: 'description', name: 'description'},
                        {data: 'causer.Name', name: 'causer.Name'},
                        {data: 'created_at', name: 'created_at'},
                    ], "oLanguage": {
                        "sEmptyTable": "no workflow under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading workflow.");
                    // console.log(er);
                });
            } else {
                $('#productActivitiesTable').DataTable().ajax.reload();
            }
        }
    </script>

    @include('snippets.actions.comments', ['canComment'=>$canComment])
@endsection
