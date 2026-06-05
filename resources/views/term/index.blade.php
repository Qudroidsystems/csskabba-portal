@extends('layouts.master')
@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Term Management</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Term Management</a></li>
                                <li class="breadcrumb-item active">Term</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('danger'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('danger') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Promotional term info banner --}}
            @php $promotionalTerm = $terms->firstWhere('is_promotional', true); @endphp
            @if ($promotionalTerm)
                <div class="alert alert-success d-flex align-items-center gap-2 py-2">
                    <i class="ri-award-line fs-5"></i>
                    <span>
                        <strong>Promotional Term:</strong>
                        <span class="badge bg-success ms-1">{{ $promotionalTerm->term }}</span>
                        — Student promotion is evaluated at the end of this term.
                    </span>
                </div>
            @else
                <div class="alert alert-warning d-flex align-items-center gap-2 py-2">
                    <i class="ri-alert-line fs-5"></i>
                    <span>No promotional term is currently set. Students will show <strong>Awaiting Final Term</strong> on their reports.</span>
                </div>
            @endif

            <div id="termList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-3">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Search terms">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">
                                        Terms
                                        <span class="badge bg-dark-subtle text-dark ms-1">{{ count($terms) }}</span>
                                    </h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex flex-wrap align-items-start gap-2">
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()">
                                            <i class="ri-delete-bin-2-line"></i>
                                        </button>
                                        @can('Create term')
                                            <button type="button" class="btn btn-primary add-btn"
                                                    data-bs-toggle="modal" data-bs-target="#addTermModal">
                                                <i class="bi bi-plus-circle align-baseline me-1"></i> Create Term
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0" id="kt_roles_view_table">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                                <th class="w-10px pe-2">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" id="checkAll" />
                                                    </div>
                                                </th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="term">Term</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="status">Status</th>
                                                <th class="min-w-150px">Promotional Term</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="datereg">Date Updated</th>
                                                <th class="min-w-100px">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600 list form-check-all">
                                            @forelse ($terms as $term)
                                                <tr data-url="{{ route('term.deleteterm', ['termid' => $term->id]) }}" id="term-row-{{ $term->id }}">
                                                    <td class="id" data-id="{{ $term->id }}">
                                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                            <input class="form-check-input" type="checkbox" name="chk_child" />
                                                        </div>
                                                    </td>
                                                    <td class="term" data-term="{{ $term->term }}">{{ $term->term }}</td>
                                                    <td class="status">
                                                        <div class="form-check form-switch d-inline-block">
                                                            <input type="checkbox"
                                                                   class="form-check-input status-toggle"
                                                                   data-id="{{ $term->id }}"
                                                                   id="status-switch-{{ $term->id }}"
                                                                   {{ $term->status ? 'checked' : '' }}>
                                                            <label class="form-check-label ms-1 status-label {{ $term->status ? 'active' : 'inactive' }}"
                                                                   for="status-switch-{{ $term->id }}"
                                                                   data-id="{{ $term->id }}">
                                                                {{ $term->status ? 'Active' : 'Inactive' }}
                                                            </label>
                                                        </div>
                                                    </td>
                                                    {{-- ── PROMOTIONAL TERM TOGGLE ── --}}
                                                    <td>
                                                        <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                                                            <input type="checkbox"
                                                                   class="form-check-input promotional-toggle"
                                                                   role="switch"
                                                                   data-id="{{ $term->id }}"
                                                                   id="promo-switch-{{ $term->id }}"
                                                                   {{ $term->is_promotional ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="promo-switch-{{ $term->id }}">
                                                                @if ($term->is_promotional)
                                                                    <span class="badge bg-success-subtle text-success border border-success-subtle promo-label-badge" id="promo-label-{{ $term->id }}">
                                                                        <i class="ri-award-line me-1"></i>Promotional
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-light text-muted promo-label-badge" id="promo-label-{{ $term->id }}">
                                                                        Not Promotional
                                                                    </span>
                                                                @endif
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td class="datereg">{{ $term->updated_at->format('Y-m-d') }}</td>
                                                    <td>
                                                        <ul class="d-flex gap-2 list-unstyled mb-0">
                                                            @can('Update term')
                                                                <li>
                                                                    <a href="javascript:void(0);"
                                                                       class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn"
                                                                       data-id="{{ $term->id }}"
                                                                       data-term="{{ $term->term }}"
                                                                       data-status="{{ $term->status ? 1 : 0 }}"
                                                                       data-is-promotional="{{ $term->is_promotional ? 1 : 0 }}">
                                                                        <i class="ph-pencil"></i>
                                                                    </a>
                                                                </li>
                                                            @endcan
                                                            @can('Delete term')
                                                                <li>
                                                                    <a href="javascript:void(0);"
                                                                       class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn"
                                                                       data-id="{{ $term->id }}">
                                                                        <i class="ph-trash"></i>
                                                                    </a>
                                                                </li>
                                                            @endcan
                                                        </ul>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="noresult text-center" style="display: table-cell;">No results found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── ADD TERM MODAL ── --}}
            <div id="addTermModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Term</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form autocomplete="off" id="add-term-form">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="term" class="form-label">Term Name</label>
                                    <input type="text" name="term" id="term" class="form-control" placeholder="e.g. First Term" required>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" id="add-status-switch" name="status" checked>
                                        <label class="form-check-label" for="add-status-switch">Active</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" id="add-promo-switch" name="is_promotional">
                                        <label class="form-check-label" for="add-promo-switch">
                                            Promotional Term
                                            <small class="text-muted d-block">If enabled, all other terms will lose their promotional status.</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="alert alert-danger d-none" id="alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="add-btn">Add Term</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ── EDIT TERM MODAL ── --}}
            <div id="editModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Term</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form autocomplete="off" id="edit-term-form">
                            <div class="modal-body">
                                <input type="hidden" id="edit-id-field" name="id">
                                <div class="mb-3">
                                    <label for="edit-term" class="form-label">Term Name</label>
                                    <input type="text" name="term" id="edit-term" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" id="edit-status-switch" name="status">
                                        <label class="form-check-label" for="edit-status-switch">Active</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" id="edit-promo-switch" name="is_promotional">
                                        <label class="form-check-label" for="edit-promo-switch">
                                            Promotional Term
                                            <small class="text-muted d-block">If enabled, all other terms will lose their promotional status.</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="alert alert-danger d-none" id="edit-alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="update-btn">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- DELETE MODAL --}}
            <div id="deleteRecordModal" class="modal fade" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body text-center">
                            <h4>Are you sure?</h4>
                            <p>You won't be able to revert this!</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-danger" id="delete-record">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
