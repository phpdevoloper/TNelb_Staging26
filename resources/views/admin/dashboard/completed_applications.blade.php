@include('admin.include.top')
@include('admin.include.header')
@include('admin.include.navbar')
<style>
    .president-dashboard-card {
        border: 1px solid #a7a7a7 !important;
    }

    .president-dashboard-card .card-header {
        border-bottom: 1px solid #a7a7a7 !important;
    }

    .bg-custom-card {
        background-color: rgb(239 241 243) !important;
    }

    /* Form code line under licence title — high contrast black */
    .completed-appl-form-label {
        color: #000000 !important;
    }

    .js-completed-badge.active-completed-filter {
        box-shadow: 0 0 0 2px rgba(0, 129, 199, 0.55);
        background-color: rgba(0, 129, 199, 0.12);
    }

    /* Ensure DataTables header text is visible */
    #secretary-inprogress-table thead th {
        background-color: #004185 !important;
        color: #ffffff !important;
        font-weight: 600;
    }

    #js-completed-applications-title {
        white-space: normal;
        line-height: 1.35;
    }

</style>
<div id="content" class="main-content">
    <div class="layout-px-spacing">
        <div class="middle-content container-xxl p-0">
             <!--  BEGIN BREADCRUMBS  -->
             <div class="secondary-nav">
                <div class="breadcrumbs-container" data-page-heading="Analytics">
                    <header class="header navbar navbar-expand-sm">
                        <a href="#" class="btn-toggle sidebarCollapse" data-placement="bottom">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu">
                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <line x1="3" y1="18" x2="21" y2="18"></line>
                            </svg>
                        </a>
                        <div class="d-flex breadcrumb-content">
                            <div class="page-header d-flex flex-column">
                                <nav class="breadcrumb-style-one" aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                                        </li>
                                        <li class="breadcrumb-item">
                                            <span class="mx-1">/</span>
                                        </li>
                                        <li class="breadcrumb-item active" aria-current="page">
                                            Completed Applications
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </header>
                </div>
            </div>
            <!--  END BREADCRUMBS  -->
            <div class="row dashboard" style="margin-top: 10px;">
                @if(!empty($competencyCards))
                    <div class="col-xl-4 col-lg-12 mb-4">
                        <div class="card h-100 shadow-none rounded-3 overflow-hidden president-dashboard-card">
                            <div class="card-header d-flex justify-content-between align-items-center border-0 border-bottom bg-info">
                                <h5 class="mb-0 text-white">Competency Certificates</h5>
                            </div>
                            <div class="card-body" style="padding: 7px 10px;">
                                @foreach(collect($competencyCards) as $summary)
                                    @php
                                        $badgeClass = $formColors[$summary['color_code'] ?? ''] ?? 'bg-secondary';
                                    @endphp
                                    <div class="d-flex align-items-center px-3 py-2 mb-1 rounded-3 bg-custom-card">
                                        @php
                                            // For contractor Form A cards, clicking "NEW/RENEWAL" should filter
                                            // contractor list by appl_type (N/R) based on staff role.
                                            $isFormAContractor = str_contains(mb_strtolower($summary['licence_name'] ?? ''), 'contractor')
                                                && strtoupper($summary['form_name'] ?? '') === 'FORM A';

                                            $roleName = $staff->name ?? '';
                                            $newHref = route('admin.view_applications', ['form_id' => $summary['id'], 'form_type' => 'N']);
                                            $renewHref = route('admin.view_applications', ['form_id' => $summary['id'], 'form_type' => 'R']);
                                            $rawCode = strtoupper((string) ($summary['form_name'] ?? ''));
                                            $compactCode = preg_replace('/[^A-Z0-9]/', '', $rawCode);
                                            $formCode = $compactCode !== '' ? substr($compactCode, -1) : '?';
                                        @endphp
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <div class="rounded-3 d-flex align-items-center justify-content-center {{ $badgeClass }}" style="width: 44px; height: 44px;">
                                                        <span class="fw-bold text-white">{{ $formCode }}</span>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-semibold">{{ $summary['licence_name'] ?? 'Unknown Licence' }}</div>
                                                    <small class="d-block mb-1 completed-appl-form-label">{{ $summary['form_name'] ?? '-' }}</small>
                                                    <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                                        @php
                                                            $completedNew = (int) ($summary['completed_new_count'] ?? 0);
                                                            $completedRenewal = (int) ($summary['completed_renewal_count'] ?? 0);
                                                        @endphp
                                                        <span class="fw-semibold text-muted me-1">Completed :</span>
                                                        <a href="#"
                                                            class="badge outline-badge-info fw-semibold text-decoration-none js-completed-badge @if ($loop->first) js-completed-badge-default @endif"
                                                            data-form-id="{{ $summary['id'] ?? '' }}"
                                                            data-form-type="N"
                                                            data-licence-name="{{ $summary['licence_name'] ?? '' }}">
                                                            New
                                                            <span class="ms-1 fw-bold text-danger">{{ $completedNew }}</span>
                                                        </a>
                                                        <a href="#"
                                                            class="badge outline-badge-info fw-semibold text-decoration-none js-completed-badge"
                                                            data-form-id="{{ $summary['id'] ?? '' }}"
                                                            data-form-type="R"
                                                            data-licence-name="{{ $summary['licence_name'] ?? '' }}">
                                                            Renewal
                                                            <span class="ms-1 fw-bold text-danger">{{ $completedRenewal }}</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                
                @if(!empty($contractorCards))
                <div class="col-xl-4 col-lg-12 mb-4">
                    <div class="card h-100 shadow-none rounded-3 overflow-hidden president-dashboard-card">
                        <div class="card-header d-flex justify-content-between align-items-center border-0 border-bottom bg-info">
                            <h5 class="mb-0 text-white">Contractor Licences</h5>
                        </div>
                        <div class="card-body" style="padding: 7px 10px;">
                            @foreach(collect($contractorCards) as $summary)
                            @php
                                $badgeClass = $formColors[$summary['color_code'] ?? ''] ?? 'bg-secondary';
                                // var_dump($summary);die;
                            @endphp
                            <div class="d-flex align-items-center px-3 py-2 mb-1 rounded-3 bg-custom-card">
                                @php
                                    // For contractor Form A cards, clicking "New" should go to the
                                    // existing Form A applications list (/admin/view_form/A).
                                    $isFormAContractor = str_contains(mb_strtolower($summary['licence_name'] ?? ''), 'contractor')
                                        && strtoupper($summary['form_name'] ?? '') === 'FORM A';

                                    $newHref = $isFormAContractor
                                        ? route('admin.view_form', ['type' => 'A'])
                                        : route('admin.view_applications', ['form_id' => $summary['id'], 'form_type' => 'N']);
                                    $renewHref = $isFormAContractor
                                        ? route('admin.view_form', ['type' => 'A'])
                                        : route('admin.view_applications', ['form_id' => $summary['id'], 'form_type' => 'R']);
                                @endphp
                                @php
                                    $rawCode = strtoupper((string) ($summary['form_name'] ?? ''));
                                    $compactCode = preg_replace('/[^A-Z0-9]/', '', $rawCode);
                                    $formCode = $compactCode !== '' ? substr($compactCode, -1) : '?';
                                @endphp
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div class="rounded-3 d-flex align-items-center justify-content-center {{ $badgeClass }}" style="width: 44px; height: 44px;">
                                                <span class="fw-bold text-white">{{ $formCode }}</span>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $summary['licence_name'] ?? 'Unknown Licence' }}</div>
                                            <small class="d-block mb-1 completed-appl-form-label">{{ $summary['form_name'] ?? '-' }}</small>
                                            <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                                @php
                                                    $completedNew = (int) ($summary['completed_new_count'] ?? 0);
                                                    $completedRenewal = (int) ($summary['completed_renewal_count'] ?? 0);
                                                @endphp
                                                <span class="fw-semibold text-muted me-1">Completed :</span>
                                                <a href="#"
                                                    class="badge outline-badge-info fw-semibold text-decoration-none js-completed-badge @if (empty($competencyCards) && $loop->first) js-completed-badge-default @endif"
                                                    data-form-id="{{ $summary['id'] ?? '' }}"
                                                    data-form-type="N"
                                                    data-licence-name="{{ $summary['licence_name'] ?? '' }}">
                                                    New
                                                    <span class="ms-1 fw-bold text-danger">{{ $completedNew }}</span>
                                                </a>
                                                <a href="#"
                                                    class="badge outline-badge-info fw-semibold text-decoration-none js-completed-badge"
                                                    data-form-id="{{ $summary['id'] ?? '' }}"
                                                    data-form-type="R"
                                                    data-licence-name="{{ $summary['licence_name'] ?? '' }}">
                                                    Renewal
                                                    <span class="ms-1 fw-bold text-danger">{{ $completedRenewal }}</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                @if(!empty($amendmentCards))
                <div class="col-xl-4 col-lg-12 mb-4">
                    <div class="card h-100 shadow-none rounded-3 overflow-hidden president-dashboard-card">
                        <div class="card-header d-flex justify-content-between align-items-center border-0 border-bottom bg-info">
                            <h5 class="mb-0 text-white">Amendments</h5>
                        </div>
                        <div class="card-body" style="padding: 7px 10px;">
                            @foreach(collect($amendmentCards) as $summary)
                            <div class="d-flex align-items-center px-3 py-2 mb-1 rounded-3 bg-custom-card">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ $summary['licence_name'] ?? 'Unknown Licence' }}</div>
                                    <small class="d-block mb-1 completed-appl-form-label">{{ $summary['form_name'] ?? '-' }}</small>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                        @php
                                            $completedNew = (int) ($summary['completed_new_count'] ?? 0);
                                            $completedRenewal = (int) ($summary['completed_renewal_count'] ?? 0);
                                        @endphp
                                        <span class="fw-semibold text-muted me-1">Completed :</span>
                                        <a href="#"
                                            class="badge outline-badge-info fw-semibold text-decoration-none js-completed-badge @if (empty($competencyCards) && empty($contractorCards) && $loop->first) js-completed-badge-default @endif"
                                            data-form-id="{{ $summary['id'] ?? '' }}"
                                            data-form-type="N"
                                            data-licence-name="{{ $summary['licence_name'] ?? '' }}">
                                            New
                                            <span class="ms-1 fw-bold text-danger">{{ $completedNew }}</span>
                                        </a>
                                        <a href="#"
                                            class="badge outline-badge-info fw-semibold text-decoration-none js-completed-badge"
                                            data-form-id="{{ $summary['id'] ?? '' }}"
                                            data-form-type="R"
                                            data-licence-name="{{ $summary['licence_name'] ?? '' }}">
                                            Renewal
                                            <span class="ms-1 fw-bold text-danger">{{ $completedRenewal }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @php
                // Default table header (first "New" badge: competency → contractor → amendment)
                $completedApplicationsDefaultTitleSuffix = '';
                if (!empty($competencyCards)) {
                    $firstCompleted = collect($competencyCards)->first();
                    $ln = trim((string) ($firstCompleted['licence_name'] ?? ''));
                    $completedApplicationsDefaultTitleSuffix = $ln !== '' ? ($ln . ' - New Application') : 'New Application';
                } elseif (!empty($contractorCards)) {
                    $firstCompleted = collect($contractorCards)->first();
                    $ln = trim((string) ($firstCompleted['licence_name'] ?? ''));
                    $completedApplicationsDefaultTitleSuffix = $ln !== '' ? ($ln . ' - New Application') : 'New Application';
                } elseif (!empty($amendmentCards)) {
                    $firstCompleted = collect($amendmentCards)->first();
                    $ln = trim((string) ($firstCompleted['licence_name'] ?? ''));
                    $completedApplicationsDefaultTitleSuffix = $ln !== '' ? ($ln . ' - New Application') : 'New Application';
                }
            @endphp
            
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info">
                            <h5 id="js-completed-applications-title" class="mb-0 text-white">Completed Application : @if($completedApplicationsDefaultTitleSuffix !== ''){{ $completedApplicationsDefaultTitleSuffix }}@endif</h5>
                        </div>
                        <div class="card-body" style="padding: 5px 15px;">
                            <div class="table-responsive">
                                <table id="secretary-inprogress-table" class="table dt-table-hover table-striped table-bordered zero-config" style="width:100%">
                                    <thead>
                                        <tr class="text-center">
                                            <th>S.No</th>
                                            <th>Application Id</th>
                                            <th>Applicant's Name</th>
                                            <th>Applied On</th>
                                            <th>Licence No</th>
                                            <th>Issued At</th>
                                            <th>Expires At</th>
                                            <th>Certificate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    @include('admin.include.footer')
    <script>
        (function() {
            if (typeof window.jQuery === 'undefined') {
                // Footer didn't load jQuery for some reason; avoid hard crash
                console.error('jQuery is not loaded; completed table click handler skipped.');
                return;
            }

            // English licence PDF (card) for competency/amendment applications
            const licenceEnUrlTemplate = "{{ route('admin.generateLicensePDF', ['application_id' => '__APP__']) }}";
            const licenceTaUrlTemplate = "{{ route('admin.licence.ta', ['application_id' => '__APP__']) }}";
            // Form P: single encrypted PDF (English + Tamil merged); any stream route serves the same file.
            const formPLicenceBilingualUrlTemplate = "{{ route('admin.formp.licence.en', ['application_id' => '__APP__']) }}";


            const formalicenceEnUrl = "{{ route('admin.licencepdf_cl.pdf', ['application_id' => '__APP__']) }}";

            const formalicenceTmUrl = "{{ route('admin.licencepdf_cl_tamil.pdf', ['application_id' => '__APP__']) }}";

            function escapeHtml(str) {
                return String(str ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            const completedTableTitlePrefix = 'Completed Application : ';

            function updateCompletedTableTitle($btn) {
                const $title = $('#js-completed-applications-title');
                if (!$title.length) return;

                let rawName = ($btn.attr('data-licence-name') || '').trim();
                if (!rawName) {
                    rawName = $btn.closest('.bg-custom-card').find('.fw-semibold').filter(function() {
                        return !$(this).hasClass('text-muted');
                    }).first().text().trim();
                }
                const formType = ($btn.attr('data-form-type') || '').trim();
                const typeLabel = formType === 'R' ? 'Renewal' : 'New Application';

                if (rawName) {
                    $title.text(completedTableTitlePrefix + rawName + ' - ' + typeLabel);
                } else {
                    $title.text(completedTableTitlePrefix.trim());
                }
            }

            function formatDateDDMMYYYY(val) {
                if (!val) return '';
                const raw = String(val).trim();
                // Handle "YYYY-MM-DD ..." / ISO
                const d = new Date(raw.replace(' ', 'T'));
                if (!isNaN(d.getTime())) {
                    const dd = String(d.getDate()).padStart(2, '0');
                    const mm = String(d.getMonth() + 1).padStart(2, '0');
                    const yyyy = d.getFullYear();
                    return `${dd}-${mm}-${yyyy}`;
                }
                // Fallback for "YYYY-MM-DD"
                const m = raw.match(/^(\d{4})-(\d{2})-(\d{2})/);
                if (m) return `${m[3]}-${m[2]}-${m[1]}`;
                return raw;
            }

            function renderRows($table, rows) {
                const $tbody = $table.find('tbody');
                const dt = ($.fn.DataTable && $.fn.DataTable.isDataTable($table)) ? $table.DataTable() : null;

                const htmlRows = (rows || []).map(function(r) {
                    const appId = escapeHtml(r.application_id);
                    const appName = escapeHtml(r.applicant_name);
                    const appliedOn = escapeHtml(formatDateDDMMYYYY(r.applied_on));
                    const licNo = escapeHtml(r.license_number);
                    const issuedAt = escapeHtml(formatDateDDMMYYYY(r.issued_at));
                    const expiresAt = escapeHtml(formatDateDDMMYYYY(r.expires_at));

                    const applicationIdRaw = r.application_id ? String(r.application_id) : '';
                    const formNameRaw = (r.form_name || '').toString().toUpperCase();
                    const formCodeRaw = (r.form_code || '').toString().toUpperCase();
                    const effectiveCode = formCodeRaw || formNameRaw;
                    const viewUrl = r.license_url ? String(r.license_url) : '#';

                    // Licence PDF links: Form P = one bilingual PDF; others = EN + TA
                    const enUrl = applicationIdRaw && effectiveCode !== 'P'
                        ? licenceEnUrlTemplate.replace('__APP__', applicationIdRaw)
                        : '#';
                    const taUrl = applicationIdRaw && effectiveCode !== 'P'
                        ? licenceTaUrlTemplate.replace('__APP__', applicationIdRaw)
                        : '#';

                      const licenseCell = applicationIdRaw
                ? (
                    formNameRaw === 'A'
                        ? `
                             <a href="${formalicenceEnUrl.replace('__APP__', applicationIdRaw)}"
                            class="text-decoration-none me-2"
                            target="_blank"
                            title="English PDF">
                                <i class="fa fa-file-pdf-o text-danger"></i> ENG
                            </a>

                              <a href="${formalicenceTmUrl.replace('__APP__', applicationIdRaw)}"
                            class="text-decoration-none me-2"
                            target="_blank"
                            title="Tamil PDF">
                                <i class="fa fa-file-pdf-o text-danger"></i>  தமிழ்
                            </a>
                        `
                        : effectiveCode === 'P'
                        ? `
                            <a href="${formPLicenceBilingualUrlTemplate.replace('__APP__', applicationIdRaw)}"
                                class="text-decoration-none"
                                target="_blank"
                                title="Form P licence (English & Tamil)">
                                <i class="fa fa-file-pdf-o text-danger"></i> Download
                            </a>
                        `
                        : `
                            <a href="${enUrl}" class="text-decoration-none me-2" target="_blank" title="English PDF">
                                <i class="fa fa-file-pdf-o text-danger"></i> ENG
                            </a>
                            <a href="${taUrl}" class="text-decoration-none" target="_blank" title="Tamil PDF">
                                <i class="fa fa-file-pdf-o text-danger"></i> TAM
                            </a>
                        `
                )
                : '';

                    return `
                        <tr>
                            <td>${escapeHtml(r.sno)}</td>
                            <td>${appId ? (viewUrl !== '#' ? `<a href="${viewUrl}" class="fw-semibold text-primary" target="_blank">${appId}</a>` : `<span class="fw-semibold">${appId}</span>`) : ''}</td>
                            <td>${appName}</td>
                            <td>${appliedOn}</td>
                            <td>${licNo}</td>
                            <td>${issuedAt}</td>
                            <td>${expiresAt}</td>
                            <td>${licenseCell}</td>
                        </tr>
                    `;
                }).join('');

                if (dt) {
                    dt.clear();
                    // Feed DataTable with DOM rows
                    const temp = document.createElement('tbody');
                    temp.innerHTML = htmlRows;
                    const data = Array.from(temp.querySelectorAll('tr')).map(tr => Array.from(tr.children).map(td => td.innerHTML));
                    dt.rows.add(data).draw();
                } else {
                    $tbody.html(htmlRows);
                }
            }

            $(document).on('click', '.js-completed-badge', function(e) {
                e.preventDefault();

                const $btn = $(this);
                updateCompletedTableTitle($btn);

                const formIdRaw = ($btn.attr('data-form-id') || '').trim();
                if (formIdRaw === '') {
                    return;
                }

                const formType = ($btn.attr('data-form-type') || '').trim();

                const $table = $('#secretary-inprogress-table');
                if (!$table.length) return;

                $('.js-completed-badge').removeClass('active-completed-filter');
                $btn.addClass('active-completed-filter');

                // Simple loading state
                const $tbody = $table.find('tbody');
                $tbody.html('<tr><td colspan="8" class="text-center">Loading...</td></tr>');

                const ajaxData = { form_id: formIdRaw };
                if (formType === 'N' || formType === 'R') {
                    ajaxData.form_type = formType;
                }

                $.ajax({
                    url: "{{ route('admin.completed_applications.data') }}",
                    method: "GET",
                    data: ajaxData,
                    success: function(resp) {
                        renderRows($table, (resp && resp.data) ? resp.data : []);
                        // scroll to table
                        const el = document.getElementById('secretary-inprogress-table');
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    },
                    error: function(xhr) {
                        const msg = (xhr && xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to load data';
                        $tbody.html(`<tr><td colspan="8" class="text-center text-danger">${escapeHtml(msg)}</td></tr>`);
                    }
                });
            });

            $(function() {
                const $default = $('.js-completed-badge-default').first();
                if ($default.length) {
                    updateCompletedTableTitle($default);
                    $default.trigger('click');
                }
            });
        })();
    </script>
</div>

