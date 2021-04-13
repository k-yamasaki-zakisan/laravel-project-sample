<!-- 出展ゾーン -->
<div class="form-group">
    <label>出展ゾーン</label>
    <select id="exhibition_zone" class="custom-select" name="exhibition_zone_id">
        <option value="">出展ゾーンを選んで
            @isset($exhibitor)
            @foreach( $exhibition_zones as $val )
            @if( $exhibitor['exhibition_id'] == $val['exhibition_id'])
            @if(old('exhibition_zone_id') == $val['id'])

        <option value="{{ $val['id'] }}" selected> {{ $val['name'] }} </option>
        @break
        @elseif($exhibitor['exhibition_zone_id'] == $val['id'])
        <option value="{{ $val['id'] }}" selected> {{ $val['name'] }} </option>
        @else
        <option value="{{ $val['id'] }}"> {{ $val['name'] }} </option>
        @endif
        @endif
        @endforeach
        @else
        @foreach( $exhibition_zones as $val )
        @if( old('exhibition_zone_id') == $val['id'])
        <option value="{{ $val['id'] }}" selected> {{ $val['name'] }} </option>
        @endif
        @endforeach
        @endisset

    </select>
</div>