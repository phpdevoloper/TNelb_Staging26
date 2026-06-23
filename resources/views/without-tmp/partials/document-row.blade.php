<tr>
    @if($row)
        <input type="hidden" name="document_id[{{ $index }}]" value="{{ $row->id }}">
    @endif
    <td><input type="text" name="document_label[{{ $index }}]" class="form-control form-control-sm" value="{{ old('document_label.'.$index, $row->document_label ?? '') }}"></td>
    <td>
        @if($row?->file_name)
            <small class="d-block text-muted">{{ $row->file_name }}</small>
        @endif
        <input type="file" name="document_file[{{ $index }}]" class="form-control-file form-control-sm">
    </td>
    <td></td>
</tr>
