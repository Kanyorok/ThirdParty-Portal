@extends('layouts.app')
@section('title', 'Static Account Hierarchy')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">🌳 Static Account Hierarchy Viewer</h4>

        <div class="card">
            <div class="card-body">
                <div class="accordion" id="staticAccountTree">
                    <!-- Parent: Assets -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingAssets">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseAssets" aria-expanded="true"
                                    aria-controls="collapseAssets">
                                1000 – Assets
                            </button>
                        </h2>
                        <div id="collapseAssets" class="accordion-collapse collapse show"
                             aria-labelledby="headingAssets" data-bs-parent="#staticAccountTree">
                            <div class="accordion-body ps-4">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <a href="#"><strong>1100</strong> – Cash at Bank</a>
                                    </li>
                                    <li class="list-group-item">
                                        <a href="#"><strong>1200</strong> – Accounts Receivable</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Parent: Liabilities -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingLiabilities">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseLiabilities" aria-expanded="false"
                                    aria-controls="collapseLiabilities">
                                2000 – Liabilities
                            </button>
                        </h2>
                        <div id="collapseLiabilities" class="accordion-collapse collapse"
                             aria-labelledby="headingLiabilities" data-bs-parent="#staticAccountTree">
                            <div class="accordion-body ps-4">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <a href="#"><strong>2100</strong> – Accounts Payable</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
