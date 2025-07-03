@php use App\Enums\Core\ExtensionsEnum; @endphp
@php use App\Enums\Core\VisibilityEnum; @endphp
@extends('layouts.app')

@section('title')
    {{ ($service->isRoot())?'Repositories': $repository->Name }}
@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">DMS</a></li>
    @if(!$service->isRoot())
        <li class="breadcrumb-item"><a href="{{ route('repo.index') }}">Root</a></li>
        @if(!$service->parentRoot())
            <li class="breadcrumb-item"><a href="javascript:void(0)">...</a></li>
        @endif
    @endif
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/dropzone/dropzone.min.css') }}">
    <style>
        .icon-size {
            height: 30px !important;
            width: 30px !important;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12 file-manger-wrapper">
            {{--<a class="h5 text-hover-primary my-3 d-block" data-bs-toggle="collapse" href="#collapseFilefilter" role="button" aria-expanded="false">
                Quick Filter
            </a>
            <div class="collapse show" id="collapseFilefilter">
                <div class="row">
                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="avtar bg-light-primary">
                                    <svg class="pc-icon wid-30 hei-30">
                                        <use xlink:href="#custom-note-1"></use>
                                    </svg>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div><h6 class="mb-1">Documents</h6>
                                        <p class="mb-0">100 files</p></div>
                                    <span class="badge bg-primary f-12">15 GB</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="avtar bg-light-danger">
                                    <svg class="pc-icon wid-30 hei-30">
                                        <use xlink:href="#custom-video-play"></use>
                                    </svg>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div><h6 class="mb-1">Videos</h6>
                                        <p class="mb-0">100 files</p></div>
                                    <span class="badge bg-danger f-12">2.4 GB</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="avtar bg-light-success">
                                    <svg class="pc-icon wid-30 hei-30">
                                        <use xlink:href="#custom-image"></use>
                                    </svg>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div><h6 class="mb-1">Images</h6>
                                        <p class="mb-0">100 files</p></div>
                                    <span class="badge bg-success f-12">2.4 GB</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card border-0 shadow-none drp-upgrade-card"
                             style="background-image: url('{{ asset('/assets/images/layout/img-profile-card.jpg') }}')">
                            <div class="card-body"><h5 class="mb-0 text-muted">20.5GB of 50GB</h5>
                                <div class="row align-items-center my-2">
                                    <div class="col">
                                        <div class="progress" style="height: 6px">
                                            <div class="progress-bar bg-primary" style="width: 70%"></div>
                                        </div>
                                    </div>
                                    <div class="col-auto"><p class="mb-0 text-muted">70%</p></div>
                                </div>
                               --}}{{-- <button class="btn btn-warning mt-3">Want More Storage?</button>--}}{{--
                            </div>
                        </div>
                    </div>
                </div>
            </div>
             <hr class="my-3 border border-secondary-subtle">
            --}}

            <div class="row my-3">
                <div class="col">
                    <a class="h5 text-hover-primary my-3 d-block" data-bs-toggle="collapse" href="#collapseRepositories"
                       role="button" aria-expanded="false">Folders </a>
                </div>
                <div class="col-auto">
                    @if(!($service->isRoot()))
                        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm " type="button">
                            <i class="fas fa-backward"></i>&nbsp; back
                        </a>
                        <a href="{{ route('repo.show',[$repository->parent->RepositoryId]) }}"
                           class="btn btn-secondary btn-sm " type="button">
                            <i class="fas fa-arrow-up"></i>&nbsp; to parent
                        </a>

                        <a class="btn btn-secondary btn-sm click-summary-data" href="javascript:void(0)"
                           data-summary_title="<i class='fas fa-folder-open'></i> {{ $repository->Name }} "
                           data-click_url="{{ route('repo.edit', $repository->RepositoryId) }}"><i
                                class="fas fa-share"></i> Share</a>
                    @endif

                    <button class="btn btn-primary btn-sm create-new-repository" type="button">
                        <i class="fas fa-plus-circle"></i>&nbsp; create repository
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseRepositories">
                <div class="row" style="min-height: 10vh;"
                     data-url="{{ route('repo.show',[$repository->RepositoryId]) }}"
                     id="repositoriesContents"></div>
                <div class="d-grid text-center" id="repositoriesMessage"></div>
            </div>

            <hr class="my-3 border border-secondary-subtle">
            {{--<div class="row my-2">
                <div class="col">
                    <ul class="list-inline ms-auto mb-3">
                        <li class="list-inline-item">
                            <div class="form-search my-1"><i class="ti ti-search"></i> <input type="search"
                                                                                              class="form-control"
                                                                                              placeholder="Search Followers">
                            </div>
                        </li>
                        <li class="list-inline-item"><select class="form-select my-1">
                                <option>All Type</option>
                                <option>Documents</option>
                                <option>Videos</option>
                                <option>Images</option>
                            </select></li>
                        <li class="list-inline-item"><input type="date" class="form-control my-1" id="example-datemax"
                                                            max="1979-12-31"></li>
                    </ul>
                </div>
                <div class="col-auto">

                </div>
            </div>--}}
            <div class="row my-3">
                <div class="col">
                    <div class="d-flex align-items-center"><h5 class="mb-0 me-2">Files</h5></div>
                </div>
                <div class="col-auto">
                    <button href="#" class="btn btn-primary btn-sm" id="action-file-upload"><i
                            class="fas fa-cloud-upload"></i>&nbsp; upload files
                    </button>
                </div>
                {{-- <div class="col-auto">
                     <ul class="nav nav-pills nav-files" id="pills-tab" role="tablist">
                         <li class="nav-item" role="presentation">
                             <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill"
                                     data-bs-target="#pills-home" role="tab" aria-controls="pills-home"
                                     aria-selected="true"><i class="ti ti-layout-grid"></i></button>
                         </li>
                         <li class="nav-item" role="presentation">
                             <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill"
                                     data-bs-target="#pills-profile" role="tab" aria-controls="pills-profile"
                                     aria-selected="false" tabindex="-1"><i class="ti ti-layout-list"></i></button>
                         </li>
                     </ul>
                 </div>--}}
            </div>
            <div class="card d-none" id="uploadCard">
                <div class="card-header pb-1 ">
                    <h6 class="card-title">Upload Files
                        <span class="float-end" style="cursor: pointer;" id="uploadCardClose"><i
                                class="fas fa-times"></i></span>
                    </h6>
                </div>
                <div class="card-body p-0 border border-top">
                    <form action="{{ route('files.store',[$repository->RepositoryId]) }}" class="dropzone"
                          id="upload-form">@csrf</form>
                </div>
            </div>
            <div class="table-responsive card bg-transparent border-0 shadow-none">
                <table class="table table-borderless file-card">
                    <tbody id="fileContents" data-url="{{ route('files.index',[$repository->RepositoryId]) }}"></tbody>
                </table>
                <div class="d-grid text-center" id="filesMessage"></div>
            </div>
            {{-- <div class="tab-content" id="pills-tabContent">
                 <div class="tab-pane fade active show" id="pills-home" role="tabpanel" tabindex="0"
                      aria-labelledby="pills-home-tab">
                     <div class="row">
                         <div class="col-md-6 col-lg-4 col-xxl-3">
                             <div class="card file-card">
                                 <div class="card-body">
                                     <div class="d-flex align-items-center justify-content-between">
                                         <div class="form-check"><input type="radio" name="file-radio"
                                                                        class="form-check-input input-primary"
                                                                        id="file-check-1"> <label
                                                 class="form-check-label d-block" for="file-check-1"></label></div>
                                         <div class="dropdown"><a
                                                 class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none"
                                                 href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                                 aria-expanded="false"><i
                                                     class="material-icons-two-tone f-18">more_vert</i></a>
                                             <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item"
                                                                                             href="#">Edit</a> <a
                                                     class="dropdown-item" href="#">Delete</a></div>
                                         </div>
                                     </div>
                                     <div class="my-3 text-center"><img
                                             src="../assets/images/application/img-file-doc.svg" alt="img"
                                             class="img-fluid"></div>
                                     <div class="d-flex align-items-center justify-content-between mt-4">
                                         <div><h6 class="mb-0"><span
                                                     class="text-truncate w-100">Document-final.docx</span></h6>
                                             <p class="mb-0 text-muted"><small>16 Nov 2022</small></p></div>
                                         <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            data-bs-toggle="modal" data-bs-target="#assignFile">
                                             <svg class="pc-icon">
                                                 <use xlink:href="#custom-user-add"></use>
                                             </svg>
                                         </a></div>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-6 col-lg-4 col-xxl-3">
                             <div class="card file-card">
                                 <div class="card-body">
                                     <div class="d-flex align-items-center justify-content-between">
                                         <div class="form-check"><input type="radio" name="file-radio"
                                                                        class="form-check-input input-primary"
                                                                        id="file-check-2"> <label
                                                 class="form-check-label d-block" for="file-check-2"></label></div>
                                         <div class="dropdown"><a
                                                 class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none"
                                                 href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                                 aria-expanded="false"><i
                                                     class="material-icons-two-tone f-18">more_vert</i></a>
                                             <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item"
                                                                                             href="#">Edit</a> <a
                                                     class="dropdown-item" href="#">Delete</a></div>
                                         </div>
                                     </div>
                                     <div class="my-3 text-center"><img
                                             src="../assets/images/application/img-file-xls.svg" alt="img"
                                             class="img-fluid"></div>
                                     <div class="d-flex align-items-center justify-content-between mt-4">
                                         <div><h6 class="mb-0"><span
                                                     class="text-truncate w-100">Document-final.docx</span></h6>
                                             <p class="mb-0 text-muted"><small>16 Nov 2022</small></p></div>
                                         <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            data-bs-toggle="modal" data-bs-target="#assignFile">
                                             <svg class="pc-icon">
                                                 <use xlink:href="#custom-user-add"></use>
                                             </svg>
                                         </a></div>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-6 col-lg-4 col-xxl-3">
                             <div class="card file-card">
                                 <div class="card-body">
                                     <div class="d-flex align-items-center justify-content-between">
                                         <div class="form-check"><input type="radio" name="file-radio"
                                                                        class="form-check-input input-primary"
                                                                        id="file-check-3"> <label
                                                 class="form-check-label d-block" for="file-check-3"></label></div>
                                         <div class="dropdown"><a
                                                 class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none"
                                                 href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                                 aria-expanded="false"><i
                                                     class="material-icons-two-tone f-18">more_vert</i></a>
                                             <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item"
                                                                                             href="#">Edit</a> <a
                                                     class="dropdown-item" href="#">Delete</a></div>
                                         </div>
                                     </div>
                                     <div class="my-3 text-center"><img
                                             src="../assets/images/application/img-file-pdf.svg" alt="img"
                                             class="img-fluid"></div>
                                     <div class="d-flex align-items-center justify-content-between mt-4">
                                         <div><h6 class="mb-0"><span
                                                     class="text-truncate w-100">Document-final.docx</span></h6>
                                             <p class="mb-0 text-muted"><small>16 Nov 2022</small></p></div>
                                         <a href="#" class="user-popup" data-bs-toggle="modal"
                                            data-bs-target="#assignFile">
                                             <div class="user-group p-1"><img src="../assets/images/user/avatar-1.jpg"
                                                                              alt="user-image" class="avtar"> <img
                                                     src="../assets/images/user/avatar-2.jpg" alt="user-image"
                                                     class="avtar"> <img src="../assets/images/user/avatar-3.jpg"
                                                                         alt="user-image" class="avtar"></div>
                                         </a></div>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-6 col-lg-4 col-xxl-3">
                             <div class="card file-card">
                                 <div class="card-body">
                                     <div class="d-flex align-items-center justify-content-between">
                                         <div class="form-check"><input type="radio" name="file-radio"
                                                                        class="form-check-input input-primary"
                                                                        id="file-check-4"> <label
                                                 class="form-check-label d-block" for="file-check-4"></label></div>
                                         <div class="dropdown"><a
                                                 class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none"
                                                 href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                                 aria-expanded="false"><i
                                                     class="material-icons-two-tone f-18">more_vert</i></a>
                                             <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item"
                                                                                             href="#">Edit</a> <a
                                                     class="dropdown-item" href="#">Delete</a></div>
                                         </div>
                                     </div>
                                     <div class="my-3 text-center"><img
                                             src="../assets/images/application/img-file-xls.svg" alt="img"
                                             class="img-fluid"></div>
                                     <div class="d-flex align-items-center justify-content-between mt-4">
                                         <div><h6 class="mb-0"><span
                                                     class="text-truncate w-100">Document-final.docx</span></h6>
                                             <p class="mb-0 text-muted"><small>16 Nov 2022</small></p></div>
                                         <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            data-bs-toggle="modal" data-bs-target="#assignFile">
                                             <svg class="pc-icon">
                                                 <use xlink:href="#custom-user-add"></use>
                                             </svg>
                                         </a></div>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-6 col-lg-4 col-xxl-3">
                             <div class="card file-card">
                                 <div class="card-body">
                                     <div class="d-flex align-items-center justify-content-between">
                                         <div class="form-check"><input type="radio" name="file-radio"
                                                                        class="form-check-input input-primary"
                                                                        id="file-check-5"> <label
                                                 class="form-check-label d-block" for="file-check-5"></label></div>
                                         <div class="dropdown"><a
                                                 class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none"
                                                 href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                                 aria-expanded="false"><i
                                                     class="material-icons-two-tone f-18">more_vert</i></a>
                                             <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item"
                                                                                             href="#">Edit</a> <a
                                                     class="dropdown-item" href="#">Delete</a></div>
                                         </div>
                                     </div>
                                     <div class="my-3 text-center"><img
                                             src="../assets/images/application/img-file-rar.svg" alt="img"
                                             class="img-fluid"></div>
                                     <div class="d-flex align-items-center justify-content-between mt-4">
                                         <div><h6 class="mb-0"><span
                                                     class="text-truncate w-100">Document-final.docx</span></h6>
                                             <p class="mb-0 text-muted"><small>16 Nov 2022</small></p></div>
                                         <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            data-bs-toggle="modal" data-bs-target="#assignFile">
                                             <svg class="pc-icon">
                                                 <use xlink:href="#custom-user-add"></use>
                                             </svg>
                                         </a></div>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-6 col-lg-4 col-xxl-3">
                             <div class="card file-card">
                                 <div class="card-body">
                                     <div class="d-flex align-items-center justify-content-between">
                                         <div class="form-check"><input type="radio" name="file-radio"
                                                                        class="form-check-input input-primary"
                                                                        id="file-check-6"> <label
                                                 class="form-check-label d-block" for="file-check-6"></label></div>
                                         <div class="dropdown"><a
                                                 class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none"
                                                 href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                                 aria-expanded="false"><i
                                                     class="material-icons-two-tone f-18">more_vert</i></a>
                                             <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item"
                                                                                             href="#">Edit</a> <a
                                                     class="dropdown-item" href="#">Delete</a></div>
                                         </div>
                                     </div>
                                     <div class="my-2 text-center"><img
                                             src="../assets/images/application/img-file-imgview.jpg" alt="img"
                                             class="img-fluid rounded"></div>
                                     <div class="d-flex align-items-center justify-content-between">
                                         <div><h6 class="mb-0"><span
                                                     class="text-truncate w-100">Document-final.docx</span></h6>
                                             <p class="mb-0 text-muted"><small>16 Nov 2022</small></p></div>
                                         <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            data-bs-toggle="modal" data-bs-target="#assignFile">
                                             <svg class="pc-icon">
                                                 <use xlink:href="#custom-user-add"></use>
                                             </svg>
                                         </a></div>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-6 col-lg-4 col-xxl-3">
                             <div class="card file-card">
                                 <div class="card-body">
                                     <div class="d-flex align-items-center justify-content-between">
                                         <div class="form-check"><input type="radio" name="file-radio"
                                                                        class="form-check-input input-primary"
                                                                        id="file-check-7"> <label
                                                 class="form-check-label d-block" for="file-check-7"></label></div>
                                         <div class="dropdown"><a
                                                 class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none"
                                                 href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                                 aria-expanded="false"><i
                                                     class="material-icons-two-tone f-18">more_vert</i></a>
                                             <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item"
                                                                                             href="#">Edit</a> <a
                                                     class="dropdown-item" href="#">Delete</a></div>
                                         </div>
                                     </div>
                                     <div class="my-3 text-center"><img
                                             src="../assets/images/application/img-file-ppt.svg" alt="img"
                                             class="img-fluid"></div>
                                     <div class="d-flex align-items-center justify-content-between mt-4">
                                         <div><h6 class="mb-0"><span
                                                     class="text-truncate w-100">Document-final.docx</span></h6>
                                             <p class="mb-0 text-muted"><small>16 Nov 2022</small></p></div>
                                         <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            data-bs-toggle="modal" data-bs-target="#assignFile">
                                             <svg class="pc-icon">
                                                 <use xlink:href="#custom-user-add"></use>
                                             </svg>
                                         </a></div>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-6 col-lg-4 col-xxl-3">
                             <div class="card file-card">
                                 <div class="card-body">
                                     <div class="d-flex align-items-center justify-content-between">
                                         <div class="form-check"><input type="radio" name="file-radio"
                                                                        class="form-check-input input-primary"
                                                                        id="file-check-8"> <label
                                                 class="form-check-label d-block" for="file-check-8"></label></div>
                                         <div class="dropdown"><a
                                                 class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none"
                                                 href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                                 aria-expanded="false"><i
                                                     class="material-icons-two-tone f-18">more_vert</i></a>
                                             <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item"
                                                                                             href="#">Edit</a> <a
                                                     class="dropdown-item" href="#">Delete</a></div>
                                         </div>
                                     </div>
                                     <div class="my-3 text-center"><img
                                             src="../assets/images/application/img-file-ai.svg" alt="img"
                                             class="img-fluid"></div>
                                     <div class="d-flex align-items-center justify-content-between mt-4">
                                         <div><h6 class="mb-0"><span
                                                     class="text-truncate w-100">Document-final.docx</span></h6>
                                             <p class="mb-0 text-muted"><small>16 Nov 2022</small></p></div>
                                         <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            data-bs-toggle="modal" data-bs-target="#assignFile">
                                             <svg class="pc-icon">
                                                 <use xlink:href="#custom-user-add"></use>
                                             </svg>
                                         </a></div>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-6 col-lg-4 col-xxl-3">
                             <div class="card file-card">
                                 <div class="card-body">
                                     <div class="d-flex align-items-center justify-content-between">
                                         <div class="form-check"><input type="radio" name="file-radio"
                                                                        class="form-check-input input-primary"
                                                                        id="file-check-9"> <label
                                                 class="form-check-label d-block" for="file-check-9"></label></div>
                                         <div class="dropdown"><a
                                                 class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none"
                                                 href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                                 aria-expanded="false"><i
                                                     class="material-icons-two-tone f-18">more_vert</i></a>
                                             <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item"
                                                                                             href="#">Edit</a> <a
                                                     class="dropdown-item" href="#">Delete</a></div>
                                         </div>
                                     </div>
                                     <div class="my-3 text-center"><img
                                             src="../assets/images/application/img-file-ppt.svg" alt="img"
                                             class="img-fluid"></div>
                                     <div class="d-flex align-items-center justify-content-between mt-4">
                                         <div><h6 class="mb-0"><span
                                                     class="text-truncate w-100">Document-final.docx</span></h6>
                                             <p class="mb-0 text-muted"><small>16 Nov 2022</small></p></div>
                                         <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            data-bs-toggle="modal" data-bs-target="#assignFile">
                                             <svg class="pc-icon">
                                                 <use xlink:href="#custom-user-add"></use>
                                             </svg>
                                         </a></div>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-6 col-lg-4 col-xxl-3">
                             <div class="card file-card">
                                 <div class="card-body">
                                     <div class="d-flex align-items-center justify-content-between">
                                         <div class="form-check"><input type="radio" name="file-radio"
                                                                        class="form-check-input input-primary"
                                                                        id="file-check-10"> <label
                                                 class="form-check-label d-block" for="file-check-10"></label></div>
                                         <div class="dropdown"><a
                                                 class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none"
                                                 href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                                 aria-expanded="false"><i
                                                     class="material-icons-two-tone f-18">more_vert</i></a>
                                             <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item"
                                                                                             href="#">Edit</a> <a
                                                     class="dropdown-item" href="#">Delete</a></div>
                                         </div>
                                     </div>
                                     <div class="my-3 text-center"><img
                                             src="../assets/images/application/img-file-txt.svg" alt="img"
                                             class="img-fluid"></div>
                                     <div class="d-flex align-items-center justify-content-between mt-4">
                                         <div><h6 class="mb-0"><span
                                                     class="text-truncate w-100">Document-final.docx</span></h6>
                                             <p class="mb-0 text-muted"><small>16 Nov 2022</small></p></div>
                                         <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            data-bs-toggle="modal" data-bs-target="#assignFile">
                                             <svg class="pc-icon">
                                                 <use xlink:href="#custom-user-add"></use>
                                             </svg>
                                         </a></div>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-6 col-lg-4 col-xxl-3">
                             <div class="card file-card">
                                 <div class="card-body">
                                     <div class="d-flex align-items-center justify-content-between">
                                         <div class="form-check"><input type="radio" name="file-radio"
                                                                        class="form-check-input input-primary"
                                                                        id="file-check-11"> <label
                                                 class="form-check-label d-block" for="file-check-11"></label></div>
                                         <div class="dropdown"><a
                                                 class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none"
                                                 href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                                 aria-expanded="false"><i
                                                     class="material-icons-two-tone f-18">more_vert</i></a>
                                             <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item"
                                                                                             href="#">Edit</a> <a
                                                     class="dropdown-item" href="#">Delete</a></div>
                                         </div>
                                     </div>
                                     <div class="my-3 text-center"><img
                                             src="../assets/images/application/img-file-img.svg" alt="img"
                                             class="img-fluid"></div>
                                     <div class="d-flex align-items-center justify-content-between mt-4">
                                         <div><h6 class="mb-0"><span
                                                     class="text-truncate w-100">Document-final.docx</span></h6>
                                             <p class="mb-0 text-muted"><small>16 Nov 2022</small></p></div>
                                         <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            data-bs-toggle="modal" data-bs-target="#assignFile">
                                             <svg class="pc-icon">
                                                 <use xlink:href="#custom-user-add"></use>
                                             </svg>
                                         </a></div>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-6 col-lg-4 col-xxl-3">
                             <div class="card file-card">
                                 <div class="card-body">
                                     <div class="d-flex align-items-center justify-content-between">
                                         <div class="form-check"><input type="radio" name="file-radio"
                                                                        class="form-check-input input-primary"
                                                                        id="file-check-12"> <label
                                                 class="form-check-label d-block" for="file-check-12"></label></div>
                                         <div class="dropdown"><a
                                                 class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none"
                                                 href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                                 aria-expanded="false"><i
                                                     class="material-icons-two-tone f-18">more_vert</i></a>
                                             <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item"
                                                                                             href="#">Edit</a> <a
                                                     class="dropdown-item" href="#">Delete</a></div>
                                         </div>
                                     </div>
                                     <div class="my-3 text-center"><img
                                             src="../assets/images/application/img-file-doc.svg" alt="img"
                                             class="img-fluid"></div>
                                     <div class="d-flex align-items-center justify-content-between mt-4">
                                         <div><h6 class="mb-0"><span
                                                     class="text-truncate w-100">Document-final.docx</span></h6>
                                             <p class="mb-0 text-muted"><small>16 Nov 2022</small></p></div>
                                         <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            data-bs-toggle="modal" data-bs-target="#assignFile">
                                             <svg class="pc-icon">
                                                 <use xlink:href="#custom-user-add"></use>
                                             </svg>
                                         </a></div>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-6 col-lg-4 col-xxl-3">
                             <div class="card file-card">
                                 <div class="card-body">
                                     <div class="d-flex align-items-center justify-content-between">
                                         <div class="form-check"><input type="radio" name="file-radio"
                                                                        class="form-check-input input-primary"
                                                                        id="file-check-13"> <label
                                                 class="form-check-label d-block" for="file-check-13"></label></div>
                                         <div class="dropdown"><a
                                                 class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none"
                                                 href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                                 aria-expanded="false"><i
                                                     class="material-icons-two-tone f-18">more_vert</i></a>
                                             <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item"
                                                                                             href="#">Edit</a> <a
                                                     class="dropdown-item" href="#">Delete</a></div>
                                         </div>
                                     </div>
                                     <div class="my-3 text-center"><img
                                             src="../assets/images/application/img-file-rar.svg" alt="img"
                                             class="img-fluid"></div>
                                     <div class="d-flex align-items-center justify-content-between mt-4">
                                         <div><h6 class="mb-0"><span
                                                     class="text-truncate w-100">Document-final.docx</span></h6>
                                             <p class="mb-0 text-muted"><small>16 Nov 2022</small></p></div>
                                         <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            data-bs-toggle="modal" data-bs-target="#assignFile">
                                             <svg class="pc-icon">
                                                 <use xlink:href="#custom-user-add"></use>
                                             </svg>
                                         </a></div>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-6 col-lg-4 col-xxl-3">
                             <div class="card file-card">
                                 <div class="card-body">
                                     <div class="d-flex align-items-center justify-content-between">
                                         <div class="form-check"><input type="radio" name="file-radio"
                                                                        class="form-check-input input-primary"
                                                                        id="file-check-14"> <label
                                                 class="form-check-label d-block" for="file-check-14"></label></div>
                                         <div class="dropdown"><a
                                                 class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none"
                                                 href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                                 aria-expanded="false"><i
                                                     class="material-icons-two-tone f-18">more_vert</i></a>
                                             <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item"
                                                                                             href="#">Edit</a> <a
                                                     class="dropdown-item" href="#">Delete</a></div>
                                         </div>
                                     </div>
                                     <div class="my-3 text-center"><img
                                             src="../assets/images/application/img-file-doc.svg" alt="img"
                                             class="img-fluid"></div>
                                     <div class="d-flex align-items-center justify-content-between mt-4">
                                         <div><h6 class="mb-0"><span
                                                     class="text-truncate w-100">Document-final.docx</span></h6>
                                             <p class="mb-0 text-muted"><small>16 Nov 2022</small></p></div>
                                         <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            data-bs-toggle="modal" data-bs-target="#assignFile">
                                             <svg class="pc-icon">
                                                 <use xlink:href="#custom-user-add"></use>
                                             </svg>
                                         </a></div>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-6 col-lg-4 col-xxl-3">
                             <div class="card file-card">
                                 <div class="card-body">
                                     <div class="d-flex align-items-center justify-content-between">
                                         <div class="form-check"><input type="radio" name="file-radio"
                                                                        class="form-check-input input-primary"
                                                                        id="file-check-15"> <label
                                                 class="form-check-label d-block" for="file-check-15"></label></div>
                                         <div class="dropdown"><a
                                                 class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none"
                                                 href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                                 aria-expanded="false"><i
                                                     class="material-icons-two-tone f-18">more_vert</i></a>
                                             <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item"
                                                                                             href="#">Edit</a> <a
                                                     class="dropdown-item" href="#">Delete</a></div>
                                         </div>
                                     </div>
                                     <div class="my-3 text-center"><img
                                             src="../assets/images/application/img-file-ppt.svg" alt="img"
                                             class="img-fluid"></div>
                                     <div class="d-flex align-items-center justify-content-between mt-4">
                                         <div><h6 class="mb-0"><span
                                                     class="text-truncate w-100">Document-final.docx</span></h6>
                                             <p class="mb-0 text-muted"><small>16 Nov 2022</small></p></div>
                                         <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            data-bs-toggle="modal" data-bs-target="#assignFile">
                                             <svg class="pc-icon">
                                                 <use xlink:href="#custom-user-add"></use>
                                             </svg>
                                         </a></div>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-6 col-lg-4 col-xxl-3">
                             <div class="card file-card">
                                 <div class="card-body">
                                     <div class="d-flex align-items-center justify-content-between">
                                         <div class="form-check"><input type="radio" name="file-radio"
                                                                        class="form-check-input input-primary"
                                                                        id="file-check-16"> <label
                                                 class="form-check-label d-block" for="file-check-16"></label></div>
                                         <div class="dropdown"><a
                                                 class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none"
                                                 href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                                 aria-expanded="false"><i
                                                     class="material-icons-two-tone f-18">more_vert</i></a>
                                             <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item"
                                                                                             href="#">Edit</a> <a
                                                     class="dropdown-item" href="#">Delete</a></div>
                                         </div>
                                     </div>
                                     <div class="my-3 text-center"><img
                                             src="../assets/images/application/img-file-ai.svg" alt="img"
                                             class="img-fluid"></div>
                                     <div class="d-flex align-items-center justify-content-between mt-4">
                                         <div><h6 class="mb-0"><span
                                                     class="text-truncate w-100">Document-final.docx</span></h6>
                                             <p class="mb-0 text-muted"><small>16 Nov 2022</small></p></div>
                                         <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            data-bs-toggle="modal" data-bs-target="#assignFile">
                                             <svg class="pc-icon">
                                                 <use xlink:href="#custom-user-add"></use>
                                             </svg>
                                         </a></div>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
                 <div class="tab-pane fade" id="pills-profile" role="tabpanel" tabindex="0"
                      aria-labelledby="pills-profile-tab">
                    Table was Here
                 </div>
             </div>--}}
        </div>
    </div>

    <div class="modal fade" id="dmsActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="createRepositoryModal">
                        <form action="{{ route('repo.store') }}" method="post"
                              id="createRepositoryForm">
                            @csrf
                            <input type="hidden" class="d-none" id="repository_parent" name="repository_parent"
                                   value="{{ $repository->RepositoryId }}">
                            <p id="repository_parent_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                            <div class="mb-3">
                                <label class="form-label" for="repository_name">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="repository_name" name="repository_name"
                                       placeholder="Name">
                                <p id="repository_name_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="repository_description">Description </label>
                                <textarea name="repository_description" id="repository_description" rows="3"
                                          class="form-control"></textarea>
                                <p id="repository_description_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start" data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="createRepositoryBtn" type="submit">
                                    <i class="fas fa-save"></i> create folder
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="updateRepositoryModal">
                        <form method="post" id="updateRepositoryForm">
                            @csrf
                            <div class="mb-3">@method('PUT')
                                <label class="form-label" for="e_repository_name">Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="e_repository_name" name="repository_name"
                                       placeholder="Name">
                                <p id="e_repository_name_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="e_repository_description">Description </label>
                                <textarea name="repository_description" id="e_repository_description" rows="3"
                                          class="form-control"></textarea>
                                <p id="e_repository_description_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start" data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="updateRepositoryBtn" type="submit">
                                    <i class="fas fa-save"></i> rename
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item text-center"
                         id="trashRepositoryModal">
                        <h4 class="text-danger">
                            Trash Repository <b class="rm-repo-name"></b> ?
                        </h4>
                        <div class="alert alert-warning" role="alert">
                            <b>Note</b> by continuing, this will delete all the files (documents) and folders inside
                        </div>
                        <form id="trashRepositoryForm" method="post"> @csrf
                            <div class="mt-4">@method('delete')
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-danger float-end" id="trashRepositoryBtn"
                                        type="submit"><i
                                        class="fas fa-trash"></i> yes, trash
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item text-center"
                         id="trashFileModal">
                        <h4 class="text-danger">
                            Trash Document <b class="rm-file-name"></b> ?
                        </h4>
                        <div class="alert alert-warning" role="alert">
                            <b>Note</b>This file will be deleted permanently
                        </div>
                        <form id="trashFileForm" method="post"> @csrf
                            <div class="mt-4">@method('delete')
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-danger float-end" id="trashFileBtn"
                                        type="submit"><i
                                        class="fas fa-trash"></i> yes, document
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ asset('assets/libs/dropzone/dropzone.min.js') }}"></script>
    <script>const $Modal = $('#dmsActionsModal');
        Dropzone.options.uploadForm = {
            maxFilesize: 9,//Mb//todo filesize
            acceptedFiles: "{{ implode(", ",ExtensionsEnum::getAllMimeTypes()) }}",
            success: function (file, response) {
                file.previewElement.remove();
                nSuccess(response.message);
                appendFiles(response.data, true);
            },
            error: function (file, message) {
                msg = (typeof message === 'string') ? message : message.message

                nWarning(msg + ' : ' + file.name);
                file.previewElement.remove();
            },
        };
        $(function () {
            $(document).on('click', '#action-file-upload', function () {
                $(this).addClass('d-none');
                $("#uploadCard").removeClass('d-none');

            });
            $(document).on('click', '#uploadCardClose', function () {
                $("#uploadCard").addClass('d-none');
                $("#action-file-upload").removeClass('d-none');
            });

            $(document).on('click', '.create-new-repository', function () {
                $(".modal-item").addClass('d-none');
                $('#createRepositoryModal').removeClass('d-none');
                $('.modal-title').html('<b class="text-info">CREATE</b> a repository (folder)');
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#createRepositoryForm').submit(async function (e) {
                e.preventDefault();
                const response = await saveForm($(this), $('#createRepositoryBtn'), false, true, true)
                if (response) {
                    $Modal.modal('hide');
                    _appendRepository(response.data);
                }
            });

            $(document).on('click', '.repo-action-update', function () {
                const data = $(this).data('info').split('~');
                $(".modal-title").html('Update Repo  : ' + data[1]);
                $("#e_repository_name").val(data[1]);
                $("#e_repository_description").html(data[2]);
                $("#updateRepositoryForm").attr('action', data[3]);
                $(".modal-item").addClass('d-none');
                $('#updateRepositoryModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#updateRepositoryForm').submit(async function (e) {
                e.preventDefault();
                const response = await saveForm($(this), $('#updateRepositoryBtn'), false, true, true, true)
                if (response) {
                    $Modal.modal('hide');
                    $('#' + response.data.id).remove();
                    _appendRepository(response.data);
                }
            });

            $(document).on('click', '.repo-action-trash', function () {
                const data = $(this).data('info').split('~');
                $(".modal-title").html('<b class="text-danger">Trash</b> Repository : ' + data[1]);
                $("#trashRepositoryForm").attr('action', data[2]);
                $(".rm-repo-name").html(data[1]);
                $(".modal-item").addClass('d-none');
                $('#trashRepositoryModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#trashRepositoryForm').submit(async function (e) {
                e.preventDefault();
                const response = await saveForm($(this), $('#trashRepositoryBtn'), false, true, true);
                if (response) {
                    $('#' + response.data.id).remove();
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.file-action-trash', function () {
                $(".modal-title").html('<b class="text-danger">Trash</b>  : ' + $(this).data('title'));
                $("#trashFileForm").attr('action', $(this).data('url'));
                $(".rm-repo-name").html($(this).data('title'));
                $(".modal-item").addClass('d-none');
                $('#trashFileModal').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#trashFileForm').submit(async function (e) {
                e.preventDefault();
                const response = await saveForm($(this), $('#trashFileBtn'), false, true, true);
                if (response) {
                    $('#' + response.data.id).remove();
                    $Modal.modal('hide');
                }
            });
            fetchRepositories();
            fetchFiles();
        });

        async function fetchRepositories() {
            const parent = $('#repositoriesContents'), cmtMsg = $('#repositoriesMessage');
            let url = parent.data('url');
            if (url === null) {
                cmtMsg.html('');
                return;
            }
            cmtMsg.html('<p class="mt-3"><i class="fas fa-spinner fa-spin fa-5x"></i> please wait</p>');
            await $.get(url, function (data) {
                $.map(data.data, function (repo) {
                    _appendRepository(repo);
                });
                url = data.links.next;
                if (url === null) {
                    parent.data('url', null);
                    cmtMsg.html('');
                    return;
                }
                parent.data('url', url);
                cmtMsg.html('<button type="button" class="btn btn-primary" onclick="fetchRepositories()">Load more</button>');
            }).fail(function (e) {
                formRequest(e)
            });
        }

        function _appendRepository(repo) {
            let content = '<div class="col-md-6 col-xl-3 dbl-click-redirect-data" id="' + repo.id + '"  data-dbl_click_url="' + repo.links.route + '" > ' +
                '<div class="card"> <div class="card-body"> <div class="d-flex"> <div class="flex-shrink-0"> <svg class="pc-icon wid-40 hei-40 ';
            content += (repo.visibility.value === '{{ VisibilityEnum::Private->value }}') ? ' text-warning' : ' text-primary';
            content += '"> <use xlink:href="#custom-folder-open"></use> </svg> </div> <div class="flex-grow-1 mx-3">' +
                '<h5 class="mb-1 d-grid"><span class="text-truncate w-100">' + repo.name + '</span></h5> <p class="mb-0"><small>';
            content += (repo.files.count >= 1) ? repo.files.string + ' file(s)' : 'empty';
            content += '</small></p></div>' +
                ' <div class="dropdown"><a class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none" href="javascript:void(0)" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="material-icons-two-tone f-18">more_vert</i></a><div class="dropdown-menu dropdown-menu-end">' +
                '<a class="dropdown-item repo-action-update" data-info="' + repo.id + '~' + repo.name + '~' + repo.description + '~' + repo.links.route + '" href="javascript:void(0)">Rename</a>' +
                '<a class="dropdown-item click-summary-data" href="javascript:void(0)" data-summary_title="<i class=\'fas fa-folder-open\'></i> ' + repo.name + ' " data-click_url="' + repo.links.summary + '">Permissions</a> ' +
                '<a class="dropdown-item repo-action-trash" href="javascript:void(0)" data-info="' + repo.id + '~' + repo.name + '~' + repo.links.route + '">Trash</a></div> </div></div> </div> </div> </div>';
            $('#repositoriesContents').append(content).fadeIn(500);
        }

        async function fetchFiles() {
            const parent = $('#fileContents'), cmtMsg = $('#filesMessage');
            let url = parent.data('url');
            if (url === null) {
                cmtMsg.html('');
                return;
            }
            cmtMsg.html('<p class="mt-3"><i class="fas fa-spinner fa-spin fa-5x"></i> please wait</p>');
            await $.get(url, function (data) {
                $.map(data.data, function (document) {
                    appendFiles(document);
                });
                url = data.links.next;
                if (url === null) {
                    parent.data('url', null);
                    cmtMsg.html('');
                    return;
                }
                parent.data('url', url);
                cmtMsg.html('<button type="button" class="btn btn-primary" onclick="fetchFiles()">Load more</button>');
            }).fail(function (e) {
                formRequest(e)
            });
        }

        function appendFiles(file, prepend = false) {
            let usersContent = '';
            file.users.data.data.forEach(function (user) {
                usersContent += user.image;
            });
            if (file.users.hasMorePages) {
                usersContent += ' <span class="avtar avtar-xs bg-light-primary text-primary">+' + file.users.total + '</span>';
            }
            let tagsContent = '';
            file.tags.data.data.forEach(function (tag) {
                if (tag.visibility.value === '{{ VisibilityEnum::Private->value }}') {
                    tagsContent += '<span class="badge rounded-pill text-bg-primary">' + tag.Name + '</span>'
                } else {
                    tagsContent += '<span class="badge rounded-pill text-bg-danger">' + tag.Name + '</span>'
                }
            });

            let content = '<tr id="' + file.id + '" class="dbl-click-redirect-data" data-dbl_click_url="' + file.links.detail + '"> <td> <div class="d-flex align-items-center"><img src="' + file.type.img + '" alt="user-image" class="wid-35">' +
                '<h6 class="mb-0 ms-2 text-truncate">' + file.name + '</h6> </div> </td> <td>' + file.size.string + '</td> <td>' + file.dated.datetime + '</td>' +
                '<td> <div class="user-group p-1">' + usersContent + '  </div> </td>' +
                '<td> <div class="d-flex flex-wrap gap-2">' + tagsContent + ' </div> </td>' +
                '<td> <ul class="list-inline text-end"> <li class="list-inline-item mx-2"> ' + file.visibility.icon + ' </li>' +
                '<li class="list-inline-item"><div class="dropdown"><a class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="material-icons-two-tone f-18">more_vert</i></a><div class="dropdown-menu dropdown-menu-end" style="">' +
                '<a class="dropdown-item" href="' + file.links.detail + '">Details</a> ' +
                '<a class="dropdown-item click-summary-data" href="javascript:void(0)" data-summary_title=" ' + file.type.icon + ' ' + file.name + ' " data-click_url="' + file.links.summary + '"> share </a>' +
                '<a class="dropdown-item file-action-trash" data-title=" ' + file.type.icon + ' ' + file.name + ' " data-url="' + file.links.detail + '" href="#">Delete</a></div></div></li> </ul> </td> </tr>';
            if (prepend) {
                $('#fileContents').prepend(content);
            } else {
                $('#fileContents').append(content);
            }
        }
    </script>
@endsection
