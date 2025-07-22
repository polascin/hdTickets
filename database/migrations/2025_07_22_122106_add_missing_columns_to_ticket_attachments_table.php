<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ticket_attachments', function (Blueprint $table) {
            $table->string('uuid')->nullable()->after('id');
            $table->renameColumn('file_path', 'filepath');
            $table->renameColumn('file_name', 'filename');
            $table->renameColumn('mime_type', 'filetype');
            $table->renameColumn('file_size', 'filesize');
            $table->renameColumn('uploaded_by', 'user_id');
            $table->dropColumn('original_name');
            $table->json('metadata')->nullable()->after('filesize');
            $table->softDeletes();
            
            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_attachments', function (Blueprint $table) {
            $table->renameColumn('filepath', 'file_path');
            $table->renameColumn('filename', 'file_name');
            $table->renameColumn('filetype', 'mime_type');
            $table->renameColumn('filesize', 'file_size');
            $table->renameColumn('user_id', 'uploaded_by');
            $table->string('original_name')->after('filename');
            $table->dropColumn(['uuid', 'metadata']);
            $table->dropSoftDeletes();
        });
    }
};
