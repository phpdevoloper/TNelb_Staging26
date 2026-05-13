 <table class="table-login">
    <thead>
        <tr>
            <th>S.No</th>
            <th>Form Type</th>
            <th>Application ID</th>
            <th>Applied On</th>
            <th>Application Status</th>
            <th>Payment Status</th>
            <th>Acknowledgement Download</th>
            <th>Certificate Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($paginatedData as $index => $workflow)
        @php
            $sts = data_get($workflow, 'status') ?? data_get($workflow, 'application_status') ?? data_get($workflow, 'app_status');
        @endphp
        <tr @if($sts == 'QU') class="return-row" @endif>
            <td @if($sts == 'QU') class="return-cell" @endif>
                @if($sts == 'QU')
                    <div class="return-ribbon-wrapper">
                        <span class="return-ribbon">RETURNED</span>
                    </div>
                @endif
                {{ $paginatedData->firstItem() + $index }}
            </td>
            <td style="width: 18%;">
              @php
             $licence_name_present = DB::table('mst_licences')

            ->where('form_code', $workflow->form_name)
             ->first();
                @endphp    
                {{ $licence_name_present->licence_name }} <br>
            [Form {{ strtoupper($workflow->form_name ?? 'NA') }}]
            </td>
            <td>{{ $workflow->application_id ?? 'NA' }}</td>
            <td>{{ isset($workflow->created_at) ? \Carbon\Carbon::parse($workflow->created_at)->format('d/m/Y') : 'NA' }}</td>

            <!-- Application Status -->
            <td>
                @if ($workflow->payment_status == 'draft')
                    @php
                        $view_page =
                            isset($workflow->appl_type) && $workflow->appl_type == 'R'
                                ? 'renew_form'
                                : (in_array(strtoupper($workflow->form_name), ['P']) ? 'edit-application_p' : 'edit-application');
                    @endphp
                    <a href="{{ route($view_page, ['application_id' => $workflow->application_id]) }}">
                        <button class="btn btn-primary btn-sm"><i class="fa fa-pencil"></i> Draft</button>
                    </a>
                @else
                    @if ($sts == 'P')
                        <span class="btn btn-sm btn-primary">Submitted</span>
                    @elseif ($sts == 'F')
                        <span class="btn btn-warning btn-sm">In Progress</span>
                    @elseif ($sts == 'QU')
                        <span class="btn btn-sm bg-return">Returned</span>
                    @elseif ($sts == 'RJ')
                        <span class="btn btn-danger btn-sm">Rejected</span>
                    @elseif ($sts == 'RE')
                        <span class="btn btn-info btn-sm">Resubmitted</span>
                    @elseif ($sts == 'A' && !empty($workflow->license_number))
                        <span class="btn btn-sm btn-success">Completed</span>
                    @elseif ($sts == 'A')
                        <span class="btn btn-sm btn-success">Approved</span>
                    @else
                        <span class="btn btn-warning btn-sm">In Progress</span>
                    @endif
                @endif
            </td>

            <!-- Payment Status -->
            <td>
                @if ($workflow->payment_status == 'payment')
                    <p class="text-success"><strong>Success</strong></p>
                @else
                    <p class="text-warning"><strong>Pending</strong></p>
                @endif
            </td>

            <!-- Acknowledgement Download -->
            <td>
                @if ($workflow->payment_status == 'draft')
                    <p>-</p>
                @else
                    @if ($workflow->form_name == 'P')
                        <a href="{{ route('generatePDFFormP-ta.pdf', ['login_id' => $workflow->application_id]) }}" target="_blank">
                            <i class="fa fa-file-pdf-o" style="font-size:20px;color:red"></i>
                            <span style="font-size: x-small;">தமிழ்</span>
                        </a>
                        <a href="{{ route('generateformP.pdf', ['login_id' => $workflow->application_id]) }}" target="_blank">
                            <i class="fa fa-file-pdf-o" style="font-size:20px;color:red"></i>
                            <span style="font-size: x-small;">English</span>
                        </a>
                    @else
                        <a href="{{ route('generate.tamil.pdf', ['login_id' => $workflow->application_id]) }}" target="_blank">
                            <i class="fa fa-file-pdf-o" style="font-size:20px;color:red"></i>
                            <span style="font-size: x-small;">தமிழ்</span>
                        </a>

                        <a href="{{ route('generate.pdf', ['login_id' => $workflow->application_id]) }}" target="_blank">
                            <i class="fa fa-file-pdf-o" style="font-size:20px;color:red"></i>
                            <span style="font-size: x-small;">English</span>
                        </a>
                    @endif
                @endif
            </td>

            <!-- License Number -->
            <td>
                @if (!empty($workflow->license_number) && $sts == 'A')
                    <a href="{{ route('admin.getLicenceDoc.pdf', ['application_id' => $workflow->application_id]) }}" target="_blank" 
                        data-bs-toggle="tooltip" data-bs-placement="top" title="View Licence Details">
                        <span class="badge badge-info">{{ $workflow->license_number }}</span>
                    </a><br>

                    @if ($workflow->form_name == 'P')
                        {{-- Form P: encrypted English and Tamil licence PDFs from private storage --}}
                        <a href="{{ route('admin.formp.licence.en', ['application_id' => $workflow->application_id]) }}" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="Download Form P Licence (English)">
                            <i class="fa fa-file-pdf-o" style="font-size:14px;color:red"></i>
                            <span class="badge outline-badge-info" style="font-size:10px;">ENG</span>
                        </a>
                        <a href="{{ route('admin.formp.licence.ta', ['application_id' => $workflow->application_id]) }}" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="Download Form P Licence (Tamil)">
                            <i class="fa fa-file-pdf-o" style="font-size:14px;color:red"></i>
                            <span class="badge outline-badge-info" style="font-size:10px;">TAM</span>
                        </a>
                    @else
                        <a href="{{ route('admin.generate.pdf', ['application_id' => $workflow->application_id]) }}" target="_blank"  data-bs-toggle="tooltip" data-bs-placement="top" title="Download Licence (English)">
                            <i class="fa fa-file-pdf-o" style="font-size:14px;color:red"></i>
                            <span class="badge outline-badge-info" style="font-size:10px;">ENG</span>
                        </a>
                        <a href="{{ route('admin.competency-certificate-tamil.pdf', ['application_id' => $workflow->application_id]) }}" target="_blank"  data-bs-toggle="tooltip" data-bs-placement="top" title="Download Licence (Tamil)">
                            <i class="fa fa-file-pdf-o" style="font-size:14px;color:red"></i>
                            <span class="badge outline-badge-info" style="font-size:10px;">TAM</span>
                        </a>
                    @endif
                    <br>

                    @if (!empty($workflow->renewals))
                        <span class="text-muted" style="font-size: 12px;">
                            Renewed {{ count($workflow->renewals) }} times
                        </span>
                        <br>
                    @endif

                    @if (!empty($workflow->renewal_application_id))
                        <strong>Renewal Application</strong><br>
                        ID :
                        <a href="{{ route('generate.pdf', ['login_id' => $workflow->renewal_application_id]) }}"
                            target="_blank" class="text-success">
                            {{ $workflow->renewal_application_id }}
                        </a>
                    @else
                        @if ($workflow->is_under_validity_period)
                            <a href="{{ route('renew_form', ['application_id' => $workflow->application_id]) }}"
                                class="text-primary">
                                (Apply for renewal)
                            </a>
                        @endif
                    @endif
                @elseif (!empty($workflow->renewal_application_id))
                    <strong>Renewal Application</strong><br>
                    ID : <span class="text-success">{{ $workflow->renewal_application_id }}</span>
                @elseif($sts == 'QU')
                    <a href="{{ route(in_array(strtoupper($workflow->form_name ?? ''), ['P']) ? 'edit-application_p' : 'edit_returned_application', ['application_id' => $workflow->application_id]) }}">
                        <button class="btn btn-primary btn-sm"><i class="fa fa-pencil"></i> Edit</button>
                    </a>
                @else
                    <p class="text-primary">NA</p>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="mt-3">
    {{ $paginatedData->links('pagination::bootstrap-5') }}
</div>
