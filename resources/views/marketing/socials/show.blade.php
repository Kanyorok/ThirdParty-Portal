@php use App\Models\User; @endphp
@extends('layouts.app')

@section('title')
    {{ $social->Type->name }} - {{ Str::upper($social->SocialID) }}
@endsection

@section('styles')
    <style>
        .comments {
            max-height: 300px;
            overflow-y: scroll;
            overflow-x: hidden;
        }
    </style>

@endsection
@section('content')
    <div class="row">
        <div class="col-md-7 col-lg-8">
            <div class="card">
                @if(!is_null($video))
                    <!--data:video/mp4;base64,-->
                    <video
                        controls
                        src="{{ $video->image_src }}"
                        @if($images->count()>0)
                            poster="{{ $images->first()?->image_src }}"
                        @endif
                        class="card-img-top">
                        <img src="https://placehold.co/800?text=Browser+Does+Not+Support+embedded+Video&font=roboto"
                             class="card-img-top" alt="Sorry, your browser doesn't support embedded videos.">
                    </video>
                @elseif($images->count()>0)
                    <div id="{{ $social->SocialID }}-carousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            @for($x = 1; $x <= $images->count(); $x++)
                                <button type="button" data-bs-target="#{{ $social->SocialID}}-carousel"
                                        data-bs-slide-to="{{ $x }}" class="active" aria-current="true"
                                        aria-label="Image {{ $x }}"></button>
                            @endfor
                        </div>
                        <div class="carousel-inner">
                            @foreach($images as $image)
                                <div class="carousel-item {{ ($loop->first)?'active':'' }}">
                                    <img src="{{ $image->image_src }}" class="d-block w-100" alt="{{ $image->Name }}">
                                </div>
                            @endforeach

                        </div>
                        <button class="carousel-control-prev" type="button"
                                data-bs-target="#{{ $social->SocialID}}-carousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button"
                                data-bs-target="#{{ $social->SocialID}}-carousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                @endif
                <div class="card-body">
                    <h5 class="card-title">{{ $social->Content }}</h5>
                </div>
            </div>
            <div class="card">
                <div class="card-header pb-0">
                    <div class="card-title pb-0">
                        Comments ({{ number_format($social->CommentsCount) }}) <span class="float-end">
                            <button class="btn btn-primary btn-sm new-comment" data-parent="comments"
                                    data-route="{{ route('social-comment.store',[$social->SocialID]) }}"
                                    data-title="new comment" type="button">
                                <i class="fas fa-plus-circle"></i> new comment
                            </button>
                        </span>
                    </div>
                </div>
                <div class="card-body pt-0 px-2 pb-1">
                    <div id="comments" class="px-2 pt-0 w-100 comments" style="max-height: 100vh"
                         data-url="{{ route('social-comment.index',[$social->SocialID]) }}"></div>
                    <div class="d-grid text-center" id="commentsMessage"></div>
                </div>
            </div>
        </div>
        <div class="col-md-5 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-center">
                        {!! $social->Type->getIcon('fa-5x m-1') !!}
                    </div>

                    <ul class="list-group list-group-flush ">
                        <li class="list-group-item">ID: <b class="float-end">{{ Str::upper($social->SocialID) }}</b>
                        </li>
                        <li class="list-group-item">Social Id: <b class="float-end">{{ $social?->RemoteId }}</b></li>
                        <li class="list-group-item">Comments: <b
                                class="float-end">{{ number_format($social->CommentsCount) }}</b>
                        <li class="list-group-item">Likes: <b
                                class="float-end">{{ number_format($social->LikesCount) }}</b>
                        <li class="list-group-item">Views: <b
                                class="float-end">{{ number_format($social->ViewsCount) }}</b>
                        </li>
                        <li class="list-group-item">Published On: <span
                                class="float-end">{{ ($social->Published_at)?$social->Published_at->format('M d, Y H:i'):'Not Published' }}</span>
                        </li>
                        <li class="list-group-item">Created On: <span
                                class="float-end">{{ $social->CreatedOn->format('M d, Y H:i') }}</span></li>
                        <li class="list-group-item">Created By: <span
                                class="float-end">
                                @if($social->creator instanceof User)
                                    <details>
                                    <summary>{{ $social->creator->UserID }}</summary>
                                    <p>{{ $social->creator->Name }}</p>
                                </details>
                                @else
                                    {{  $social->CreatedBy }}
                                @endif
                            </span></li>
                        <li class="list-group-item">Modified On: <span
                                class="float-end">{{ $social->ModifiedOn->format('M d, Y H:i') }}</span></li>
                        <li class="list-group-item">Modified By: <span
                                class="float-end">
                                @if($social->modified instanceof User)
                                    <details>
                                    <summary>{{ $social->modified->UserID }}</summary>
                                    <p>{{ $social->modified->Name }}</p>
                                </details>
                                @else
                                    {{  $social->CreatedBy }}
                                @endif
                            </span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="socialActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">

                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        const $Modal = $('#socialActionsModal');
        window._commentPage = '{{ route('social-comment.index',[$social->SocialID]) }}';
        $(function () {



            {{--
            $(document).on('click', '.trash-comment', function () {
                let comment_id = $(this).data('info');
                $(".modal-item").addClass('d-none');
                $('#trashCommentModal').removeClass('d-none');
                $('#trashCommentForm').attr('action', '{{ route('social-comment.store',[$social->SocialID]) }}/' + comment_id);
                $('#trashCommentContent').html($('#comment-' + comment_id).html());
                $('.modal-title').html('<b>Trash</b> comment');
                $('.modal-dialog').removeClass('modal-lg');
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            });

            $('form#trashCommentForm').submit(async function (e) {
                e.preventDefault();
                let response = await saveForm($(this), $('#trashCommentBtn'), false, true, true)
                if (response) {
                    $('#commentBody-' + response.id).fadeOut(2000, 'swing');
                    $Modal.modal('hide');
                }
            });


            $(document).on('click', '.fetch-more-comments', function () {
                fetchComments();
            }); --}}

        });

    </script>
    @include('snippets.actions.comments', ['canComment'=>true])
@endsection
