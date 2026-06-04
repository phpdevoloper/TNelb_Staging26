<div class="work-exp-view-wrap" id="work-exp-view-wrap">
    <div class="wx-order-card">
        <div class="wx-summary-table-wrap">
            <table class="wx-summary-table" id="work-exp-view-table">
                <thead>
                    <tr>
                        <th class="wx-summary-th-sno">S.No</th>
                        <th>Employment Type</th>
                        <th class="wx-summary-th-org"><span class="wx-th-org-line">Organisation &amp;</span><span class="wx-th-org-line">Address</span></th>
                        <th>Designation</th>
                        <th>Nature of Work</th>
                        <th>Voltage Level</th>
                        <th>Transformer kVA</th>
                        <th>Total Experience</th>
                        <th>Attachment</th>
                    </tr>
                </thead>
                <tbody id="work-exp-view-tbody">
                    @if(isset($exp_details) && $exp_details->isNotEmpty())
                        @foreach($exp_details as $index => $expRow)
                            @include('user_login.partials.form-s-work-exp-view-row', ['expRow' => $expRow, 'sno' => $loop->iteration])
                        @endforeach
                    @else
                        <tr>
                            <td colspan="9" class="text-center text-muted py-3">No work experience entries</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