.form-check-input.status-toggle,
.form-check-input.promotional-toggle {
    width: 3em; height: 1.5em; cursor: pointer; transition: all .3s ease;
}
.form-check-input.promotional-toggle:checked {
    background-color: #198754; border-color: #198754;
}
.form-check-input.status-toggle:checked {
    background-color: #198754; border-color: #198754;
}
.status-label.active   { color: #198754; font-weight: 500; }
.status-label.inactive { color: #dc3545; font-weight: 500; }
.promotional-toggle:disabled { cursor: not-allowed; opacity: .6; }
</style>

<script>
$(function () {
    const CSRF = '{{ csrf_token() }}';

    /* ── Promotional toggle (inline, no page reload) ── */
    $(document).on('change', '.promotional-toggle', async function () {
        const id        = $(this).data('id');
        const isPromo   = $(this).is(':checked');
        const $toggle   = $(this);
        const $label    = $(`#promo-label-${id}`);

        $toggle.prop('disabled', true);

        try {
            const res  = await fetch(`/term/${id}/promotional`, {
                method:  'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept':       'application/json',
                },
                body: JSON.stringify({ is_promotional: isPromo ? 1 : 0 }),
            });
            const data = await res.json();

            if (res.ok && data.success) {
                // Reset ALL other toggles and labels since only one can be promotional
                if (isPromo) {
                    $('.promotional-toggle').not($toggle).each(function () {
                        const otherId = $(this).data('id');
                        $(this).prop('checked', false);
                        $(`#promo-label-${otherId}`)
                            .removeClass('bg-success-subtle text-success border border-success-subtle')
                            .addClass('bg-light text-muted')
                            .html('Not Promotional');
                    });
                }

                // Update current label
                if (isPromo) {
                    $label
                        .removeClass('bg-light text-muted')
                        .addClass('bg-success-subtle text-success border border-success-subtle')
                        .html('<i class="ri-award-line me-1"></i>Promotional');
                } else {
                    $label
                        .removeClass('bg-success-subtle text-success border border-success-subtle')
                        .addClass('bg-light text-muted')
                        .html('Not Promotional');
                }

                Swal.fire({ icon: 'success', text: data.message, timer: 2000, showConfirmButton: false });
            } else {
                // Revert toggle on failure
                $toggle.prop('checked', !isPromo);
                Swal.fire('Error', data.message || 'Failed to update.', 'error');
            }
        } catch (err) {
            $toggle.prop('checked', !isPromo);
            Swal.fire('Error', 'An error occurred.', 'error');
        } finally {
            $toggle.prop('disabled', false);
        }
    });

    /* ── Edit button: pre-fill modal including is_promotional ── */
    $(document).on('click', '.edit-item-btn', function () {
        const id          = $(this).data('id');
        const term        = $(this).data('term');
        const status      = $(this).data('status');
        const isPromo     = $(this).data('is-promotional');

        $('#edit-id-field').val(id);
        $('#edit-term').val(term);
        $('#edit-status-switch').prop('checked', status == 1);
        $('#edit-promo-switch').prop('checked',  isPromo == 1);
        $('#edit-alert-error-msg').addClass('d-none').text('');
        $('#editModal').modal('show');
    });

    /* ── Add form submit ── */
    $('#add-term-form').on('submit', async function (e) {
        e.preventDefault();
        const btn = $('#add-btn');
        btn.prop('disabled', true).text('Adding…');

        try {
            const res  = await fetch('{{ route("term.store") }}', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({
                    term:           $('#term').val(),
                    status:         $('#add-status-switch').is(':checked') ? 1 : 0,
                    is_promotional: $('#add-promo-switch').is(':checked') ? 1 : 0,
                }),
            });
            const data = await res.json();

            if (res.ok && data.success) {
                Swal.fire({ icon: 'success', text: data.message, timer: 2000, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                $('#alert-error-msg').removeClass('d-none').text(data.message || 'Failed.');
            }
        } catch (err) {
            $('#alert-error-msg').removeClass('d-none').text('An error occurred.');
        } finally {
            btn.prop('disabled', false).text('Add Term');
        }
    });

    /* ── Edit form submit ── */
    $('#edit-term-form').on('submit', async function (e) {
        e.preventDefault();
        const id  = $('#edit-id-field').val();
        const btn = $('#update-btn');
        btn.prop('disabled', true).text('Updating…');

        try {
            const res  = await fetch(`/term/${id}`, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({
                    _method:        'PUT',
                    term:           $('#edit-term').val(),
                    status:         $('#edit-status-switch').is(':checked') ? 1 : 0,
                    is_promotional: $('#edit-promo-switch').is(':checked') ? 1 : 0,
                }),
            });
            const data = await res.json();

            if (res.ok && data.success) {
                Swal.fire({ icon: 'success', text: data.message, timer: 2000, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                $('#edit-alert-error-msg').removeClass('d-none').text(data.message || 'Failed.');
            }
        } catch (err) {
            $('#edit-alert-error-msg').removeClass('d-none').text('An error occurred.');
        } finally {
            btn.prop('disabled', false).text('Update');
        }
    });
});
</script>
@endsection
