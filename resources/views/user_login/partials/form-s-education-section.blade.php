{{--
    Form S — "Details of Technical Qualification passed by the applicant" section.

    One shared markup for every Form S flow so the table, the level list and the
    row fields cannot drift apart between New / Digitisation / Renewal / Alteration.
    Row markup lives in form-s-education-row; "Add row" clones the <template> below
    via public/assets/js/form_s_education.js (load that script on the page).

    Params (all optional):
      $eduMode          'new' | 'digitization' | 'renewal' | 'alteration'   (default 'new')
      $eduSectionNo     number shown in the section badge                    (default 6)
      $eduRows          saved education rows (collection); empty => one blank row
      $eduApplicationId application pk (renewal document links)
      $eduFrozen        true => adds .fs-alt-frozen-section (alteration read-only styling)
      $eduMode 'edit'   edit draft / edit returned application (saved rows deletable, ids carried)
      $eduLockSaved     saved rows read-only (default: only renewal)      $eduDocRemovable  show "Remove" next to saved document
      $eduShowAdd       false hides the header "+"                        $eduSectionClass / $eduReturnSection  wrapper class / data-return-section

    Behaviour per mode (kept identical to the old per-view copies):
      new / digitization : blank row only, files posted as education_document[], at least one row kept
      renewal            : saved rows locked (no remove), hidden edu_id/existing_document/removed_document,
                           files posted as education_document[<index>]
      alteration         : saved rows removable via delete_education, same hidden fields as renewal
--}}
@php
    $eduMode = $eduMode ?? 'new';
    $eduSectionNo = $eduSectionNo ?? 6;
    $eduRows = collect($eduRows ?? [])->values();
    $eduApplicationId = (int) ($eduApplicationId ?? 0);
    $eduFrozen = (bool) ($eduFrozen ?? false);

    $eduIsRenewal = $eduMode === 'renewal';
    $eduIsAlteration = $eduMode === 'alteration';
    $eduIsEdit = $eduMode === 'edit'; // edit draft / edit returned application
    // Renewal / alteration / edit pages change existing rows, so they carry ids and index the file inputs.
    $eduTracksIds = $eduIsRenewal || $eduIsAlteration || $eduIsEdit;
    // Saved rows the applicant may delete (AJAX delete_education).
    $eduHasDelete = $eduIsAlteration || $eduIsEdit;

    // Optional per-page switches (defaults reproduce the plain modes):
    $eduLockSaved = (bool) ($eduLockSaved ?? $eduIsRenewal);      // saved rows read-only, no remove
    $eduDocRemovable = (bool) ($eduDocRemovable ?? false);        // "Remove" button next to a saved document
    $eduShowAdd = (bool) ($eduShowAdd ?? true);                   // hide header "+" (returned application, locked section)
    $eduRowsLocked = (bool) ($eduRowsLocked ?? false);            // whole section read-only: no row/document remove buttons
    $eduSectionClass = (string) ($eduSectionClass ?? '');         // extra class on the section wrapper
    $eduReturnSection = $eduReturnSection ?? null;                // data-return-section value

    // Class on the header "+" button (kept so existing CSS such as .fs-alt-frozen-section still matches).
    $eduAddBtnClass = ['new' => 'add-more', 'digitization' => 'add-more', 'renewal' => 'add-more-education', 'alteration' => 'add-more add-more-education', 'edit' => 'add-more add-more-education'][$eduMode] ?? 'add-more';

    $eduLevels = [
        'DEE' => 'Diploma(Electrical Engineering)',
        'BEE' => 'B.E(Electrical Engineering)',
        'MEE' => 'M.E(Electrical Engineering)',
        'AMIE' => 'Pass in AMIE',
        'EXS' => 'Ex Serviceman'
    ];
    // "Ex Serviceman" is the single code EXS. Older rows saved as 'EX' are shown as EXS by the row partial
    // (so there is never a second "Ex Serviceman" option).

    $eduRowsToRender = $eduRows->isNotEmpty() ? $eduRows : collect([null]);
    $eduYearMax = (int) date('Y');
@endphp
<div class="fs-section {{ $eduFrozen ? 'fs-alt-frozen-section' : '' }} {{ $eduSectionClass }}"
    @if ($eduReturnSection) data-return-section="{{ $eduReturnSection }}" @endif>
    <div class="fs-section-header">
        <span class="fs-section-num">{{ $eduSectionNo }}</span>
        <div>
            <div class="fs-section-title">
                Details of Technical Qualification passed by the applicant.
                <span class="section-req">*</span>
                <span class="section-hint">(Upload the documents)</span>
            </div>
            <div class="fs-section-tamil">விண்ணப்பதாரரின் தொழில்நுட்ப தேர்ச்சி மற்றும் தேர்ச்சி பற்றிய விவரங்கள் <span style="font-size:.72rem;">(ஆவணங்களை பதிவேற்ற வேண்டும்)</span></div>
        </div>
    </div>
    <div class="fs-section-body">
        <div class="{{ $eduTracksIds ? 'table-responsive' : 'fs-table-wrap' }}">
            <table class="table table-bordered {{ $eduIsRenewal ? 'table-striped' : '' }}" id="education-table">
                <thead>
                    <tr>
                        <th rowspan="2">S.No</th>
                        <th rowspan="2">Education Level</th>
                        <th rowspan="2">University / Institute</th>
                        <th colspan="2" class="text-center">Month &amp; Year of Passing</th>
                        <th rowspan="2">Certificate No</th>
                        <th class="text-center" rowspan="2">Upload Document
                            <br><span class="file-limit">File type: PDF(Min 5 KB To Max 200 KB)</span>
                        </th>
                        <th class="text-center p-1" rowspan="2">
                            <div class="form-s-actions-stack">
                                @if ($eduShowAdd)
                                    <button type="button" class="btn-tbl-add {{ $eduAddBtnClass }} py-1 px-2" title="Add row">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                @endif
                            </div>
                        </th>
                    </tr>
                </thead>
                {{-- data-* drive form_s_education.js: row limits and whether an added row must carry a file. --}}
                <tbody id="education-container"
                    data-max-rows="5"
                    data-min-rows="{{ $eduTracksIds ? 0 : 1 }}"
                    data-require-file="{{ $eduTracksIds ? 1 : 0 }}">
                    @foreach ($eduRowsToRender as $eduRow)
                        @include('user_login.partials.form-s-education-row', [
                            'eduRow' => $eduRow,
                            'eduIdx' => $loop->index,
                            'eduSerial' => $loop->iteration,
                        ])
                    @endforeach
                </tbody>
            </table>
            {{-- Blank row cloned by "Add row"; __IDX__ / __SERIAL__ are filled in by the script. --}}
            <template id="education-row-template">
                @include('user_login.partials.form-s-education-row', [
                    'eduRow' => null,
                    'eduIdx' => '__IDX__',
                    'eduSerial' => '__SERIAL__',
                ])
            </template>
        </div>
    </div>
</div>
