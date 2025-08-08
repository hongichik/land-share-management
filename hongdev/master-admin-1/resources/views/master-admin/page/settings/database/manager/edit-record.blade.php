@extends('master-admin::master-admin.layout.layout-master')

@section('title', 'Edit Record')

@section('page_title', 'Edit Record - ' . $table)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Record in {{ $table }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('master-admin.settings.database.manager.update-record', [$table, $record->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    @foreach($columns as $column)
                        @php
                            $fieldName = $driver === 'mysql' ? $column->Field : $column->name;
                            $fieldType = $driver === 'mysql' ? $column->Type : $column->type;
                            $isNullable = $driver === 'mysql' ? $column->Null === 'YES' : !$column->notnull;
                            $isAutoIncrement = $driver === 'mysql' ? str_contains($column->Extra, 'auto_increment') : false;
                            $currentValue = $record->$fieldName ?? '';
                        @endphp
                        
                        <div class="mb-3">
                            <label for="{{ $fieldName }}" class="form-label">
                                {{ $fieldName }}
                                <small class="text-muted">({{ $fieldType }})</small>
                                @if(!$isNullable && !$isAutoIncrement)
                                    <span class="text-danger">*</span>
                                @endif
                                @if($isAutoIncrement)
                                    <span class="badge bg-info">Auto Increment</span>
                                @endif
                            </label>
                            
                            @if($isAutoIncrement)
                                <input type="text" class="form-control" value="{{ $currentValue }}" readonly>
                                <input type="hidden" name="{{ $fieldName }}" value="{{ $currentValue }}">
                            @elseif(str_contains(strtolower($fieldType), 'text') || str_contains(strtolower($fieldType), 'longtext'))
                                <textarea class="form-control" id="{{ $fieldName }}" name="{{ $fieldName }}" 
                                          rows="3" {{ !$isNullable ? 'required' : '' }}>{{ $currentValue }}</textarea>
                            @elseif(str_contains(strtolower($fieldType), 'datetime'))
                                <input type="datetime-local" class="form-control" id="{{ $fieldName }}" 
                                       name="{{ $fieldName }}" value="{{ $currentValue ? date('Y-m-d\TH:i', strtotime($currentValue)) : '' }}" 
                                       {{ !$isNullable ? 'required' : '' }}>
                            @elseif(str_contains(strtolower($fieldType), 'date'))
                                <input type="date" class="form-control" id="{{ $fieldName }}" 
                                       name="{{ $fieldName }}" value="{{ $currentValue ? date('Y-m-d', strtotime($currentValue)) : '' }}" 
                                       {{ !$isNullable ? 'required' : '' }}>
                            @elseif(str_contains(strtolower($fieldType), 'time'))
                                <input type="time" class="form-control" id="{{ $fieldName }}" 
                                       name="{{ $fieldName }}" value="{{ $currentValue ? date('H:i', strtotime($currentValue)) : '' }}" 
                                       {{ !$isNullable ? 'required' : '' }}>
                            @elseif(str_contains(strtolower($fieldType), 'bool'))
                                <select class="form-select" id="{{ $fieldName }}" name="{{ $fieldName }}" {{ !$isNullable ? 'required' : '' }}>
                                    @if($isNullable)
                                        <option value="" {{ $currentValue === null ? 'selected' : '' }}>NULL</option>
                                    @endif
                                    <option value="1" {{ $currentValue == 1 ? 'selected' : '' }}>True</option>
                                    <option value="0" {{ $currentValue == 0 ? 'selected' : '' }}>False</option>
                                </select>
                            @else
                                <input type="text" class="form-control" id="{{ $fieldName }}" 
                                       name="{{ $fieldName }}" value="{{ $currentValue }}" {{ !$isNullable ? 'required' : '' }}>
                            @endif
                            
                            @if($isNullable && !$isAutoIncrement)
                                <div class="form-text">This field can be left empty (NULL)</div>
                            @endif
                        </div>
                    @endforeach
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('master-admin.settings.database.manager.table', $table) }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
