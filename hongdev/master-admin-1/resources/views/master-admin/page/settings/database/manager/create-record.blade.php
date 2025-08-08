@extends('master-admin::master-admin.layout.layout-master')

@section('title', 'Insert Record')

@section('page_title', 'Insert Record - ' . $table)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Insert New Record into {{ $table }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('master-admin.settings.database.manager.store-record', $table) }}" method="POST">
                    @csrf
                    
                    @foreach($columns as $column)
                        @php
                            $fieldName = $driver === 'mysql' ? $column->Field : $column->name;
                            $fieldType = $driver === 'mysql' ? $column->Type : $column->type;
                            $isNullable = $driver === 'mysql' ? $column->Null === 'YES' : !$column->notnull;
                            $isAutoIncrement = $driver === 'mysql' ? str_contains($column->Extra, 'auto_increment') : false;
                        @endphp
                        
                        @if(!$isAutoIncrement)
                        <div class="mb-3">
                            <label for="{{ $fieldName }}" class="form-label">
                                {{ $fieldName }}
                                <small class="text-muted">({{ $fieldType }})</small>
                                @if(!$isNullable)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            
                            @if(str_contains(strtolower($fieldType), 'text') || str_contains(strtolower($fieldType), 'longtext'))
                                <textarea class="form-control" id="{{ $fieldName }}" name="{{ $fieldName }}" 
                                          rows="3" {{ !$isNullable ? 'required' : '' }}></textarea>
                            @elseif(str_contains(strtolower($fieldType), 'date') || str_contains(strtolower($fieldType), 'time'))
                                <input type="datetime-local" class="form-control" id="{{ $fieldName }}" 
                                       name="{{ $fieldName }}" {{ !$isNullable ? 'required' : '' }}>
                            @elseif(str_contains(strtolower($fieldType), 'bool'))
                                <select class="form-select" id="{{ $fieldName }}" name="{{ $fieldName }}" {{ !$isNullable ? 'required' : '' }}>
                                    @if($isNullable)
                                        <option value="">NULL</option>
                                    @endif
                                    <option value="1">True</option>
                                    <option value="0">False</option>
                                </select>
                            @else
                                <input type="text" class="form-control" id="{{ $fieldName }}" 
                                       name="{{ $fieldName }}" {{ !$isNullable ? 'required' : '' }}>
                            @endif
                            
                            @if($isNullable)
                                <div class="form-text">This field can be left empty (NULL)</div>
                            @endif
                        </div>
                        @endif
                    @endforeach
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('master-admin.settings.database.manager.table', $table) }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success">Insert Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
