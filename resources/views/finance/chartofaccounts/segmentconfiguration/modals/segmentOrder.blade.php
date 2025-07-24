    <!-- Modal for Segment Order -->
    <div class="modal fade" id="segmentOrderModal" tabindex="-1" aria-labelledby="segmentOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('segment-order.save') }}">
                @csrf
                @method('post')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Segment Order</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <ul id="segmentList" class="list-group">
                            @foreach($segments as $segment)
                                <li class="list-group-item d-flex align-items-center justify-content-between border rounded mb-2 shadow-sm p-3 bg-light"
                                    data-segment="{{ $segment->SegmentType }}">
                                    <span class="fw-bold">{{ $segment->SegmentType }}</span>
                                    <i class="fas fa-grip-vertical fs-4 text-muted drag-handle"></i>
                                </li>
                            @endforeach
                        </ul>
                        <input type="hidden" name="segment_order" id="segmentOrderInput">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success" type="button" onclick="submitSegmentOrder(this)">
                            Save Order
                        </button>

                    </div>
                </div>
            </form>
        </div>
    </div>
