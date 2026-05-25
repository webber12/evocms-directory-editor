<input type="text"
       name="field[{{ $field }}]"
       value="{{ $value }}"
       placeholder="введите значение"
       onload="this.style.width = (this.value.length + 1) + 'ch'"
>