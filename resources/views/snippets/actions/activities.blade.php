<script>
    $(function () {
        fetchActivities('activitiesMain', 'activitiesMessage');
    });

    async function fetchActivities(parent_id, actMsg_id) {
        const parent = $('#' + parent_id), actMsg = $('#' + actMsg_id);
        let url = parent.data('url');
        if (url === null) {
            actMsg.html('');
            return;
        }
        actMsg.html('<i class="fas fa-spinner fa-spin"></i> please wait');
        await $.get(url, function (data) {
            $.map(data.data, function (activity) {
                appendAct(parent, activity.Content.html);
            });
            url = data.links.next;
            if (url === null) {
                parent.data('url', null);
                actMsg.html('');
                return;
            }
            parent.data('url', url);
            let action = "fetchActivities('" + parent_id + "','" + actMsg_id + "')";
            actMsg.html('<button type="button" class="btn btn-primary" onclick="' + action + '">Load more</button>');
        }).fail(function (e) {
            formRequest(e)
        });
    }

    function appendAct(parent, activityContent, prepend = false) {
        if (prepend) {
            parent.prepend(activityContent);
        } else {
            parent.append(activityContent);
        }
    }
</script>
