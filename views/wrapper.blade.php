<div class="directory_editor_wrapper editor_type_{{ $fieldType ?? '' }}">
    <form method="post" onsubmit="return false;">
        <input type="hidden" name="action" value="saveValue">
        <input type="hidden" name="id" value="{{ $id }}">

        {!! $rows !!}

        <div class="directory_editor_buttons">
            <button type="button" class="btn btn-success" data-submit>OK</button>
            <button type="button" class="btn btn-warning" data-cancel>Cancel</button>
        </div>
    </form>
</div>