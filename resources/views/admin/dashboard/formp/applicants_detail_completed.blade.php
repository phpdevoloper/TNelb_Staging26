@include('admin.include.top')
@include('admin.include.header')
@include('admin.include.navbar')

<style>
    .tab-content {
        padding: 0px 20px;
    }
</style>

<div id="content" class="main-content">
    <div class="layout-px-spacing">
        <div class="middle-content container-xxl p-0">
            <div class="secondary-nav">
                <div class="breadcrumbs-container" data-page-heading="Analytics">
                    <header class="header navbar navbar-expand-sm">
                        <a href="javascript:void(0);" class="btn-toggle sidebarCollapse" data-placement="bottom">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu">
                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <line x1="3" y1="18" x2="21" y2="18"></line>
                            </svg>
                        </a>
                        <div class="d-flex breadcrumb-content">
                            <div class="page-header">
                                <div class="page-title"></div>
                                <nav class="breadcrumb-style-one" aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.completed_applications') }}">Completed Applications</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">View Completed Form P</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </header>
                </div>
            </div>

            <div class="row layout-top-spacing">
                <div class="col-lg-12 layout-spacing">
                    <div class="statbox widget ">
                        <div class="widget-header applicant_details">
                            <div class="row">
                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                    <h4>
                                        Applicant Id : <span> {{ $applicant->application_id }}</span>
                                        Applicant Name : <span style="color:#098501;">{{ $applicant->applicant_name }} </span>
                                        D.O.B : <span style="color:#098501;">{{ $applicant->d_o_b }} ({{ $applicant->age }} years old) </span>
                                        Applied For : <span style="color:#098501;"> {{ $applicant->form_name }}  | License {{ $applicant->license_name }}</span>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tabsSimple" class="col-xl-7 col-6 layout-spacing">
                    <div class="statbox widget box box-shadow">
                        <div class="widget-header">
                            <div class="row">
                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                    <h4>Applicant's Details</h4>
                                </div>
                            </div>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="simple-tab">
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">Personal Details</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact-tab-pane" type="button" role="tab" aria-controls="contact-tab-pane" aria-selected="false">Payment Status</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">Check List</button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
                                        <div class="row mt-3 ">
                                            <div class="row">
                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <p><strong>Applicant Id:</strong></p>
                                                            <p><strong>Applicant Name:</strong></p>
                                                            <p><strong>Father's Name:</strong></p>
                                                            <p><strong>Address:</strong></p>
                                                            <p><strong>D.O.B & Age:</strong></p>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <p>{{ $applicant->application_id }}</p>
                                                            <p>{{ $applicant->applicant_name }}</p>
                                                            <p>{{ $applicant->fathers_name }}</p>
                                                            <p>{{ $applicant->applicants_address }}</p>
                                                            <p>{{ $applicant->d_o_b }} ({{ $applicant->age }} years old)</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3">
                                                    @if(isset($uploadedPhoto) && !empty($uploadedPhoto->upload_path))
                                                        <img src="{{ url($uploadedPhoto->upload_path) }}"
                                                            alt="Applicant Photo"
                                                            class="img-fluid rounded" width="150">
                                                    @else
                                                        <p>No photo available</p>
                                                    @endif
                                                </div>
                                            </div>

                                            <h6 class="mt-2 mb-2 fw-bold text-info">Educational Qualifications</h6>
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Degree</th>
                                                            <th>Institution</th>
                                                            <th>Year of Passing</th>
                                                            <th>Percentage</th>
                                                            <th>Documents</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($educationalQualifications as $education)
                                                            @php
                                                                $percentage = data_get($education, 'percentage');
                                                            @endphp
                                                            <tr>
                                                                <td style="max-width: 10%;">{{ $education->educational_level }}</td>
                                                                <td style="width: 20%;">{{ $education->institute_name }}</td>
                                                                <td style="width: 20%;">{{ $education->year_of_passing }}</td>
                                                                <td style="width: 20%;">{{ $percentage !== null && $percentage !== '' ? $percentage.'%' : 'N/A' }}</td>
                                                                <td style="text-align:center;">
                                                                    @if(isset($education->upload_document))
                                                                        <a href="{{ url($education->upload_document) }}" target="_blank">
                                                                            <i class="fa fa-file-pdf-o" style="font-size:28px;color:red"></i>
                                                                        </a>
                                                                    @else
                                                                        No Documents Uploaded
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="5" class="text-center">No educational details available.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>

                                            <h6 class="mt-2 mb-2 fw-bold text-info">Work Experience</h6>
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Company Name</th>
                                                            <th>Designation</th>
                                                            <th>Years of Experience</th>
                                                            <th>Documents</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($workExperience as $experience)
                                                            <tr>
                                                                <td>{{ $experience->company_name }}</td>
                                                                <td>{{ $experience->designation }}</td>
                                                                <td>{{ (format_total_exp_years($experience->experience ?? $experience->total_exp) ?? '—') }} years</td>
                                                                <td style="text-align:center;">
                                                                    @if($experience->upload_document)
                                                                        <a href="{{ url($experience->upload_document) }}" target="_blank">
                                                                            <i class="fa fa-file-pdf-o" style="font-size:28px;color:red"></i>
                                                                        </a>
                                                                    @else
                                                                        No Documents Uploaded
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="3" class="text-center">No work experience available.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>

                                            <h6 class="mt-2 mb-2 fw-bold text-info">Electrical Assistant Qualification Certificate</h6>
                                            <div class="row">
                                                <div class="col-lg-6 col-6">
                                                    <p><strong>License Number:</strong></p>
                                                    <p><strong>Date:</strong></p>
                                                </div>
                                                <div class="col-lg-6 col-6">
                                                    @php
                                                        if (empty($applicant->previously_number) || empty($applicant->previously_date)){
                                                            $value = 'No';
                                                        }else{
                                                            $value = ($applicant->previously_number ?: '') . ' , ' . (!empty($applicant->previously_date) ? format_date($applicant->previously_date) : '' . '<a href="">view</a>');
                                                        }
                                                    @endphp
                                                    <p>{{ $value }}</p>
                                                </div>
                                            </div>

                                            <hr>
                                            <h6 class="mt-2 mb-2 fw-bold text-info">Wireman.C.C/Wireman Helper.C.C issued by this Board?</h6>
                                            <div class="row">
                                                <div class="col-lg-6 col-6">
                                                    <p><strong>Wireman License Number:</strong></p>
                                                    <p><strong>Date:</strong></p>
                                                </div>
                                                <div class="col-lg-6 col-6">
                                                    @php
                                                        if (empty($applicant->certificate_no) || empty($applicant->certificate_date)){
                                                            $cert_no = 'No';
                                                        }else{
                                                            $cert_no = 'Yes, '.($applicant->certificate_no ?: '') . ' , ' . (!empty($applicant->certificate_date) ? format_date($applicant->certificate_date) : '' . '<a href="">view</a>');
                                                        }
                                                    @endphp
                                                    <p>{{ $cert_no }}</p>
                                                </div>
                                            </div>

                                            <hr>
                                            <div class="row">
                                                <div class="col-lg-6 col-6">
                                                    <p><strong>Aadhaar:</strong></p>
                                                </div>
                                                @php
                                                    try {
                                                        $decryptedaadhar = $applicant->aadhaar ? Crypt::decryptString($applicant->aadhaar) : null;
                                                    } catch (\Exception $e) {
                                                        $decryptedaadhar = null;
                                                    }
                                                    $masked = $decryptedaadhar && strlen($decryptedaadhar) === 12
                                                        ? str_repeat('X', 8) . substr($decryptedaadhar, -4)
                                                        : ($decryptedaadhar ? 'Invalid Aadhaar' : 'N/A');
                                                @endphp
                                                <div class="col-lg-6 col-6">
                                                    <p>{{ $masked }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel" aria-labelledby="contact-tab" tabindex="0">
                                        <div class="row text-center fw-bold border-bottom pb-2 mb-3 mt-3">
                                            <div class="col-lg-6 text-primary">
                                                Payment Details
                                            </div>
                                            <div class="col-lg-6 text-primary">
                                                Transaction Details
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-lg-6">
                                                <div class="row mt-3">
                                                    <div class="col-lg-6">
                                                        <p><strong>Application Type</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p>{{ ($applicant->appl_type ?? 'N') === 'R' ? 'Renewal Application' : 'New Application' }}</p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p><strong>Application Fees</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p>{{ $applicant->amount }}.00</p>
                                                    </div>
                                                    @if (!empty($applicant->late_fees))
                                                        <div class="col-lg-6">
                                                            <p><strong>Late Fees({{ $applicant->late_months }} Months)</strong></p>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <p>Rs.{{ $applicant->late_fees }}.00</p>
                                                        </div>
                                                    @endif
                                                    <div class="col-lg-6">
                                                        <p><strong>Date of application</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p>{{ !empty($applicant->transaction_date) ? format_date($applicant->transaction_date) : format_date_other($applicant->created_at ?? now()) }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="row mt-3">
                                                    <div class="col-lg-6">
                                                        <p><strong> Payment Status</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p class="badge badge-success">{{ strtoupper($applicant->payment_status) }}</p>
                                                    </div>

                                                    <div class="col-lg-6">
                                                        <p><strong> Transaction Id</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p>{{ $applicant->transaction_id }}</p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p><strong>Amount</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p>{{ $applicant->amount }}.00</p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p><strong>Payment mode:</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <P>{{ $applicant->payment_mode ?? 'UPI' }}</P>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p><strong> Payment Time</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p>{{ !empty($applicant->transaction_date) ? format_date($applicant->transaction_date) : format_date_other($applicant->created_at ?? now()) }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                                        <div class="row mt-3">
                                            <div class="col-lg-12">
                                                <p>This checklist is available only for reference in completed view.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div> {{-- tab-content --}}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right side: only Workflow (no query/remarks/buttons) --}}
                <div id="tabsSimple" class="col-xl-5 col-6 layout-spacing">
                    <div class="statbox widget box box-shadow">
                        <div class="widget-header">
                            <div class="row">
                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                    <h4>Workflow</h4>
                                </div>
                            </div>
                        </div>
                        <div class="widget-content widget-content-area pb-1">
                            <div class="mt-container mx-auto">
                                <div class="timeline-line">
                                    @foreach ($workflows as $row)
                                        <div class="item-timeline">
                                            <p class="t-time">{{ format_date_other($row->created_at) }}</p>
                                            <div class="t-dot {{ $row->appl_status == 'RE' ? 't-dot-danger' : ($row->appl_status == 'A' ? 't-dot-success' : 't-dot-info') }}"></div>
                                            <div class="t-text">
                                                @if ($row->appl_status == 'RE')
                                                    <p>Returned by {{ $row->processed_by }}</p>
                                                @elseif ($row->appl_status == 'A')
                                                    <p>Approved by {{ $row->processed_by }}</p>
                                                @else
                                                    <p>Processed by {{ $row->processed_by }}</p>
                                                @endif
                                                <p class="t-meta-time">
                                                    @if (isset($row->appl_status) && $row->appl_status == 'RJ' && !empty($row->reject_reason))
                                                        Reason: {{ $row->reject_reason }}
                                                    @else
                                                        @if (empty($row->name))
                                                            Approved by {{ $row->processed_by }}
                                                        @else
                                                            Forwarded to {{ $row->name }}<br>
                                                            @if(!empty($row->remarks)) Remarks: {{ $row->remarks }} @endif
                                                        @endif
                                                    @endif
                                                </p>
                                                @if(!empty($row->query_status) && $row->query_status == 'P' && !empty($row->queries))
                                                    <p class="text-danger small">
                                                        Query raised:
                                                        {{ is_string($row->queries) ? $row->queries : implode(', ', (array) json_decode($row->queries, true)) }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12 d-flex justify-content-end mb-3">
                    <a href="{{ route('admin.completed_applications') }}" class="btn btn-primary">
                        Back to Completed Applications
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.include.footer')

