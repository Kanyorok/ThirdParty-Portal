<div class="modal fade" id="SnippetCommentActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">..</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content text-center with-gradient d-none modal-item" id="trashCommentModal">
                    <h3 class="h3 text-center" id="trashCommentContent"></h3>
                    <div class="mt-2 mb-2">
                        Are you sure you want to trash this comment ?
                    </div>
                    <hr>
                    <form id="trashCommentForm" method="post"> @csrf
                        <div class="mt-4">@method('delete')
                            <button type="button" class="btn btn-success float-start"
                                    data-bs-dismiss="modal">
                                no, keep
                            </button>
                            <button class="btn btn-danger float-end" id="trashCommentBtn" type="submit"><i
                                    class="fas fa-trash"></i> yes, trash
                            </button>
                        </div>
                    </form>
                </div>
                <div class="onboarding-content with-gradient d-none modal-item" id="createCommentModal">
                    @if($canComment)
                        <form action="#" method="post" id="createCommentForm">
                            @csrf
                            <div class="d-flex align-items-start">
                                {!! auth()->user()->getImage(' width="36" height="36" class="rounded-circle me-2"') !!}
                                <div class="flex-grow-1">
                                    <label class="form-label" for="social_comment">{{ auth()->user()->UserID }}</label>
                                    <textarea name="social_comment" id="social_comment" class="form-control"
                                              required rows="2" maxlength="5000" minlength="2"></textarea>
                                    <p id="social_comment_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="createCommentBtn" type="submit">
                                    <i class="fas fa-plus-circle"></i> add comment
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-danger" role="alert">
                            <div class="alert-icon">
                                <i class="far fa-fw fa-bell"></i>
                            </div>
                            <div class="alert-message">
                                Comments has been disabled
                            </div>
                        </div>
                        <hr>
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary float-start"
                                    data-bs-dismiss="modal">
                                cancel
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const $CommentsModal = $('#SnippetCommentActionsModal');
    $(function () {

        $(document).on('click', '.new-comment', function () {
            $(".modal-item").addClass('d-none');
            $('#createCommentModal').removeClass('d-none');
            $('#createCommentForm').attr('action', $(this).data('route')).data('parent', $(this).data('parent'));
            $('.social_comment').html('');
            $('.modal-title').html($(this).data('title'));
            $CommentsModal.modal('show');
        });
        @if($canComment)
        $('form#createCommentForm').submit(async function (e) {
            e.preventDefault();
            const parent_id = $(this).data('parent')
            let response = await saveForm($(this), $('#createCommentBtn'), false, true, true)
            if (response) {
                appendComment($('#' + parent_id), response.data);
                $CommentsModal.modal('hide');
            }
        });
        @endif
        $(document).on('click', '.trash-comment', function () {
            let comment_id = $(this).data('info');
            $(".modal-item").addClass('d-none');
            $('#trashCommentModal').removeClass('d-none');
            $('#trashCommentForm').attr('action', window._commentPage + '/' + comment_id);
            $('#trashCommentContent').html($('#comment-' + comment_id).html());
            $('.modal-title').html('<b>Trash</b> comment');
            $('.modal-dialog').removeClass('modal-lg');
            $CommentsModal.children().first().removeClass('modal-lg');
            $CommentsModal.modal('show');
        });
        $('form#trashCommentForm').submit(async function (e) {
            e.preventDefault();
            let response = await saveForm($(this), $('#trashCommentBtn'), false, true, true)
            if (response) {
                $('#commentBody-' + response.id).fadeOut(2000, 'swing');
                $CommentsModal.modal('hide');
            }
        });

        fetchComments('comments', 'commentsMessage');
    });

    async function fetchComments(parent_id, cmtMsg_id) {
        const parent = $('#' + parent_id), cmtMsg = $('#' + cmtMsg_id);
        let url = parent.data('url');
        if (url === null) {
            cmtMsg.html('');
            return;
        }
        cmtMsg.html('<i class="fas fa-spinner fa-spin"></i> please wait');
        await $.get(url, function (data) {
            $.map(data.data, function (comment) {
                appendComment(parent, comment);
            });
            url = data.links.next;
            if (url === null) {
                parent.data('url', null);
                cmtMsg.html('');
                return;
            }
            parent.data('url', url);
            let action = "fetchComments('" + parent_id + "','" + cmtMsg_id + "')";
            cmtMsg.html('<button type="button" class="btn btn-primary" onclick="' + action + '">Load more</button>');
        }).fail(function (e) {
            formRequest(e)
        });
    }

    function appendComment(parent, comment/*, prepend = false*/) {
        let action = "fetchComments('commentComments-" + comment.id + "','commentCommentsMsg-" + comment.id + "')";
        let content = '<div id="commentBody-' + comment.id + '" class="mt-2 pt-2 border-1 border-top"><div class="d-flex align-items-start">' + comment.actor.avatar +
            '<div class="flex-grow-1"><div class="float-end"><small class="text-navy">' + comment.dated.sting + '</small>';
        if (comment.permission.cancelable) {
            content += '<a class="text-danger mx-2 trash-comment" data-info="' + comment.id + '" href="javascript: void(0);"><i class="fas fa-trash"></i></a>';
        }
        content += '</div><strong>' + comment.actor.name + '</strong>' +
            '<small class="text-muted mx-2">' + comment.dated.datetime +
            '</small><p id="comment-' + comment.id + '">' + comment.msg + '</p><div class="row"><div class="col"><i class="fas fa-heart"></i>(' + comment.extra.likes.string +
            ') Likes</div>';
        if (comment.extra.comments.numeric > 0) {
            content += '<a class="col" href="javascript: void(0);" onclick="' + action + '"><i class="fas fa-comments"></i> (' + comment.extra.comments.string +
                ') Comments</a>';
        } else {
            content += '<div class="col"><i class="fas fa-comments"></i> (' + comment.extra.comments.string +
                ') Comments</div>';
        }
        content += '<div class="col"><button class="btn  btn-link new-comment" data-parent="commentComments-' + comment.id + '" data-route="' + window._commentPage + '?CommentId=' + comment.id +
            '" data-title="reply to comment" type="button"> <i class="fas fa-reply"></i> reply</button></div>' +
            '<div class="col-12 my-2"><div id="commentComments-' + comment.id + '" data-url="' + window._commentPage + '?CommentId=' + comment.id +
            '"></div><div id="commentCommentsMsg-' + comment.id + '"></div> </div>';

        content += '</div></div></div></div>';
        /*if (prepend) {
            parent.prepend(content);
        } else {*/
        parent.append(content);
        //}
    }
</script>
