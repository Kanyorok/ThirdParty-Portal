@extends('layouts.app')

@section('title')
    {{ $file->Name }}
@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">DMS</a></li>
    @if($repoService->isRoot())
        <li class="breadcrumb-item"><a href="{{ route('repo.index') }}">Root</a></li>
    @elseif($repoService->parentRoot())
        <li class="breadcrumb-item"><a href="javascript:void(0)">...</a></li>
    @endif
@endsection
@section('styles')

@endsection
@section('content')
    <div class="row">
        <div class="col-md-4 col-xxl-3">
            <div class="card">
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item ">Name: <b class="float-end">{{ $file->Name }}</b></li>
                        <li class="list-group-item">Type : <b class="float-end">{!! $file->ext()->getIcon() !!} &nbsp;
                                {{$file->ext()->name}}</b></li>
                        <li class="list-group-item">Visibility : <b class="float-end">{!! $file->Visibility->icon() !!}
                                &nbsp; {{$file->Visibility->name}}</b></li>
                        <li class="list-group-item">Versions : <b
                                class="float-end">{{ number_format($file->versions_count) }}</b></li>
                        <li class="list-group-item">Size : <b
                                class="float-end">{{  \Illuminate\Support\Number::fileSize( $file->current->Size, 2) }}</b>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="accordion accordion-flush" id="filePropertiesAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="filePropertiesHeader">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#flush-fileProperties" aria-expanded="false"
                                        aria-controls="flush-fileProperties">
                                    File Properties
                                </button>
                            </h2>
                            <div id="flush-fileProperties" class="accordion-collapse collapse"
                                 aria-labelledby="filePropertiesHeader" data-bs-parent="#filePropertiesAccordion">
                                <ul class="list-group list-group-flush">
                                    @foreach($file->properties as $property)
                                        <li class="list-group-item ">{{ $property->Name }} : <b
                                                class="float-end">{{ $property->formated_value }}</b></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    @include('snippets.behind_scenes',['model'=>$file])
                </div>
            </div>
        </div>
        <div class="col-md-8 col-xxl-9">
            <div class="card ">
                <div class="card-body" style="min-height: 100px">

                </div>
            </div>


            <div class="card">
                <div class="card-header p-0">
                    <div class="nav nav-pills card-header py-2">
                        <ul class="nav" role="tablist">
                            {{-- <li class="nav-item"><a class="nav-link " href="#tab-comments"
                                                     data-bs-toggle="tab" role="tab" aria-selected="false"
                                 >comments</a></li>--}}
                            <li class="nav-item"><a class="nav-link active" href="#tab-watchers" data-bs-toggle="tab"
                                                    role="tab" aria-selected="false" onclick="fetchWatchersTable()"
                                >users & teams</a></li>
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
                        {{-- <div class="tab-pane m-2 " id="tab-comments" role="tabpanel">
                             <div class="pb-1 mb-1 border-bottom">
                                 Comments
                                 @if($ticket->Status->value === TicketStatusEnum::Active->value)
                                     <span class="float-end">
                                               <button class="btn btn-primary btn-sm new-comment" data-parent="comments"
                                                       data-route="{{  route('ticket-comment.store',[$ticket->TicketID])  }}"
                                                       data-title="new comment" type="button">
                                             <i class="fas fa-plus-circle"></i> new comment
                                         </button>
                                     </span>
                                 @endif
                             </div>
                             <div id="comments" class="px-2 pt-0 w-100 comments" style="max-height: 100vh"
                                  data-url="{{  route('ticket-comment.index',[$ticket->TicketID]) }}"></div>
                             <div class="d-grid text-center" id="commentsMessage"></div>
                         </div>--}}
                        <div class="tab-pane m-2 " id="tab-workflow" role="tabpanel">
                            <table id="ticketWorkflowTable"
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
                            <table id="ticketActivitiesTable"
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
                        <div class="tab-pane m-2 active show" id="tab-watchers" role="tabpanel">
                            <table id="ticketWatchersTable"
                                   class="table table-striped no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Party</th>
                                    <th>Role</th>
                                    <th>Dated</th>
                                    <th>action</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')

@endsection
