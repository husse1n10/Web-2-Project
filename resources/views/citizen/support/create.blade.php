@extends('layouts.app')
@section('title', 'New Support Ticket')
@section('page-title', 'New Support Ticket')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card citizen-reveal" data-citizen-reveal>
            <div class="card-header">
                <span class="card-title">Open a new ticket</span>
                <div class="text-muted" style="font-size:.8rem">An admin will reply as soon as possible.</div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('citizen.support.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Subject *</label>
                        <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                               value="{{ old('subject') }}" maxlength="200" required
                               placeholder="e.g. Can't verify my phone number">
                        @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Describe your issue *</label>
                        <textarea name="body" rows="6" class="form-control @error('body') is-invalid @enderror"
                                  maxlength="5000" required
                                  placeholder="Give us as much detail as possible — what you tried, what happened, any error messages...">{{ old('body') }}</textarea>
                        @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Attachment (optional)</label>
                        <input type="file" name="attachment"
                               class="form-control @error('attachment') is-invalid @enderror"
                               accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt">
                        <div class="form-text">Max 5 MB. Allowed: images, PDF, Word, plain text.</div>
                        @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('citizen.support') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-1"></i> Submit Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
