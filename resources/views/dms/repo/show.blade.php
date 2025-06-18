@extends('layouts.app')

@section('title','Repositories')
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


            <a class="h5 text-hover-primary my-3 d-block" data-bs-toggle="collapse" href="#collapseRepositories"
               role="button" aria-expanded="false">Folders </a>
            <div class="collapse show" id="collapseRepositories">
                <div class="row">
                    <div class="col-md-6 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <svg class="pc-icon wid-40 hei-40 text-warning">
                                            <use xlink:href="#custom-folder-open"></use>
                                        </svg>
                                    </div>
                                    <div class="flex-grow-1 mx-3"><h5 class="mb-1 d-grid"><span
                                                class="text-truncate w-100">Documents</span></h5>
                                        <p class="mb-0"><small>24 files</small></p></div>
                                    <div class="dropdown"><a
                                            class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none"
                                            href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false"><i class="material-icons-two-tone f-18">more_vert</i></a>
                                        <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#">Edit</a>
                                            <a class="dropdown-item" href="#">Delete</a></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
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
                    <tbody id="fileContents" data-url="{{ route('repo.show',[$repository->RepositoryId]) }}"></tbody>
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
@endsection
@section('scripts')
    <script src="{{ asset('assets/libs/dropzone/dropzone.min.js') }}"></script>
    <script>
        Dropzone.options.uploadForm = {
            maxFilesize: 9,//Mb//todo filesize
            acceptedFiles: "{{ implode(", ",\App\Enums\Core\ExtensionsEnum::getAllMimeTypes()) }}",
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

            fetchFiles();
        });

        async function fetchFiles() {
            const parent = $('#fileContents'), cmtMsg = $('#filesMessage');
            let url = parent.data('url');
            if (url === null) {
                cmtMsg.html('');
                return;
            }
            cmtMsg.html('<i class="fas fa-spinner fa-spin"></i> please wait');
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
                if (tag.visibility.value === '{{ \App\Enums\Core\VisibilityEnum::Private->value }}') {
                    tagsContent += '<span class="badge rounded-pill text-bg-primary">' + tag.Name + '</span>'
                } else {
                    tagsContent += '<span class="badge rounded-pill text-bg-danger">' + tag.Name + '</span>'
                }
            });


            let content = '<tr id="' + file.id + '"> <td> <div class="d-flex align-items-center"><img src="' + file.type.icon + '" alt="user-image" class="wid-35">' +
                '<h6 class="mb-0 ms-2 text-truncate">' + file.name + '</h6> </div> </td> <td>' + file.size.string + '</td> <td>' + file.dated.datetime + '</td>' +
                '<td> <div class="user-group p-1">' + usersContent + '  </div> </td>' +
                '<td> <div class="d-flex flex-wrap gap-2">' + tagsContent + ' </div> </td>' +
                '<td> <ul class="list-inline text-end"> <li class="list-inline-item mx-2"> ' + file.visibility.icon + ' </li>' +
                '<li class="list-inline-item"> <a href="#" class="btn btn-outline-info btn-sm"> <i data-feather="eye" class="text-info"></i> details</a> </li> </ul> </td> </tr>';
            if (prepend) {
                $('#fileContents').prepend(content);
            } else {
                $('#fileContents').append(content);
            }
        }
    </script>
@endsection
