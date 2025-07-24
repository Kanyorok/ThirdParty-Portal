<!-- Modal for GlType -->
<div class="modal fade" id="glTypeModal" tabindex="-1" aria-labelledby="glTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('glTypeSegmentValue.save') }}">
            @csrf
            @method('post')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">GL Type Segment Values</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center">
                            <thead class="table-light">
                            <tr>
                                <th scope="col">GL Type</th>
                                <th scope="col">Segment Value</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($accountTypes as $segment)
                                <tr>
                                    <td class="fw-semibold">{{ $segment->Description }}</td>
                                    <td>
                                        <input type="text" name="segment_values[{{ $segment->Value }}]" value="{{$segment->DisplayOrder}}" min="0" class="form-control" placeholder="Enter segment value">
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" type="submit" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit(); }">
                        Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
