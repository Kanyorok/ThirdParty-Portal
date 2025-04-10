<div class="row">
    <div class="col-5 align-content-center text-center">
        <div>
            {!! $user->getImage('class="img-fluid img-thumbnail  mb-2" width="100" height="100"') !!}
        </div>
    </div>
    <div class="col-7 align-content-center ">
        <ul class="list-group list-group-flush">
            <li class="list-group-item p-1">
                <div class="h4 text-primary text-uppercase text-decoration-underline">{{ $user->UserID }}</div>
            </li>
            <li class="list-group-item p-1">{{ $user->Name }}</li>
            <li class="list-group-item p-1">{{ ($user->trashed())?"OLD":"" }} Employee</li>
        </ul>
    </div>
</div>
