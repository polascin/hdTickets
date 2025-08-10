@extends('layouts.app')

@section('title', 'Settings Import/Export')

@push('styles')
<link href="{{ asset('css/profile.css?v=' . filemtime(public_path('css/profile.css'))) }}" rel="stylesheet">
<style>
    .settings-export-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .settings-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .settings-card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        position: relative;
    }

    .settings-card-body {
        padding: 20px;
    }

    .category-selector {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .category-item {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .category-item:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        transform: translateY(-2px);
    }

    .category-item.selected {
        border-color: #667eea;
        background: #f7fafc;
    }

    .category-item input[type="checkbox"] {
        position: absolute;
        top: 12px;
        right: 12px;
        transform: scale(1.2);
    }

    .category-icon {
        width: 32px;
        height: 32px;
        margin-bottom: 8px;
        opacity: 0.7;
    }

    .category-name {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 4px;
        color: #2d3748;
    }

    .category-description {
        font-size: 12px;
        color: #718096;
        line-height: 1.4;
    }

    .format-selector {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
    }

    .format-option {
        flex: 1;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }

    .format-option:hover {
        border-color: #667eea;
    }

    .format-option.selected {
        border-color: #667eea;
        background: #f7fafc;
    }

    .format-option input[type="radio"] {
        display: none;
    }

    .export-button {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        margin-bottom: 16px;
    }

    .export-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .export-button:disabled {
        background: #cbd5e0;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .import-dropzone {
        border: 2px dashed #cbd5e0;
        border-radius: 8px;
        padding: 40px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }

    .import-dropzone:hover,
    .import-dropzone.dragover {
        border-color: #667eea;
        background: #f7fafc;
    }

    .import-dropzone.has-file {
        border-color: #48bb78;
        background: #f0fff4;
    }

    .file-info {
        background: #e6fffa;
        border: 1px solid #81e6d9;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 16px;
        display: none;
    }

    .file-info.show {
        display: block;
    }

    .merge-strategy {
        margin-bottom: 20px;
    }

    .merge-option {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .merge-option:hover {
        border-color: #667eea;
    }

    .merge-option.selected {
        border-color: #667eea;
        background: #f7fafc;
    }

    .merge-option input[type="radio"] {
        margin-right: 8px;
    }

    .preview-container {
        background: #f7fafc;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
        display: none;
    }

    .preview-container.show {
        display: block;
    }

    .preview-section {
        margin-bottom: 20px;
    }

    .preview-section h4 {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 12px;
        color: #2d3748;
    }

    .preview-item {
        background: white;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 8px;
        border-left: 4px solid #667eea;
    }

    .preview-item.conflict {
        border-left-color: #f56565;
        background: #fed7d7;
    }

    .preview-item.new {
        border-left-color: #48bb78;
        background: #c6f6d5;
    }

    .conflict-resolver {
        background: white;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        border: 1px solid #e2e8f0;
    }

    .conflict-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
    }

    .conflict-action {
        padding: 6px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        background: white;
        cursor: pointer;
        font-size: 12px;
    }

    .conflict-action.selected {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }

    .progress-bar {
        background: #e2e8f0;
        border-radius: 8px;
        height: 8px;
        overflow: hidden;
        margin: 16px 0;
        display: none;
    }

    .progress-bar.show {
        display: block;
    }

    .progress-fill {
        background: linear-gradient(90deg, #667eea, #764ba2);
        height: 100%;
        width: 0%;
        transition: width 0.3s ease;
    }

    .status-message {
        padding: 12px;
        border-radius: 8px;
        margin: 16px 0;
        display: none;
    }

    .status-message.show {
        display: block;
    }

    .status-message.success {
        background: #c6f6d5;
        border: 1px solid #48bb78;
        color: #22543d;
    }

    .status-message.error {
        background: #fed7d7;
        border: 1px solid #f56565;
        color: #742a2a;
    }

    .status-message.warning {
        background: #fefcbf;
        border: 1px solid #d69e2e;
        color: #744210;
    }

    .reset-section {
        border-top: 1px solid #e2e8f0;
        padding-top: 20px;
        margin-top: 20px;
    }

    .danger-button {
        background: #f56565;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 12px 24px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .danger-button:hover {
        background: #e53e3e;
        transform: translateY(-2px);
    }

    .loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 1s ease-in-out infinite;
        margin-right: 8px;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    @media (max-width: 768px) {
        .settings-export-container {
            padding: 12px;
        }

        .category-selector {
            grid-template-columns: 1fr;
        }

        .format-selector {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="settings-export-container">
    <div class="settings-card">
        <div class="settings-card-header">
            <h2>Settings Import/Export</h2>
            <p>Export your preferences for backup or transfer to another account. Import settings from a previously exported file.</p>
        </div>
        
        <!-- Export Section -->
        <div class="settings-card-body">
            <h3 class="section-title">Export Settings</h3>
            <p class="text-muted">Choose which settings to include in your export. Sensitive data like passwords and API keys are automatically excluded.</p>
            
            <div class="category-selector">
                @foreach($exportable_categories as $key => $category)
                <div class="category-item" data-category="{{ $key }}">
                    <input type="checkbox" id="export_{{ $key }}" value="{{ $key }}" checked>
                    <div class="category-icon">
                        @switch($category['icon'])
                            @case('settings')
                                <svg fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                                </svg>
                                @break
                            @case('users')
                                <svg fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                                </svg>
                                @break
                            @case('map-pin')
                                <svg fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                </svg>
                                @break
                            @case('dollar-sign')
                                <svg fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                                </svg>
                                @break
                            @case('bell')
                                <svg fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
                                </svg>
                                @break
                        @endswitch
                    </div>
                    <div class="category-name">{{ $category['name'] }}</div>
                    <div class="category-description">{{ $category['description'] }}</div>
                </div>
                @endforeach
            </div>

            <div class="format-selector">
                @foreach($supported_formats as $format => $info)
                <div class="format-option {{ $loop->first ? 'selected' : '' }}" data-format="{{ $format }}">
                    <input type="radio" name="export_format" value="{{ $format }}" {{ $loop->first ? 'checked' : '' }}>
                    <div class="format-name">{{ $info['name'] }}</div>
                    <div class="format-description">{{ $info['description'] }}</div>
                </div>
                @endforeach
            </div>

            <button type="button" class="export-button" id="exportButton">
                <span class="button-text">Export Selected Settings</span>
                <div class="loading-spinner" style="display: none;"></div>
            </button>

            @if($last_export)
            <div class="last-export-info">
                <small class="text-muted">Last export: {{ $last_export['date'] }} ({{ $last_export['categories'] }})</small>
            </div>
            @endif
        </div>
    </div>

    <!-- Import Section -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3>Import Settings</h3>
            <p>Upload a previously exported settings file to restore your preferences.</p>
        </div>
        
        <div class="settings-card-body">
            <div class="import-dropzone" id="importDropzone">
                <div class="dropzone-content">
                    <svg width="48" height="48" fill="currentColor" class="upload-icon" style="opacity: 0.5; margin-bottom: 16px;">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"/>
                    </svg>
                    <div class="dropzone-text">
                        <strong>Click to select a file</strong> or drag and drop
                    </div>
                    <div class="dropzone-subtext">
                        Supports JSON files up to 2MB
                    </div>
                </div>
                <input type="file" id="importFile" accept=".json,application/json" style="display: none;">
            </div>

            <div class="file-info" id="fileInfo">
                <div class="file-details">
                    <strong>File:</strong> <span id="fileName"></span><br>
                    <strong>Size:</strong> <span id="fileSize"></span><br>
                    <strong>Categories:</strong> <span id="fileCategories"></span>
                </div>
            </div>

            <div class="merge-strategy">
                <h4>Import Strategy</h4>
                <p class="text-muted">Choose how to handle conflicts with existing settings.</p>
                
                <div class="merge-option selected" data-strategy="merge">
                    <input type="radio" name="merge_strategy" value="merge" checked>
                    <strong>Merge with existing</strong>
                    <div class="text-small text-muted">Update existing settings, keep unchanged ones</div>
                </div>
                
                <div class="merge-option" data-strategy="overwrite">
                    <input type="radio" name="merge_strategy" value="overwrite">
                    <strong>Overwrite existing</strong>
                    <div class="text-small text-muted">Replace all existing settings with imported ones</div>
                </div>
                
                <div class="merge-option" data-strategy="skip_existing">
                    <input type="radio" name="merge_strategy" value="skip_existing">
                    <strong>Skip existing</strong>
                    <div class="text-small text-muted">Only add new settings, don't change existing ones</div>
                </div>
            </div>

            <button type="button" class="export-button" id="previewButton" disabled>
                <span class="button-text">Preview Import</span>
                <div class="loading-spinner" style="display: none;"></div>
            </button>

            <div class="preview-container" id="previewContainer">
                <h4>Import Preview</h4>
                <div id="previewContent"></div>
                
                <div class="preview-actions">
                    <button type="button" class="export-button" id="confirmImportButton">
                        <span class="button-text">Confirm Import</span>
                        <div class="loading-spinner" style="display: none;"></div>
                    </button>
                    <button type="button" class="secondary-button" id="cancelImportButton">
                        Cancel Import
                    </button>
                </div>
            </div>

            <div class="progress-bar" id="importProgress">
                <div class="progress-fill"></div>
            </div>

            <div class="status-message" id="statusMessage"></div>
        </div>
    </div>

    <!-- Reset Section -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3>Reset Settings</h3>
            <p>Reset your settings to default values. This action can be backed up automatically.</p>
        </div>
        
        <div class="settings-card-body">
            <div class="reset-options">
                <label>
                    <input type="checkbox" id="createBackup" checked> Create backup before reset
                </label>
            </div>
            
            <div class="category-selector">
                @foreach($exportable_categories as $key => $category)
                <div class="category-item" data-category="{{ $key }}">
                    <input type="checkbox" id="reset_{{ $key }}" value="{{ $key }}" checked>
                    <div class="category-name">{{ $category['name'] }}</div>
                </div>
                @endforeach
            </div>

            <button type="button" class="danger-button" id="resetButton">
                <span class="button-text">Reset Selected Settings</span>
                <div class="loading-spinner" style="display: none;"></div>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/modules/SettingsExportManager.js?v=' . filemtime(public_path('js/modules/SettingsExportManager.js'))) }}"></script>
@endpush
