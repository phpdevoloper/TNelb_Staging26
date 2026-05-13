@include('admin.include.top')
@include('admin.include.header')
@include('admin.include.navbar')

<style>
    .seperator-header h4 {
        background: #ffcc00;
        color: #333;
        padding: 10px 15px;
        font-size: 20px;
        font-weight: bold;
        text-transform: uppercase;
        border-radius: 5px;
        text-align: center;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
    }

    table th {
        background-color: #004185 !important;
        color: #ffffff !important;
    }
</style>

<div id="content" class="main-content">
    <div class="layout-px-spacing">
        <div class="middle-content container-xxl p-0">
            <div class="secondary-nav">
                <div class="breadcrumbs-container">
                    <header class="header navbar navbar-expand-sm">
                        <a href="javascript:void(0);" class="btn-toggle sidebarCollapse" data-placement="bottom">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="feather feather-menu">
                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <line x1="3" y1="18" x2="21" y2="18"></line>
                            </svg>
                        </a>
                        <div class="d-flex breadcrumb-content">
                            <nav class="breadcrumb-style-one" aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.completed_applications') }}">Completed Applications</a>
                                    </li>
                                    <li class="breadcrumb-item active">View Application</li>
                                </ol>
                            </nav>
                        </div>
                    </header>
                </div>
            </div>

            <div class="row layout-top-spacing">
                {{-- Full Applicant Details (reference: formp applicants_detail, without actions) --}}
                <div class="col-lg-12 layout-spacing">
                    <div class="statbox widget box box-shadow">
                        <div class="widget-header">
                            <h4>Applicant's Details</h4>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="row mt-3">
                                <div class="row">
                                    <div class="col-lg-9">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <p><strong>Applicant Id:</strong></p>
                                                <p><strong>Applicant Name:</strong></p>
                                                <p><strong>Father's Name:</strong></p>
                                                <p><strong>Address:</strong></p>
                                                <p><strong>D.O.B &amp; Age:</strong></p>
                                            </div>
                                            <div class="col-lg-6">
                                                <p>{{ $applicant->application_id }}</p>
                                                <p>{{ $applicant->applicant_name }}</p>
                                                <p>{{ $applicant->fathers_name }}</p>
                                                <p>{{ $applicant->applicants_address }}</p>
                                                <p>{{ $applicant->d_o_b }} @if(!empty($applicant->age)) ({{ $applicant->age }} years old) @endif</p>
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

                                {{-- Educational Qualifications --}}
                                <h6 class="mt-3 mb-2 fw-bold text-info">Educational Qualifications</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Degree</th>
                                                <th>Institution</th>
                                                <th>Year of Passing</th>
                                                <th>Certificate No</th>
                                                <th>Documents</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($educationalQualifications ?? [] as $education)
                                                <tr>
                                                    <td style="max-width: 10%;">{{ $education->educational_level }}</td>
                                                    <td style="width: 20%;">{{ $education->institute_name }}</td>
                                                    <td style="width: 20%;">{{ $education->year_of_passing }}</td>
                                                    @php
                                                        // Some forms only have certificate_no (no percentage column)
                                                        $certificateNo = data_get($education, 'certificate_no');
                                                    @endphp
                                                    <td style="width: 20%;">
                                                        @if($certificateNo !== null && $certificateNo !== '')
                                                            {{ $certificateNo }}
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td style="text-align:center; width: 15%;">
                                                        @if(!empty($education->upload_document))
                                                            <a href="{{ url($education->upload_document) }}" target="_blank">
                                                                <i class="fa fa-file-pdf-o" style="font-size:20px;color:red"></i>
                                                            </a>
                                                        @else
                                                            <span class="text-muted">No document</span>
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

                                @php
                                    $formNameUpper = strtoupper((string)($applicant->form_name ?? ''));
                                    $showWorkExperience = in_array($formNameUpper, ['FORM S', 'S', 'FORM W', 'W']);
                                @endphp

                                @if($showWorkExperience)
                                    {{-- Work Experience --}}
                                    <h6 class="mt-3 mb-2 fw-bold text-info">Work Experience</h6>
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
                                                @forelse ($workExperience ?? [] as $experience)
                                                    <tr>
                                                        <td>{{ $experience->company_name }}</td>
                                                        <td>{{ $experience->designation }}</td>
                                                        <td>{{ $experience->experience }} years</td>
                                                        <td style="text-align:center;">
                                                            @if(!empty($experience->upload_document))
                                                                <a href="{{ url($experience->upload_document) }}" target="_blank">
                                                                    <i class="fa fa-file-pdf-o" style="font-size:20px;color:red"></i>
                                                                </a>
                                                            @else
                                                                <span class="text-muted">No document</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center">No work experience available.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                {{-- Aadhaar (masked) if available --}}
                                @php
                                    try {
                                        $decryptedaadhar = isset($applicant->aadhaar) ? Crypt::decryptString($applicant->aadhaar) : null;
                                    } catch (\Exception $e) {
                                        $decryptedaadhar = null;
                                    }
                                    $masked = $decryptedaadhar && strlen($decryptedaadhar) === 12
                                        ? str_repeat('X', 8) . substr($decryptedaadhar, -4)
                                        : ($decryptedaadhar ? 'Invalid Aadhaar' : 'N/A');
                                @endphp

                                <h6 class="mt-3 mb-2 fw-bold text-info">Identity Details</h6>
                                <div class="row">
                                    <div class="col-lg-6 col-6">
                                        <p><strong>Aadhaar:</strong></p>
                                    </div>
                                    <div class="col-lg-6 col-6">
                                        <p>{{ $masked }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Workflow timeline with time --}}
                <div id="timelineMinimal" class="col-lg-12 layout-spacing mt-2">
                    <div class="statbox widget box box-shadow">
                        <div class="widget-header">
                            <div class="row">
                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                    <h4>Workflow (with time)</h4>
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
                                                @php
                                                    $processedBy = $row->processed_by;
                                                    $roleLabel = $processedBy === 'SE' ? 'Secretary' : $processedBy;
                                                    $isApplicantResubmission = $row->appl_status == 'RE' && $processedBy === 'AP';
                                                @endphp

                                                @if ($isApplicantResubmission)
                                                    <p>Resubmitted by Applicant</p>
                                                @elseif ($row->appl_status == 'RE')
                                                    <p>Returned by {{ $roleLabel }}</p>
                                                @elseif ($row->appl_status == 'A')
                                                    <p>Approved by {{ $roleLabel }}</p>
                                                @elseif (isset($row->appl_status) && $row->appl_status == 'RJ')
                                                    <p class="text-danger">Rejected by {{ $roleLabel }}</p>
                                                @else
                                                    <p>Processed by {{ $roleLabel }}</p>
                                                @endif

                                                @if (!$isApplicantResubmission)
                                                    <p class="t-meta-time">
                                                        @if (isset($row->appl_status) && $row->appl_status == 'RJ' && !empty($row->reject_reason))
                                                            Reason: {{ $row->reject_reason }}
                                                        @else
                                                            @if (empty($row->name))
                                                                Approved by {{ $roleLabel }}
                                                            @else
                                                                Forwarded to {{ $row->name }}<br>
                                                                @if(!empty($row->remarks)) Remarks: {{ $row->remarks }} @endif
                                                            @endif
                                                        @endif
                                                    </p>
                                                @endif

                                                @if(!empty($row->query_status) && $row->query_status == 'P' && !empty($row->queries) && !$isApplicantResubmission)
                                                    <p class="text-danger small">
                                                        Query raised by {{ $roleLabel }}:
                                                        {{ is_string($row->queries) ? $row->queries : implode(', ', (array) json_decode($row->queries, true)) }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach

                                    @if($user_entry)
                                        <div class="item-timeline">
                                            <p class="t-time">{{ format_date_other($user_entry->created_at ?? $user_entry->dt_submit ?? $user_entry->updated_at ?? null) }}</p>
                                            <div class="t-dot t-dot-warning"></div>
                                            <div class="t-text">
                                            <p>Received from Applicant</p>
                                                <p class="t-meta-time">
                                                    Form: {{ $user_entry->form_name ?? 'N/A' }},
                                                    License: {{ $user_entry->license_name ?? 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
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

