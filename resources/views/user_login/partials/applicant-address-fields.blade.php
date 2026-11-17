{{--
 | Structured applicant address (Forms S / W / WH, "Applicant Address" section).
 |
 | The six visible parts are composed by public/assets/js/applicant_address.js into the hidden
 | #applicants_address, which is the only address field the backend validates/saves (max 255).
 | Composed format: "Line1, Line2, City, District, State - Pincode" (empty parts skipped,
 | District only for Tamil Nadu).
 |
 | Parameters (all optional):
 |   $addressSaved  string|null  Address saved on an existing application (edit views). When
 |                               empty, the fields are prefilled from the registration profile
 |                               exactly like apply-form-s.
 |   $addressLocked bool         Section is locked (returned application, partial unlock): the
 |                               parts are rendered disabled and the JS never touches the hidden
 |                               value, so the saved address is posted unchanged.
 --}}
@php
    /*
     | Same option lists as register.blade.php (kept in sync by hand until the
     | backend exposes a shared lookup).
     */
    $addrStates = [
        'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat',
        'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh',
        'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
        'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh',
        'Uttarakhand', 'West Bengal', 'Andaman and Nicobar Islands', 'Chandigarh',
        'Dadra and Nagar Haveli and Daman and Diu', 'Lakshadweep', 'Delhi', 'Puducherry',
        'Jammu and Kashmir', 'Ladakh',
    ];
    $addrDistricts = [
        'Ariyalur', 'Chengalpattu', 'Chennai', 'Coimbatore', 'Cuddalore', 'Dharmapuri', 'Dindigul',
        'Erode', 'Kancheepuram', 'Kanyakumari', 'Karur', 'Krishnagiri', 'Madurai', 'Nagapattinam',
        'Namakkal', 'Nilgiris', 'Perambalur', 'Pudukkottai', 'Ramanathapuram', 'Salem',
        'Sivagangai', 'Tenkasi', 'Thanjavur', 'The Nilgiris', 'Theni', 'Tirunelveli', 'Tiruppur',
        'Tiruvallur', 'Tiruvannamalai', 'Vellore', 'Viluppuram', 'Virudhunagar',
    ];

    /*
     | Parse a saved address back into its parts. Returns null when the string was not
     | produced by our composer (legacy free-text address).
     |
     | Rules: must end with " - NNNNNN" (6 digits) and the comma-separated segment just
     | before it must be a known state. Of the remaining segments: for Tamil Nadu a trailing
     | known district is taken as District (only when 3+ segments remain); then last = City, first = Line 1, anything in
     | between (re-joined with ", ") = Line 2. One segment -> Line 1 only (City left empty so
     | the user is asked to fill it). Over-long parts are kept as-is; the client validation
     | reports them instead of silently truncating.
     */
    $addrParse = function (string $saved) use ($addrStates, $addrDistricts): ?array {
        $saved = trim(preg_replace('/\s+/u', ' ', $saved));
        if (! preg_match('/^(.*)\s-\s(\d{6})$/su', $saved, $m)) {
            return null;
        }
        $segments = array_values(array_filter(array_map('trim', explode(',', $m[1])), 'strlen'));
        $state = array_pop($segments);
        if ($state === null || ! in_array($state, $addrStates, true)) {
            return null;
        }
        $district = '';
        // A complete TN address always has Line 1, City and District, so only take a trailing
        // district when at least three segments remain (otherwise it is more likely the City).
        if ($state === 'Tamil Nadu' && count($segments) >= 3 && in_array(end($segments), $addrDistricts, true)) {
            $district = array_pop($segments);
        }
        $line1 = (string) (array_shift($segments) ?? '');
        $city = $segments ? (string) array_pop($segments) : '';

        return [
            'line1' => $line1,
            'line2' => implode(', ', $segments),
            'city' => $city,
            'state' => $state,
            'district' => $district,
            'pincode' => $m[2],
        ];
    };

    $addrLocked = (bool) ($addressLocked ?? false);
    $addrSavedRaw = trim((string) ($addressSaved ?? ''));
    $addrLegacy = null;

    if ($addrSavedRaw !== '') {
        // Edit views: rebuild the parts from the saved application address.
        $addrParts = $addrParse($addrSavedRaw);
        if ($addrParts === null) {
            // Legacy free text: fields start empty, the old text is shown read-only and
            // stays in the hidden input until the structured address is complete.
            $addrLegacy = $addrSavedRaw;
            $addrParts = ['line1' => '', 'line2' => '', 'city' => '', 'state' => '', 'district' => '', 'pincode' => ''];
        }
        $addrLine1 = $addrParts['line1'];
        $addrLine2 = $addrParts['line2'];
        $addrCity = $addrParts['city'];
        $addrState = $addrParts['state'];
        $addrDistrict = $addrParts['district'];
        $addrPincode = $addrParts['pincode'];
    } else {
        // New application (and edit fallback): prefill from the registered address.
        // Legacy users (registered before the multi-part form) only have the old single
        // `address` column (raw column, not the accessor, to avoid duplicating Line 2 / City).
        // Best-effort split on commas: first = Line 1, last = City, middle = Line 2.
        // A single-part text, or a user who already has a City, keeps the whole text in Line 1.
        $addrUser = Auth::user();
        $addrLine1Default = (string) ($addrUser->address_line1 ?? '');
        $addrLine2Default = (string) ($addrUser->address_line2 ?? '');
        $addrCityDefault = (string) ($addrUser->city ?? '');
        if (trim($addrLine1Default) === '' && $addrUser) {
            $addrLegacyRaw = trim((string) ($addrUser->getRawOriginal('address') ?? ''));
            $addrLegacyParts = array_values(array_filter(
                array_map('trim', explode(',', $addrLegacyRaw)),
                'strlen'
            ));
            if (trim($addrCityDefault) === '' && trim($addrLine2Default) === '' && count($addrLegacyParts) >= 2) {
                $addrLine1Default = $addrLegacyParts[0];
                $addrCityDefault = $addrLegacyParts[count($addrLegacyParts) - 1];
                $addrLine2Default = implode(', ', array_slice($addrLegacyParts, 1, -1));
            } else {
                $addrLine1Default = $addrLegacyRaw;
            }
        }
        $addrLine1 = (string) old('address_line1', $addrLine1Default);
        $addrLine2 = (string) old('address_line2', $addrLine2Default);
        $addrCity = (string) old('city', $addrCityDefault);
        $addrState = (string) old('state', $addrUser->state ?? '');
        $addrDistrict = (string) old('district', $addrUser->district ?? '');
        $addrPincode = (string) old('pincode', $addrUser->pincode ?? '');
    }
    $addrIsTn = $addrState === 'Tamil Nadu';

    // Server-side value for the hidden field; applicant_address.js recomputes it live.
    if ($addrLegacy !== null) {
        $addrComposed = $addrLegacy;
    } elseif ($addrLocked && $addrSavedRaw !== '') {
        // Locked section: post the saved address verbatim.
        $addrComposed = $addrSavedRaw;
    } else {
        $addrComposed = implode(', ', array_filter(array_map('trim', [
            $addrLine1, $addrLine2, $addrCity, $addrIsTn ? $addrDistrict : '', $addrState,
        ]), 'strlen'));
        if (trim($addrPincode) !== '') {
            $addrComposed .= ($addrComposed !== '' ? ' - ' : '') . trim($addrPincode);
        }
        $addrComposed = \Illuminate\Support\Str::limit($addrComposed, 255, '');
    }
