<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FixCommentsAndAttachmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Fixing comments and attachments tables...');
        
        // Fix ticket_comments table
        $this->fixComments();
        
        // Fix ticket_attachments table  
        $this->fixAttachments();
        
        $this->command->info('Comments and attachments tables fixed successfully!');
    }
    
    private function fixComments()
    {
        $this->command->info('Fixing ticket_comments table...');
        
        // Update existing comments to have proper UUIDs
        $commentsWithoutUuid = DB::table('ticket_comments')->whereNull('uuid')->get();
        foreach ($commentsWithoutUuid as $comment) {
            DB::table('ticket_comments')->where('id', $comment->id)->update([
                'uuid' => Str::uuid()->toString()
            ]);
        }
        
        // Fix missing type field
        DB::table('ticket_comments')->whereNull('type')->update([
            'type' => 'comment'
        ]);
        
        // Fix missing is_solution field
        DB::table('ticket_comments')->whereNull('is_solution')->update([
            'is_solution' => false
        ]);
        
        $this->command->info('Fixed ' . $commentsWithoutUuid->count() . ' comments');
    }
    
    private function fixAttachments()
    {
        $this->command->info('Fixing ticket_attachments table...');
        
        // Update existing attachments to have proper UUIDs
        $attachmentsWithoutUuid = DB::table('ticket_attachments')->whereNull('uuid')->get();
        foreach ($attachmentsWithoutUuid as $attachment) {
            DB::table('ticket_attachments')->where('id', $attachment->id)->update([
                'uuid' => Str::uuid()->toString()
            ]);
        }
        
        $this->command->info('Fixed ' . $attachmentsWithoutUuid->count() . ' attachments');
    }
}
