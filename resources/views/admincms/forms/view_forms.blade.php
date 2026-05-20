@include('admincms.include.top')
@include('admincms.include.header')
@include('admincms.include.navbar')
<style>
    h4, h5 { color: #000; }
    .modal-title { color: #000; }

    /* ── Instruction editor modal — modern shell ─────────────────────────── */
    #exampleModal .modal-dialog { max-width: 1080px; }
    #exampleModal .modal-content {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(20, 30, 60, 0.18);
    }
    #exampleModal .modal-header {
        background: linear-gradient(135deg, #2c3e8f 0%, #4361ee 100%);
        color: #fff;
        padding: 16px 24px;
        border: 0;
    }
    #exampleModal .modal-header .modal-title { color: #fff; font-weight: 600; letter-spacing: 0.2px; }
    #exampleModal .modal-header .btn-close { filter: invert(1) brightness(2); opacity: 0.9; }
    #exampleModal .modal-body { padding: 20px 24px; background: #f6f8fc; }
    #exampleModal .modal-footer { background: #fff; border-top: 1px solid #eef0f5; padding: 14px 24px; }
    #exampleModal .modal-footer .btn { border-radius: 8px; padding: 8px 18px; font-weight: 500; }
    #exampleModal .modal-footer .btn-primary {
        background: linear-gradient(135deg, #4361ee, #3b58d8);
        border-color: #3b58d8;
        box-shadow: 0 6px 16px rgba(67, 97, 238, 0.28);
    }
    #exampleModal .modal-footer .btn-light-dark { background: #eef0f5; border: 0; color: #344767; }

    /* ── Help banner ─────────────────────────────────────────────────────── */
    .instruction-help {
        display: flex; gap: 12px; align-items: flex-start;
        background: #ffffff; border: 1px solid #e6e9f2;
        border-left: 4px solid #4361ee;
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 14px;
        font-size: 13px;
        color: #475569;
    }
    .instruction-help .help-icon {
        width: 28px; height: 28px; flex: 0 0 28px;
        border-radius: 50%; background: #eef2ff; color: #4361ee;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 700;
    }
    .instruction-help code { background: #f1f5f9; color: #c026d3; padding: 1px 6px; border-radius: 4px; font-size: 12px; }

    /* ── Merge tags strip ────────────────────────────────────────────────── */
    .quill-merge-tags-bar {
        display: flex; flex-wrap: wrap; align-items: center; gap: 10px;
        background: #ffffff;
        border: 1px solid #e6e9f2;
        border-radius: 10px;
        padding: 10px 12px;
        margin-bottom: 12px;
    }
    .quill-merge-tags-bar label {
        font-size: 12px; text-transform: uppercase; letter-spacing: 0.6px;
        color: #64748b; font-weight: 600; margin: 0;
    }
    .quill-merge-tags-bar .form-select {
        max-width: 320px;
        border-radius: 8px;
        border-color: #d6dbe5;
        background-color: #f8fafc;
        font-size: 13px;
        padding: 6px 32px 6px 12px;
    }
    .quill-merge-tags-bar .form-select:focus {
        border-color: #4361ee;
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
    }
    .quill-merge-tags-bar .merge-chip {
        font-size: 12px; padding: 4px 10px; border-radius: 999px;
        background: #eef2ff; color: #3b58d8; border: 1px solid #dbe2ff;
        cursor: pointer; transition: all 0.15s ease;
        font-weight: 500;
    }
    .quill-merge-tags-bar .merge-chip:hover { background: #3b58d8; color: #fff; border-color: #3b58d8; }

    /* ── Quill toolbar + editor card ─────────────────────────────────────── */
    .instruction-editor-card {
        background: #ffffff;
        border: 1px solid #e6e9f2;
        border-radius: 10px;
        overflow: hidden;
        display: flex; flex-direction: column;
    }
    #exampleModal #toolbar-container {
        border: 0 !important;
        border-bottom: 1px solid #eef0f5 !important;
        border-radius: 0 !important;
        background: #fafbff;
        padding: 8px 10px;
        flex: 0 0 auto;
    }
    #exampleModal #toolbar-container .ql-formats { margin-right: 10px; }
    #exampleModal #toolbar-container button:hover,
    #exampleModal #toolbar-container .ql-picker-label:hover {
        color: #4361ee !important;
    }
    #exampleModal #toolbar-container .ql-stroke { stroke: #475569; }
    #exampleModal #toolbar-container button:hover .ql-stroke { stroke: #4361ee; }
    /* Custom (non-format) buttons need their own visuals because Quill won't style them */
    #exampleModal #toolbar-container .ql-custom {
        width: 28px; height: 24px; display: inline-flex; align-items: center; justify-content: center;
        background: transparent; border: 0; color: #475569; cursor: pointer; padding: 0; margin: 0 2px;
    }
    #exampleModal #toolbar-container .ql-custom:hover { color: #4361ee; }
    #exampleModal #toolbar-container .ql-custom svg { width: 16px; height: 16px; }

    /* Single scrollbar — only the editor scrolls; modal body does not */
    #exampleModal .modal-body { display: flex; flex-direction: column; min-height: 0; }
    #exampleModal .instruction-editor-card { flex: 1 1 auto; min-height: 0; }
    #exampleModal #editor-container {
        flex: 1 1 auto;
        min-height: 320px;
        max-height: calc(85vh - 280px);
        overflow-y: auto;
        background: #fff;
        border: 0 !important;
        border-radius: 0 0 10px 10px;
        padding: 8px 4px;
    }
    /* Quill's default snow theme paints body text at rgba(0,0,0,.65) — force solid dark for readability */
    #exampleModal #editor-container .ql-editor,
    #exampleModal #editor-container .ql-editor p,
    #exampleModal #editor-container .ql-editor li,
    #exampleModal #editor-container .ql-editor span,
    #exampleModal #editor-container .ql-editor blockquote,
    #exampleModal #editor-container .ql-editor pre {
        color: #0f172a !important;
    }
    #exampleModal #editor-container .ql-editor {
        min-height: 300px;
        font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, 'Helvetica Neue', Arial, sans-serif;
        font-size: 15px;
        line-height: 1.7;
        padding: 18px 22px;
        font-weight: 400;
        letter-spacing: 0.1px;
    }
    #exampleModal #editor-container .ql-editor p { margin: 0 0 10px; }
    #exampleModal #editor-container .ql-editor h1 { font-size: 28px; font-weight: 700; margin: 18px 0 10px; color: #0b1220 !important; }
    #exampleModal #editor-container .ql-editor h2 { font-size: 22px; font-weight: 700; margin: 16px 0 8px;  color: #0b1220 !important; }
    #exampleModal #editor-container .ql-editor h3 { font-size: 18px; font-weight: 600; margin: 14px 0 6px;  color: #0b1220 !important; }
    #exampleModal #editor-container .ql-editor strong { color: #0b1220 !important; font-weight: 600; }
    #exampleModal #editor-container .ql-editor a { color: #2c54e0; text-decoration: underline; }
    #exampleModal #editor-container .ql-editor blockquote {
        border-left: 4px solid #4361ee; background: #f8fafc;
        padding: 8px 14px; margin: 12px 0; border-radius: 6px;
    }
    #exampleModal #editor-container .ql-editor pre.ql-syntax,
    #exampleModal #editor-container .ql-editor pre {
        background: #0f172a; color: #e2e8f0 !important; padding: 12px 14px;
        border-radius: 8px; font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace;
        font-size: 13px;
    }
    #exampleModal #editor-container .ql-editor code {
        background: #eef2ff; color: #4338ca; padding: 1px 6px; border-radius: 4px; font-size: 13px;
    }
    #exampleModal #editor-container .ql-editor hr {
        border: 0; border-top: 1px solid #d1d5db; margin: 14px 0;
    }
    #exampleModal #editor-container .ql-editor ::selection { background: #c7d2fe; color: #0f172a; }
    #exampleModal #editor-container .ql-editor.ql-blank::before {
        color: #94a3b8;
        font-style: normal;
        font-size: 15px;
        left: 22px; right: 22px;
    }

    /* ── Fullscreen mode ─────────────────────────────────────────────────── */
    #exampleModal.editor-fullscreen .modal-dialog { max-width: 100vw; width: 100vw; height: 100vh; margin: 0; }
    #exampleModal.editor-fullscreen .modal-content { height: 100vh; border-radius: 0; }
    #exampleModal.editor-fullscreen #editor-container { max-height: calc(100vh - 280px); }

    /* ── Tables inside editor ────────────────────────────────────────────── */
    #exampleModal #editor-container .ql-editor table {
        border-collapse: collapse;
        width: 100%;
        margin: 12px 0;
        font-size: 14px;
    }
    #exampleModal #editor-container .ql-editor table td,
    #exampleModal #editor-container .ql-editor table th {
        border: 1px solid #d0d7e5;
        padding: 8px 12px;
        vertical-align: top;
        min-width: 60px;
    }
    #exampleModal #editor-container .ql-editor table th { background: #eef2ff; color: #1e293b; font-weight: 600; }
    #exampleModal #editor-container .ql-editor table tr:nth-child(even) td { background: #fafbff; }
    #exampleModal #editor-container .ql-editor table tr:hover td { background: #f1f5ff; }

    /* ── Table grid picker popover (Google-Docs style) ───────────────────── */
    .ql-table-picker {
        position: absolute; z-index: 1080;
        background: #fff; border: 1px solid #e6e9f2; border-radius: 10px;
        box-shadow: 0 16px 40px rgba(20, 30, 60, 0.18);
        padding: 12px; display: none;
        min-width: 220px;
    }
    .ql-table-picker.show { display: block; }
    .ql-table-picker .picker-label {
        font-size: 12px; color: #475569; font-weight: 600;
        margin-bottom: 8px; text-align: center;
    }
    .ql-table-picker .picker-grid {
        display: grid; grid-template-columns: repeat(8, 22px); gap: 2px;
    }
    .ql-table-picker .picker-cell {
        width: 22px; height: 22px;
        border: 1px solid #d6dbe5; border-radius: 3px;
        background: #f8fafc; cursor: pointer;
        transition: background 0.08s ease, border-color 0.08s ease;
    }
    .ql-table-picker .picker-cell.hot { background: #c7d2fe; border-color: #4361ee; }
    .ql-table-picker .picker-readout {
        margin-top: 8px; text-align: center;
        font-size: 12px; color: #1e293b; font-weight: 500;
    }

    /* ── Table context floater (row/col actions) ─────────────────────────── */
    .ql-table-context {
        position: absolute; z-index: 1080;
        background: #1e293b; color: #fff;
        border-radius: 8px;
        padding: 4px;
        display: none;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.35);
    }
    .ql-table-context.show { display: inline-flex; gap: 2px; }
    .ql-table-context button {
        background: transparent; color: #fff; border: 0;
        padding: 6px 10px; font-size: 12px; cursor: pointer;
        border-radius: 6px; font-weight: 500;
    }
    .ql-table-context button:hover { background: #4361ee; }
    .ql-table-context .sep { width: 1px; background: #475569; margin: 4px 2px; }

    /* ── Hint + counter footer below editor ──────────────────────────────── */
    .editor-hint {
        font-size: 12px; color: #64748b; margin-top: 10px;
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    }
    .editor-hint .dot { width: 6px; height: 6px; border-radius: 50%; background: #4361ee; display: inline-block; }
    .editor-hint .counter {
        margin-left: auto;
        background: #ffffff; border: 1px solid #e6e9f2; border-radius: 999px;
        padding: 3px 12px; font-weight: 500; color: #475569;
    }
    .editor-hint .counter strong { color: #1f2937; font-weight: 600; }
</style>

{{--
    MERGE TAGS → Laravel: after you have HTML (from Quill root.innerHTML or a rendered view),
    replace placeholder tokens with str_replace. Keys must match MERGE_TAG_DEFINITIONS in script below.

    Store: JSON.stringify(quill.getContents()) — Delta keeps placeholder text as-is.

    Reload: quill.setContents(JSON.parse(storedDeltaJson));

    PHP example (use the same literal strings as inserted in the editor, e.g. braces + APPLICANT_NAME + braces):
        $map = [
            'literal-merge-token-1' => $applicant->name ?? '',
            'literal-merge-token-2' => $licence->license_number ?? '',
            // ... same keys as in MERGE_TAG_DEFINITIONS
        ];
        $html = str_replace(array_keys($map), array_values($map), $html);
--}}

<div id="content" class="main-content">
    <div class="layout-px-spacing">
        <div class="middle-content p-0">
            <div class="page-meta">
                <h4>Certificates & Licences</h4>
                <nav class="breadcrumb-style-one" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Licences Management</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Certificates & Licences</li>
                    </ol>
                </nav>
            </div>
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
                            <div class="page-header">
                                <div class="page-title"></div>
                                <nav class="breadcrumb-style-one" aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#">Content Management System for TNELB</a></li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                    </header>
                </div>
            </div>
            <!--  END BREADCRUMBS  -->

            <div class="row layout-top-spacing">
                <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing mb-5">
                    <div class="row">
                        {{-- <div class="col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <form id="addForms" class="simple-example" novalidate>
                                        <div class="mb-2">
                                            <label for="inputEmail4" class="form-label">Category<span class="text-danger">*</span> </label>
                                            <select class="form-select" name="form_cate" id="form_cate">
                                                <option value="">Please select category</option>
                                                @foreach ($categories as $item)
                                                    <option value="{{ $item->id }}">{{ $item->category_name }}</option>
                                                @endforeach
                                            </select>
                                            <small class="text-danger d-none error-form_cate">Please choose the category</small>
                                        </div>
                                        <div class="mb-2">
                                            <label for="inputEmail4" class="form-label">Certificate / Licence Name <span class="text-danger">*</span> </label>
                                            <input type="text" class="form-control" name="cert_name" id="cert_name">
                                            <small class="text-danger d-none error-cer_val">Please fill the Certificate / Licence Name</small>
                                        </div>
                                        <div class="mb-2">
                                            <label for="inputEmail4" class="form-label">Certificate / Licence Code <span class="text-danger">*</span> </label>
                                            <input type="text" class="form-control" name="cate_licence_code" id="cate_licence_code" maxlength="5" placeholder="eg.C,B">
                                            <small class="text-danger d-none error-cert_code">Please fill the Certificate / Licence Code</small>
                                        </div>
                                        <div class="mb-2">
                                            <label for="inputEmail4" class="form-label">Form Name <span class="text-danger">*</span> </label>
                                            <input type="text" class="form-control" name="form_name" id="form_name">
                                            <small class="text-danger d-none error-form_name">Please fill the Form Name</small>
                                        </div>
                                        <div class="mb-2">
                                            <label for="inputEmail4" class="form-label">Form Code <span class="text-danger">*</span> </label>
                                            <input type="text" class="form-control" name="form_code" id="form_code" maxlength="5" placeholder="eg.S,W">
                                            <small class="text-danger d-none error-form_code">Please choose the Form Code</small>
                                        </div>
                                        <div class="mb-2">
                                            <label for="inputEmail4" class="form-label">Status<span class="text-danger">*</span> </label>
                                            <select class="form-select" name="form_status" id="form_status">
                                                <option value="1">Active</option>
                                                <option value="2">In Active</option>
                                            </select>
                                            <small class="text-danger d-none error-form_status">Please choose the Form status</small>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Create</button>
                                    </form>
                                </div>
                            </div>
                        </div> --}}
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between">
                                    <h5>Add New Certificates / Licences</h5>
                                    <button class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addFormModal"><i class="fa fa-plus"></i> Add</button>
                                </div>
                                <div class="card-body">
                                    <table id="style-3" class="table style-2  dt-table-hover">
                                        <thead>
                                            <tr>
                                                
                                                <th class=""> S.No </th>
                                                <th>Certificate / Licence Name</th>
                                                <th>Certificate / Licence Code</th>
                                                <th>Form Name</th>
                                                <th>Form Code</th>
                                                <th>Category</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Created At</th>
                                                <th class="text-center dt-no-sorting">Instructions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($all_licences as $index => $row)
                                            <!-- {{ $index }} -->
                                                <tr>
                                                    <!-- S.No -->
                                                    <td class="text-center">{{ $index +1 }}</td>

                                                    <!-- Category Name -->
                                                    <td>{{ $row->licence_name }}</td>
                                                    <td>{{ $row->cert_licence_code }}</td>

                                                    <td>{{ $row->form_name }}</td>
                                                    <td>{{ $row->form_code }}</td>
                                                    
                                                    <td>{{ $row->category_name }}</td>

                                                    <!-- Status -->
                                                    <td class="text-center">
                                                        @if($row->status == 1)
                                                            <span class="badge outline-badge-success">Active</span>
                                                        @else
                                                            <span class="badge outline-badge-danger">Inactive</span>
                                                        @endif
                                                    </td>

                                                    <td class="text-center">{{ \Carbon\Carbon::parse($row->created_at)->format('d-m-Y') }}</td>

                                                    <!-- Action -->
                                                    <td class="text-center">
                                                        {{-- <a href="javascript:void(0);" class="bs-tooltip editForm" 
                                                            data-bs-toggle="modal" data-bs-target="#editFormModal" title="Edit"
                                                            data-row_id="{{ $row->id }}"
                                                            data-form_name="{{ $row->form_name }}"
                                                            data-licence_name="{{ $row->licence_name }}"
                                                            data-category="{{ $row->category_id }}"
                                                            data-cert_licence_code="{{ $row->cert_licence_code }}"
                                                            data-form_code="{{ $row->form_code }}"
                                                            data-status="{{ $row->status }}"
                                                            >
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" 
                                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" 
                                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                                                                class="feather feather-edit">
                                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                            </svg>
                                                        </a> --}}
                                                        <a href="javascript:void(0);" class="bs-tooltip editInstruction" data-bs-toggle="modal" data-bs-target="#exampleModal" title="Instructions"
                                                            data-licence_id="{{ $row->id }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text">
                                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                                <polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line>
                                                                <line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline>
                                                            </svg>
                                                        </a>
                                                    </td>
                                                    
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">No records found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Instruction Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="exampleModalLabel">Instructions &amp; Declaration</h5>
                    <small class="d-block mt-1" style="opacity: 0.85;">Write one template for this certificate — use merge tags for values that change.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                {{-- <div class="instruction-help">
                    <span class="help-icon">i</span>
                    <div>
                        Use <strong>Merge Tags</strong> for amounts that change per licence (fees are kept in <strong>Fees Management</strong>). Do not type rupee values by hand.
                        Tokens are resolved server-side by <code>LicenceManagementController::mergeInstructionTokens</code>.
                    </div>
                </div> --}}

                {{-- Merge tags must live OUTSIDE #toolbar-container — Quill rewrites that node and would remove a custom select. --}}
                <div class="quill-merge-tags-bar">
                    <label for="quill-merge-tags-select">Fees tags</label>
                    <select id="quill-merge-tags-select" class="form-select form-select-sm" title="Insert placeholder at cursor">
                        <option value="">— Choose tag to insert —</option>
                    </select>
                    <span class="text-muted small d-none d-md-inline">or quick insert:</span>
                    @verbatim
                    <button type="button" class="merge-chip" data-merge-tag="{{FEE_NEW}}">Fee · New</button>
                    <button type="button" class="merge-chip" data-merge-tag="{{FEE_RENEWAL}}">Fee · Renewal</button>
                    <button type="button" class="merge-chip" data-merge-tag="{{FEE_LATE}}">Fee · Late</button>
                    <button type="button" class="merge-chip" data-merge-tag="{{CERTIFICATE_NAME}}">Certificate</button>
                    <button type="button" class="merge-chip" data-merge-tag="{{FORM_NAME}}">Form</button>
                    @endverbatim
                </div>

                <div class="instruction-editor-card">
                    <div id="toolbar-container">
                        <span class="ql-formats">
                            <button type="button" class="ql-custom" id="ql-undo-btn" title="Undo (Ctrl+Z)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-15-6.7L3 13"/></svg>
                            </button>
                            <button type="button" class="ql-custom" id="ql-redo-btn" title="Redo (Ctrl+Y)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 7v6h-6"/><path d="M3 17a9 9 0 0 1 15-6.7L21 13"/></svg>
                            </button>
                        </span>
                        <span class="ql-formats">
                            <select class="ql-font"></select>
                            <select class="ql-size"></select>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-bold" title="Bold (Ctrl+B)"></button>
                            <button class="ql-italic" title="Italic (Ctrl+I)"></button>
                            <button class="ql-underline" title="Underline (Ctrl+U)"></button>
                            <button class="ql-strike" title="Strikethrough"></button>
                            <button class="ql-code" title="Inline code"></button>
                        </span>
                        <span class="ql-formats">
                            <select class="ql-color" title="Text color"></select>
                            <select class="ql-background" title="Highlight color"></select>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-script" value="sub" title="Subscript"></button>
                            <button class="ql-script" value="super" title="Superscript"></button>
                        </span>
                        <span class="ql-formats">
                            <select class="ql-header" title="Heading level">
                                <option selected></option>
                                <option value="1"></option>
                                <option value="2"></option>
                                <option value="3"></option>
                                <option value="4"></option>
                                <option value="5"></option>
                                <option value="6"></option>
                            </select>
                            <button class="ql-blockquote" title="Block quote"></button>
                            <button class="ql-code-block" title="Code block"></button>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-list" value="ordered" title="Ordered list"></button>
                            <button class="ql-list" value="bullet" title="Bulleted list"></button>
                            <button class="ql-indent" value="-1" title="Decrease indent"></button>
                            <button class="ql-indent" value="+1" title="Increase indent"></button>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-direction" value="rtl" title="Text direction"></button>
                            <select class="ql-align" title="Alignment"></select>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-link" title="Insert link"></button>
                            <button class="ql-image" title="Insert image"></button>
                            <button class="ql-video" title="Insert video"></button>
                            <button type="button" class="ql-custom" id="ql-hr-btn" title="Insert horizontal rule">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="12" x2="20" y2="12"/></svg>
                            </button>
                            <button type="button" class="ql-custom" id="ql-table-btn" title="Insert table">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>
                            </button>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-clean" title="Clear formatting"></button>
                            <button type="button" class="ql-custom" id="ql-fullscreen-btn" title="Toggle fullscreen">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9V3h6"/><path d="M21 9V3h-6"/><path d="M3 15v6h6"/><path d="M21 15v6h-6"/></svg>
                            </button>
                        </span>
                    </div>
                    <input type="hidden" name="licence_id" id="licence_id">
                    <div id="editor-container"></div>
                </div>

                {{-- Table tools: grid picker + context floater (positioned via JS) --}}
                <div id="ql-table-picker" class="ql-table-picker" role="dialog" aria-label="Insert table">
                    <div class="picker-label">Insert table</div>
                    <div class="picker-grid" id="ql-table-picker-grid"></div>
                    <div class="picker-readout" id="ql-table-picker-readout">0 &times; 0</div>
                </div>
                <div id="ql-table-context" class="ql-table-context" role="toolbar" aria-label="Table actions">
                    <button type="button" data-table-action="insertRowAbove" title="Insert row above">⬆ Row</button>
                    <button type="button" data-table-action="insertRowBelow" title="Insert row below">⬇ Row</button>
                    <span class="sep"></span>
                    <button type="button" data-table-action="insertColumnLeft" title="Insert column left">⬅ Col</button>
                    <button type="button" data-table-action="insertColumnRight" title="Insert column right">➡ Col</button>
                    <span class="sep"></span>
                    <button type="button" data-table-action="deleteRow" title="Delete row">✕ Row</button>
                    <button type="button" data-table-action="deleteColumn" title="Delete column">✕ Col</button>
                    <button type="button" data-table-action="deleteTable" title="Delete table">✕ Table</button>
                </div>

                <div class="editor-hint">
                    <span class="dot"></span>
                    Tip: Press the chip or pick from the dropdown to insert a placeholder at the cursor.
                    <span class="counter" id="ql-counter"><strong>0</strong> words &middot; <strong>0</strong> characters</span>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light-dark" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btnInstruction">Save instructions</button>
            </div>
        </div>
    </div>
</div>


<!--Add Modal -->
<div class="modal fade" id="addFormModal" tabindex="-1" role="dialog" aria-labelledby="addFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addFormModalLabel"><span class="badge badge-primary"><i class="fa fa-wpforms"></i></span> Add Certificate / Licences</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="red" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                    </button>
                </button>
                {{-- <span><span class="text-danger">(Note:</span> Currently, late fees are applicable only during the last 3 months before the expiry date.)</span> --}}
            </div>
            <form id="addForms" class="simple-example" novalidate>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6 mb-3">
                            <label for="inputEmail4" class="form-label">Category<span class="text-danger">*</span> </label>
                            <select class="form-select" name="form_cate" id="form_cate">
                                <option value="">Please select category</option>
                                @foreach ($categories as $item)
                                    <option value="{{ $item->id }}">{{ $item->category_name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger d-none error-form_cate">Please choose the category</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 mb-2">
                            <label for="inputEmail4" class="form-label">Certificate / Licence Name <span class="text-danger">*</span> </label>
                            <input type="text" class="form-control" name="cert_name" id="cert_name">
                            <small class="text-danger d-none error-cer_val">Please fill the Certificate / Licence Name</small>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label for="inputEmail4" class="form-label">Certificate / Licence Code <span class="text-danger">*</span> </label>
                            <input type="text" class="form-control" name="cate_licence_code" id="cate_licence_code" maxlength="5" placeholder="eg.C,B">
                            <small class="text-danger d-none error-cert_code">Please fill the Certificate / Licence Code</small>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label for="inputEmail4" class="form-label">Form Name <span class="text-danger">*</span> </label>
                            <input type="text" class="form-control" name="form_name" id="form_name">
                            <small class="text-danger d-none error-form_name">Please fill the Form Name</small>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label for="inputEmail4" class="form-label">Form Code <span class="text-danger">*</span> </label>
                            <input type="text" class="form-control" name="form_code" id="form_code" maxlength="5" placeholder="eg.S,W">
                            <small class="text-danger d-none error-form_code">Please choose the Form Code</small>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label for="inputEmail4" class="form-label">Status<span class="text-danger">*</span> </label>
                            <select class="form-select" name="form_status" id="form_status">
                                <option value="1">Active</option>
                                <option value="2">In Active</option>
                            </select>
                            <small class="text-danger d-none error-form_status">Please choose the Form status</small>
                        </div>
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary">Add</button>
                            <button type="button" class="btn btn btn-light-dark" data-bs-dismiss="modal" onclick="$('#addForms').trigger('reset');"><i class="flaticon-cancel-12"></i> Cancel</button>
                        </div>
                    </div> 
                </div>
            </form>
        </div>
    </div>
</div>

<!--Add Edit Modal -->
<div class="modal fade" id="editFormModal" tabindex="-1" role="dialog" aria-labelledby="editFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFormModalLabel"><span class="badge badge-primary"><i class="fa fa-wpforms"></i></span> Edit Certificate / Licences</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="red" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                    </button>
                </button>
                {{-- <span><span class="text-danger">(Note:</span> Currently, late fees are applicable only during the last 3 months before the expiry date.)</span> --}}
            </div>
            <form id="editForms" class="simple-example" novalidate>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6 mb-2">
                            <label for="inputEmail4" class="form-label">Category<span class="text-danger">*</span> </label>
                            <select class="form-select" name="edit_form_cate" id="edit_form_cate">
                                <option value="">Please select category</option>
                                @foreach ($categories as $item)
                                    <option value="{{ $item->id }}">{{ $item->category_name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger d-none error-edit_form_cate">Please choose the category</small>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label for="inputEmail4" class="form-label">Certificate / Licence Name <span class="text-danger">*</span> </label>
                            <input type="text" class="form-control" name="edit_cert_name" id="edit_cert_name">
                            <small class="text-danger d-none error-cer_error">Please fill the Certificate / Licence Name</small>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label for="inputEmail4" class="form-label">Certificate / Licence Code <span class="text-danger">*</span> </label>
                            <input type="text" class="form-control" name="edit_cate_licence_code" id="edit_cate_licence_code" maxlength="5" placeholder="eg.C,B">
                            <small class="text-danger d-none error-cert_code_error">Please fill the Certificate / Licence Code</small>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label for="inputEmail4" class="form-label">Form Name <span class="text-danger">*</span> </label>
                            <input type="text" class="form-control" name="edit_form_name" id="edit_form_name">
                            <small class="text-danger d-none error-edit_form_name">Please fill the Form Name</small>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label for="inputEmail4" class="form-label">Form Code <span class="text-danger">*</span> </label>
                            <input type="text" class="form-control" name="edit_form_code" id="edit_form_code" maxlength="5" placeholder="eg.S,W">
                            <small class="text-danger d-none error-edit_form_code">Please choose the Form Code</small>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label for="inputEmail4" class="form-label">Status<span class="text-danger">*</span> </label>
                            <select class="form-select" name="edit_form_status" id="edit_form_status">
                                <option value="1">Active</option>
                                <option value="2">In Active</option>
                            </select>
                            <small class="text-danger d-none error-edit_form_status">Please choose the Form status</small>
                        </div>
                        <input type="hidden" name="cert_id" id="edit_cert_id">
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <button type="button" class="btn btn btn-light-dark" data-bs-dismiss="modal" onclick="$('#feesForm').trigger('reset');"><i class="flaticon-cancel-12"></i> Cancel</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>



@include('admincms.include.footer');

<script>
    $('.zero-config').DataTable({
        "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
    "<'table-responsive'tr>" +
    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
        "oLanguage": {
            "oPaginate": { "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>', "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>' },
            "sInfo": "Showing page _PAGE_ of _PAGES_",
            "sSearch": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
            "sSearchPlaceholder": "Search...",
            "sLengthMenu": "Results :  _MENU_",
        },
        "stripeClasses": [],
        "lengthMenu": [7, 10, 20, 50],
        "pageLength": 7 
    });


(function () {
    /**
     * Merge tags = placeholders. Fees (N/R/L) resolve from tnelb_fees per licence in PHP; applicant fields at runtime via $extra.
     * The Blade verbatim block below keeps double-curly pairs as literal JS strings.
     */
    @verbatim
    var MERGE_TAG_DEFINITIONS = [
        { tag: '{{FEE_NEW}}', label: 'Fee — New application (N)' },
        { tag: '{{FEE_RENEWAL}}', label: 'Fee — Renewal (R)' },
        { tag: '{{FEE_LATE}}', label: 'Fee — Late renewal (L)' },
        { tag: '{{CERTIFICATE_NAME}}', label: 'Certificate / Licence name' },
        { tag: '{{FORM_NAME}}', label: 'Form name' },
        { tag: '{{LICENCE_CODE}}', label: 'Licence code' },
        { tag: '{{CURRENT_DATE}}', label: 'Today’s date' },
        { tag: '{{APPLICANT_NAME}}', label: 'Applicant name (runtime)' },
        { tag: '{{LICENCE_NUMBER}}', label: 'Licence number (runtime)' },
        { tag: '{{EXPIRY_DATE}}', label: 'Expiry date (runtime)' },
        { tag: '{{APPLICATION_ID}}', label: 'Application ID (runtime)' },
    ];
    @endverbatim

    var mergeSelect = document.getElementById('quill-merge-tags-select');
    if (mergeSelect) {
        MERGE_TAG_DEFINITIONS.forEach(function (def) {
            var opt = document.createElement('option');
            opt.value = def.tag;
            opt.textContent = def.label;
            mergeSelect.appendChild(opt);
        });
    }

    var options = {
        modules: {
            toolbar: '#toolbar-container',
            table: true,
        },
        placeholder: 'Type here...',
        theme: 'snow',
    };

    var quill;
    try {
        quill = new Quill('#editor-container', options);
    } catch (e) {
        console.error('Quill init failed:', e);
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Editor failed to load', text: String(e.message || e) });
        }
        return;
    }
    window.quill = quill;

    /** Re-bind select in case DOM was touched */
    mergeSelect = document.getElementById('quill-merge-tags-select');

    /**
     * Insert merge tag at current selection; caret moves to end of inserted token.
     */
    function insertMergeTagAtCursor(token) {
        if (!token) {
            return;
        }
        quill.focus();
        var range = getInsertRange(quill);
        var index = range ? range.index : quill.getLength();
        quill.insertText(index, token, 'user');
        var nextPos = index + token.length;
        quill.setSelection(nextPos, 0, 'silent');
        quill.setSelection(nextPos, 0, 'user');
    }

    /** Resolve selection; if none (editor unfocused), place caret at end. */
    function getInsertRange(editor) {
        var r = editor.getSelection(true);
        if (r) {
            return r;
        }
        var len = editor.getLength();
        editor.setSelection(Math.max(0, len - 1), 0, 'silent');
        return editor.getSelection(true);
    }

    if (mergeSelect) {
        mergeSelect.addEventListener('change', function () {
            var val = this.value;
            if (!val) {
                return;
            }
            insertMergeTagAtCursor(val);
            this.selectedIndex = 0;
        });
    }

    /** Quick-insert chips next to the dropdown */
    document.querySelectorAll('.quill-merge-tags-bar .merge-chip[data-merge-tag]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            insertMergeTagAtCursor(btn.getAttribute('data-merge-tag'));
        });
    });

    /** Undo / Redo via Quill history */
    var undoBtn = document.getElementById('ql-undo-btn');
    if (undoBtn) {
        undoBtn.addEventListener('click', function (e) {
            e.preventDefault();
            quill.history.undo();
        });
    }
    var redoBtn = document.getElementById('ql-redo-btn');
    if (redoBtn) {
        redoBtn.addEventListener('click', function (e) {
            e.preventDefault();
            quill.history.redo();
        });
    }

    /** Insert horizontal rule at caret (uses Quill's "divider" via inline HTML) */
    var hrBtn = document.getElementById('ql-hr-btn');
    if (hrBtn) {
        hrBtn.addEventListener('click', function (e) {
            e.preventDefault();
            quill.focus();
            var r = getInsertRange(quill);
            var idx = r ? r.index : quill.getLength();
            quill.insertText(idx, '\n', 'user');
            quill.clipboard.dangerouslyPasteHTML(idx + 1, '<hr>');
            quill.setSelection(idx + 2, 0, 'user');
        });
    }

    /** Table tools — grid picker + context floater wired to Quill's table module. */
    var tableModule = (function () {
        try { return quill.getModule('table'); } catch (e) { return null; }
    })();

    var tableBtn = document.getElementById('ql-table-btn');
    var tablePicker = document.getElementById('ql-table-picker');
    var tablePickerGrid = document.getElementById('ql-table-picker-grid');
    var tablePickerReadout = document.getElementById('ql-table-picker-readout');
    var TABLE_PICKER_COLS = 8, TABLE_PICKER_ROWS = 8;

    function buildTablePickerGrid() {
        if (!tablePickerGrid || tablePickerGrid.childElementCount > 0) {
            return;
        }
        for (var r = 1; r <= TABLE_PICKER_ROWS; r++) {
            for (var c = 1; c <= TABLE_PICKER_COLS; c++) {
                var cell = document.createElement('div');
                cell.className = 'picker-cell';
                cell.dataset.r = String(r);
                cell.dataset.c = String(c);
                tablePickerGrid.appendChild(cell);
            }
        }
    }
    function highlightPicker(rows, cols) {
        var cells = tablePickerGrid.querySelectorAll('.picker-cell');
        cells.forEach(function (cell) {
            var hot = (+cell.dataset.r <= rows) && (+cell.dataset.c <= cols);
            cell.classList.toggle('hot', hot);
        });
        tablePickerReadout.textContent = rows + ' \u00d7 ' + cols;
    }
    function positionPicker() {
        if (!tableBtn || !tablePicker) {
            return;
        }
        var rect = tableBtn.getBoundingClientRect();
        tablePicker.style.top = (window.scrollY + rect.bottom + 6) + 'px';
        tablePicker.style.left = Math.max(8, window.scrollX + rect.left - 60) + 'px';
    }
    function showPicker() {
        if (!tablePicker) {
            return;
        }
        buildTablePickerGrid();
        highlightPicker(0, 0);
        positionPicker();
        tablePicker.classList.add('show');
    }
    function hidePicker() {
        if (tablePicker) {
            tablePicker.classList.remove('show');
        }
    }
    if (tableBtn && tablePicker) {
        if (!tableModule) {
            tableBtn.style.display = 'none';
        } else {
            tableBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (tablePicker.classList.contains('show')) {
                    hidePicker();
                } else {
                    showPicker();
                }
            });
            tablePickerGrid.addEventListener('mousemove', function (e) {
                var target = e.target.closest('.picker-cell');
                if (target) {
                    highlightPicker(+target.dataset.r, +target.dataset.c);
                }
            });
            tablePickerGrid.addEventListener('click', function (e) {
                var target = e.target.closest('.picker-cell');
                if (!target) {
                    return;
                }
                var rows = +target.dataset.r;
                var cols = +target.dataset.c;
                quill.focus();
                try {
                    tableModule.insertTable(rows, cols);
                } catch (err) {
                    console.error('Insert table failed:', err);
                }
                hidePicker();
            });
            document.addEventListener('click', function (e) {
                if (!tablePicker.classList.contains('show')) {
                    return;
                }
                if (e.target.closest('#ql-table-picker') || e.target.closest('#ql-table-btn')) {
                    return;
                }
                hidePicker();
            });
            window.addEventListener('resize', function () {
                if (tablePicker.classList.contains('show')) {
                    positionPicker();
                }
            });
        }
    }

    /** Floating context bar — appears when caret sits inside a table cell. */
    var tableContext = document.getElementById('ql-table-context');
    function findClosestCell(node) {
        while (node && node !== quill.root) {
            if (node.nodeType === 1 && (node.tagName === 'TD' || node.tagName === 'TH')) {
                return node;
            }
            node = node.parentNode;
        }
        return null;
    }
    function positionContext(cell) {
        if (!tableContext || !cell) {
            return;
        }
        var rect = cell.getBoundingClientRect();
        tableContext.style.top = (window.scrollY + rect.top - 38) + 'px';
        tableContext.style.left = (window.scrollX + rect.left) + 'px';
    }
    function refreshTableContext() {
        if (!tableContext || !tableModule) {
            return;
        }
        var sel = quill.getSelection();
        if (!sel) {
            tableContext.classList.remove('show');
            return;
        }
        var leaf = quill.getLeaf(sel.index);
        var domNode = leaf && leaf[0] ? leaf[0].domNode : null;
        var cell = findClosestCell(domNode);
        if (!cell) {
            tableContext.classList.remove('show');
            return;
        }
        positionContext(cell);
        tableContext.classList.add('show');
    }
    quill.on('selection-change', refreshTableContext);
    quill.on('text-change', function () {
        setTimeout(refreshTableContext, 0);
    });
    if (tableContext && tableModule) {
        tableContext.addEventListener('mousedown', function (e) {
            // Prevent caret from leaving the table cell when clicking a button.
            e.preventDefault();
        });
        tableContext.addEventListener('click', function (e) {
            var btn = e.target.closest('button[data-table-action]');
            if (!btn) {
                return;
            }
            var action = btn.getAttribute('data-table-action');
            try {
                if (typeof tableModule[action] === 'function') {
                    tableModule[action]();
                }
            } catch (err) {
                console.error('Table action failed:', action, err);
            }
            setTimeout(refreshTableContext, 0);
        });
    }

    /** Fullscreen toggle */
    var fsBtn = document.getElementById('ql-fullscreen-btn');
    var instrModal = document.getElementById('exampleModal');
    if (fsBtn && instrModal) {
        fsBtn.addEventListener('click', function (e) {
            e.preventDefault();
            instrModal.classList.toggle('editor-fullscreen');
            window.dispatchEvent(new Event('resize'));
        });
    }

    /** Live word / character counter */
    var counter = document.getElementById('ql-counter');
    function updateCounter() {
        if (!counter) {
            return;
        }
        var text = quill.getText().replace(/\u00A0/g, ' ').trim();
        var chars = text.length;
        var words = text === '' ? 0 : text.split(/\s+/).length;
        counter.innerHTML = '<strong>' + words + '</strong> words &middot; <strong>' + chars + '</strong> characters';
    }
    quill.on('text-change', updateCounter);
    updateCounter();

    if (instrModal) {
        instrModal.addEventListener('shown.bs.modal', function () {
            quill.focus();
            if (typeof quill.update === 'function') {
                quill.update();
            }
            window.dispatchEvent(new Event('resize'));
            updateCounter();
        });
        instrModal.addEventListener('hidden.bs.modal', function () {
            instrModal.classList.remove('editor-fullscreen');
        });
    }
})();

</script>