@endphp
<div class="row" id="fs-address-block" data-fs-address
    @if ($addrLocked) data-address-locked="1" @endif
    @if ($addrLegacy !== null) data-legacy-address="{{ $addrLegacy }}" @endif>
    @if ($addrLegacy !== null)
        <div class="col-12 mb-2" id="address_legacy_box">
            <div role="note" style="background:#fff8e1;border:1px solid #ffe08a;border-left:4px solid #f0ad4e;border-radius:6px;padding:8px 10px;font-size:.82rem;color:#5c4400;">
                <div style="font-weight:600;margin-bottom:2px;">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                    Existing address (please re-enter it in the fields below)
                </div>
                <div style="white-space:pre-line;word-break:break-word;">{{ $addrLegacy }}</div>
            </div>
        </div>
    @endif
    <div class="col-12 mb-2">
        <div class="fs-field-label">Address Line 1 <span class="req">*</span></div>
        <div class="fs-field-tamil">முகவரி வரி 1</div>
        <input type="text" class="form-control" id="address_line1" name="address_line1"
            maxlength="70" autocomplete="address-line1" placeholder="Door / flat no., building, street"
            value="{{ $addrLine1 }}" required @disabled($addrLocked)>
        <span id="address_line1_error" class="text-danger" style="font-size:.78rem;"></span>
    </div>
    <div class="col-12 mb-2">
        <div class="fs-field-label">Address Line 2</div>
        <div class="fs-field-tamil">முகவரி வரி 2</div>
        <input type="text" class="form-control" id="address_line2" name="address_line2"
            maxlength="60" autocomplete="address-line2" placeholder="Area, landmark (optional)"
            value="{{ $addrLine2 }}" @disabled($addrLocked)>
        <span id="address_line2_error" class="text-danger" style="font-size:.78rem;"></span>
    </div>
    <div class="col-12 col-sm-6 mb-2">
        <div class="fs-field-label">Village / Town / City <span class="req">*</span></div>
        <div class="fs-field-tamil">கிராமம் / நகரம்</div>
        <input type="text" class="form-control" id="city" name="city" data-titlecase
            maxlength="40" autocomplete="address-level2" placeholder="Village, town or city"
            value="{{ $addrCity }}" required @disabled($addrLocked)>
        <span id="city_error" class="text-danger" style="font-size:.78rem;"></span>
    </div>
    <div class="col-12 col-sm-6 mb-2">
        <div class="fs-field-label">State <span class="req">*</span></div>
        <div class="fs-field-tamil">மாநிலம்</div>
        <select class="form-control" id="state" name="state" autocomplete="address-level1" required @disabled($addrLocked)>
            <option value="">--- Select State ---</option>
            @foreach ($addrStates as $addrStateOpt)
                <option value="{{ $addrStateOpt }}" @selected($addrState === $addrStateOpt)>{{ $addrStateOpt }}</option>
            @endforeach
        </select>
        <span id="state_error" class="text-danger" style="font-size:.78rem;"></span>
    </div>
    <div class="col-12 col-sm-6 mb-2" id="district_row" @if (! $addrIsTn) hidden @endif>
        <div class="fs-field-label">District <span class="req">*</span></div>
        <div class="fs-field-tamil">மாவட்டம்</div>
        <select class="form-control" id="district" name="district" required @disabled($addrLocked || ! $addrIsTn)>
            <option value="">--- Select District ---</option>
            @foreach ($addrDistricts as $addrDistrictOpt)
                <option value="{{ $addrDistrictOpt }}" @selected($addrIsTn && $addrDistrict === $addrDistrictOpt)>{{ $addrDistrictOpt }}</option>
            @endforeach
        </select>
        <span id="district_error" class="text-danger" style="font-size:.78rem;"></span>
    </div>
    <div class="col-12 col-sm-6 mb-2">
        <div class="fs-field-label">Pin Code <span class="req">*</span></div>
        <div class="fs-field-tamil">அஞ்சல் குறியீட்டு எண்</div>
        <input type="text" class="form-control" id="pincode" name="pincode"
            inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="postal-code"
            placeholder="6 digit pin code" value="{{ $addrPincode }}" required @disabled($addrLocked)>
        <span id="pincode_error" class="text-danger" style="font-size:.78rem;"></span>
    </div>
</div>
<input type="hidden" id="applicants_address" name="applicants_address" value="{{ $addrComposed }}">
<span id="applicants_address_error" class="text-danger" style="font-size:.78rem;"></span>
