<div class="delete-account-section">
    <div class="mb-4">
        <h5 class="mb-2 text-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ __('Delete Account') }}
        </h5>
        <p class="text-muted small">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </div>

    {{-- Delete Account Button --}}
    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmAccountDeletionModal">
        <i class="fas fa-trash-alt me-2"></i>
        {{ __('Delete My Account') }}
    </button>

    {{-- Account Deletion Confirmation Modal --}}
    <div class="modal fade" id="confirmAccountDeletionModal" tabindex="-1" aria-labelledby="confirmAccountDeletionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-2">
                    <h5 class="modal-title text-danger" id="confirmAccountDeletionModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ __('Delete Account Confirmation') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form method="post" action="{{ route('profile.destroy') }}" id="account-deletion-form">
                    <div class="modal-body">
                        @csrf
                        @method('delete')

                        <div class="alert alert-danger" role="alert">
                            <h6 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ __('Are you absolutely sure?') }}
                            </h6>
                            <p class="mb-0">
                                {{ __('This action cannot be undone. This will permanently delete your account and all associated data.') }}
                            </p>
                        </div>

                        <p class="mb-3">
                            {{ __('Please enter your password to confirm you would like to permanently delete your account.') }}
                        </p>

                        <div class="mb-3">
                            <label for="delete_account_password" class="form-label visually-hidden">
                                {{ __('Password') }}
                            </label>
                            <input type="password" 
                                   class="form-control @error('password', 'userDeletion') is-invalid @enderror" 
                                   id="delete_account_password" 
                                   name="password" 
                                   required 
                                   placeholder="{{ __('Enter your password to confirm') }}">
                            @error('password', 'userDeletion')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="modal-footer border-0 pt-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash-alt me-2"></i>
                            {{ __('Yes, Delete My Account') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Show modal if there are userDeletion errors
    @if(isset($errors) && $errors->userDeletion && $errors->userDeletion->isNotEmpty())
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('confirmAccountDeletionModal'));
            modal.show();
        });
    @endif
    
    // Add confirmation before form submission
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('account-deletion-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!confirm('{{ __('This is your last chance! Are you absolutely sure you want to delete your account? This cannot be undone.') }}')) {
                    e.preventDefault();
                }
            });
        }
    });
</script>
@endpush
