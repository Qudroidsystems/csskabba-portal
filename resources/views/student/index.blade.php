@extends('layouts.master')
@section('content')
<?php
use Spatie\Permission\Models\Role;
?>
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Student Management</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                                <li class="breadcrumb-item active">Students</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            <style>
                /* ========================================================
                   ROOT VARIABLES
                   ======================================================== */
                :root {
                    --sm-primary:   #1e3a5f;
                    --sm-accent:    #2563eb;
                    --sm-success:   #16a34a;
                    --sm-warning:   #d97706;
                    --sm-danger:    #dc2626;
                    --sm-purple:    #7c3aed;
                    --sm-pink:      #db2777;
                    --sm-teal:      #0d9488;
                    --sm-muted:     #6b7280;
                    --sm-border:    #e2e8f0;
                    --sm-bg:        #f8fafc;
                    --sm-radius:    14px;
                    --sm-shadow:    0 2px 12px rgba(0,0,0,.08);
                    --sm-shadow-lg: 0 8px 32px rgba(0,0,0,.12);
                }

                /* ========================================================
                   HERO BANNER
                   ======================================================== */
                .sm-hero {
                    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 55%, #4f46e5 100%);
                    border-radius: var(--sm-radius);
                    padding: 26px 30px;
                    margin-bottom: 22px;
                    position: relative;
                    overflow: hidden;
                }
                .sm-hero::before {
                    content: '';
                    position: absolute; top: -70px; right: -70px;
                    width: 220px; height: 220px;
                    background: rgba(255,255,255,.06);
                    border-radius: 50%;
                }
                .sm-hero h1 { font-size: 21px; font-weight: 700; color: #fff; margin: 0 0 5px; position: relative; z-index: 1; }
                .sm-hero p  { font-size: 13px; color: rgba(255,255,255,.75); margin: 0; position: relative; z-index: 1; }

                /* ========================================================
                   STAT CARDS
                   ======================================================== */
                .sm-stat {
                    background: #fff;
                    border: 1px solid var(--sm-border);
                    border-radius: var(--sm-radius);
                    padding: 20px 22px;
                    position: relative;
                    overflow: hidden;
                    transition: transform .2s, box-shadow .2s;
                    margin-bottom: 20px;
                }
                .sm-stat:hover { transform: translateY(-3px); box-shadow: var(--sm-shadow-lg); }
                .sm-stat::before {
                    content: '';
                    position: absolute; top: 0; left: 0; right: 0; height: 3px;
                    background: var(--sc, var(--sm-accent));
                }
                .sm-stat-icon {
                    width: 50px; height: 50px; border-radius: 12px;
                    display: flex; align-items: center; justify-content: center;
                    font-size: 20px; color: #fff; margin-bottom: 14px;
                    background: var(--sc, var(--sm-accent));
                    box-shadow: 0 4px 12px rgba(0,0,0,.12);
                }
                .sm-stat-value { font-size: 28px; font-weight: 800; color: var(--sm-primary); line-height: 1; margin-bottom: 4px; }
                .sm-stat-label { font-size: 11px; font-weight: 600; color: var(--sm-muted); text-transform: uppercase; letter-spacing: .04em; }
                .sm-stat-sub   { font-size: 11px; color: var(--sm-muted); margin-top: 4px; }
                .sm-stat-sub .up   { color: var(--sm-success); }
                .sm-stat-sub .down { color: var(--sm-danger); }

                /* ========================================================
                   DASHBOARD STATS CARD (legacy class kept for compat)
                   ======================================================== */
                .dashboard-stats-card {
                    border: none;
                    border-radius: 16px;
                    box-shadow: 0 4px 20px rgba(0,0,0,.08);
                    transition: all .3s ease;
                    margin-bottom: 24px;
                    position: relative;
                    overflow: hidden;
                }
                .dashboard-stats-card:hover { transform: translateY(-8px); box-shadow: 0 12px 30px rgba(0,0,0,.15); }
                .dashboard-stats-card::before {
                    content: '';
                    position: absolute; top: 0; left: 0; right: 0; height: 4px;
                    background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
                }
                .dashboard-stats-card .card-body { padding: 24px; position: relative; z-index: 1; }
                .dashboard-stats-card .stats-icon {
                    width: 64px; height: 64px; border-radius: 16px;
                    display: flex; align-items: center; justify-content: center;
                    margin-bottom: 20px; font-size: 28px;
                    background: rgba(255,255,255,.2); backdrop-filter: blur(10px); color: white;
                }
                .dashboard-stats-card .stats-content { display: flex; flex-direction: column; gap: 8px; }
                .dashboard-stats-card .stats-label { font-size: 14px; font-weight: 500; color: rgba(255,255,255,.9); text-transform: uppercase; letter-spacing: .5px; }
                .dashboard-stats-card .stats-value { font-size: 32px; font-weight: 700; color: white; line-height: 1; }
                .dashboard-stats-card .stats-change { font-size: 12px; font-weight: 500; display: flex; align-items: center; gap: 4px; color: rgba(255,255,255,.8); }
                .dashboard-stats-card .stats-change.positive { color: #10b981; }
                .stats-primary { --gradient-start:#4361ee; --gradient-end:#3a0ca3; background: linear-gradient(135deg,#4361ee 0%,#3a0ca3 100%); }
                .stats-success { --gradient-start:#10b981; --gradient-end:#047857; background: linear-gradient(135deg,#10b981 0%,#047857 100%); }
                .stats-warning { --gradient-start:#f59e0b; --gradient-end:#b45309; background: linear-gradient(135deg,#f59e0b 0%,#b45309 100%); }
                .stats-info    { --gradient-start:#0ea5e9; --gradient-end:#0369a1; background: linear-gradient(135deg,#0ea5e9 0%,#0369a1 100%); }
                .stats-purple  { --gradient-start:#8b5cf6; --gradient-end:#7c3aed; background: linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%); }
                .stats-pink    { --gradient-start:#ec4899; --gradient-end:#be185d; background: linear-gradient(135deg,#ec4899 0%,#be185d 100%); }
                .stats-teal    { --gradient-start:#14b8a6; --gradient-end:#0d9488; background: linear-gradient(135deg,#14b8a6 0%,#0d9488 100%); }

                /* ========================================================
                   STUDENT PROFILE CARD (card view)
                   ======================================================== */
                .student-profile-card {
                    border: 1px solid #e5e7eb;
                    border-radius: 16px;
                    overflow: hidden;
                    transition: all .3s ease;
                    background: white;
                    height: 100%;
                    position: relative;
                    box-shadow: 0 2px 8px rgba(0,0,0,.04);
                }
                .student-profile-card:hover { border-color: #3b82f6; box-shadow: 0 8px 30px rgba(59,130,246,.15); transform: translateY(-4px); }
                .student-profile-card.selected { border-color: #3b82f6; background-color: #f0f9ff; }
                .student-profile-card .card-header {
                    background: linear-gradient(135deg,#667eea 0%,#764ba2 100%);
                    padding: 20px; position: relative; min-height: 120px;
                }
                .student-profile-card .avatar-container {
                    position: absolute; top: 16px; right: 16px;
                    width: 76px; height: 76px; border-radius: 14px;
                    overflow: hidden; border: 3px solid white;
                    box-shadow: 0 4px 12px rgba(0,0,0,.15); background: white;
                    cursor: pointer; transition: transform .2s;
                }
                .student-profile-card .avatar-container:hover { transform: scale(1.06); }
                .student-profile-card .avatar { width: 100%; height: 100%; object-fit: cover; }
                .student-profile-card .avatar-initials {
                    width: 100%; height: 100%;
                    display: flex; align-items: center; justify-content: center;
                    font-size: 26px; font-weight: 700; color: #667eea;
                    background: linear-gradient(135deg,#f3f4f6 0%,#e5e7eb 100%);
                    cursor: pointer;
                }
                .student-profile-card .header-content { padding-right: 96px; }
                .student-profile-card .student-name { font-size: 18px; font-weight: 700; color: white; margin-bottom: 5px; line-height: 1.3; }
                .student-profile-card .student-admission {
                    font-size: 12px; color: rgba(255,255,255,.9);
                    background: rgba(255,255,255,.15); padding: 3px 10px;
                    border-radius: 20px; display: inline-block; backdrop-filter: blur(10px);
                }
                .student-profile-card .card-body { padding: 16px; }
                .student-profile-card .student-info-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 10px; margin-bottom: 16px; }
                .student-profile-card .info-item { display: flex; flex-direction: column; gap: 3px; }
                .student-profile-card .info-label { font-size: 10px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .5px; }
                .student-profile-card .info-value { font-size: 13px; font-weight: 600; color: #374151; }
                .student-profile-card .status-badge {
                    display: inline-flex; align-items: center; gap: 5px;
                    padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; margin-bottom: 14px;
                }
                .student-profile-card .status-active   { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
                .student-profile-card .status-inactive { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
                .student-profile-card .status-new      { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
                .student-profile-card .status-old      { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
                .student-profile-card .action-buttons  { display: flex; gap: 7px; padding-top: 14px; border-top: 1px solid #e5e7eb; }
                .student-profile-card .action-btn {
                    flex: 1; padding: 9px 4px; border-radius: 10px; border: none;
                    font-size: 12px; font-weight: 600; cursor: pointer;
                    transition: all .2s; display: flex; align-items: center; justify-content: center; gap: 5px;
                }
                .student-profile-card .view-btn   { background-color: #3b82f6; color: white; }
                .student-profile-card .view-btn:hover   { background-color: #2563eb; transform: translateY(-2px); }
                .student-profile-card .edit-btn   { background-color: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
                .student-profile-card .edit-btn:hover   { background-color: #e5e7eb; transform: translateY(-2px); }
                .student-profile-card .delete-btn { background-color: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
                .student-profile-card .delete-btn:hover { background-color: #fee2e2; transform: translateY(-2px); }
                .student-profile-card .checkbox-container { position: absolute; top: 14px; left: 14px; z-index: 2; }
                .student-profile-card .form-check-input { width: 18px; height: 18px; cursor: pointer; border: 2px solid white; background-color: rgba(255,255,255,.2); }
                .student-profile-card .form-check-input:checked { background-color: #3b82f6; border-color: #3b82f6; }

                /* ========================================================
                   TABLE
                   ======================================================== */
                .data-table-container { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.04); }
                .data-table { margin-bottom: 0; }
                .data-table thead { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); }
                .data-table thead th { border: none; color: white; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: .5px; padding: 14px 12px; }
                .data-table tbody tr { transition: all .2s ease; border-bottom: 1px solid #e5e7eb; }
                .data-table tbody tr:hover { background-color: #f9fafb; }
                .data-table tbody tr.selected { background-color: #f0f9ff; }
                .data-table tbody td { padding: 14px 12px; vertical-align: middle; }

                /* avatar in table */
                .tbl-avatar {
                    width: 44px; height: 44px; border-radius: 11px;
                    object-fit: cover; border: 2px solid #e5e7eb;
                    cursor: pointer; transition: transform .2s, box-shadow .2s;
                }
                .tbl-avatar:hover { transform: scale(1.08); box-shadow: 0 4px 14px rgba(0,0,0,.15); }
                .tbl-avatar-init {
                    width: 44px; height: 44px; border-radius: 11px;
                    background: linear-gradient(135deg,#667eea,#764ba2);
                    display: inline-flex; align-items: center; justify-content: center;
                    font-size: 16px; font-weight: 700; color: #fff;
                    border: 2px solid #e5e7eb; cursor: pointer;
                    transition: transform .2s, box-shadow .2s;
                }
                .tbl-avatar-init:hover { transform: scale(1.08); box-shadow: 0 4px 14px rgba(0,0,0,.15); }

                /* ========================================================
                   FILTER BAR
                   ======================================================== */
                .filter-bar { background: white; padding: 18px 20px; border-radius: 0; margin-bottom: 0; border-bottom: 1px solid #e5e7eb; background: #fafbfc; }
                .search-box { position: relative; }
                .search-box input { padding-left: 42px; padding-right: 38px; border-radius: 10px; border: 1.5px solid #e5e7eb; height: 44px; font-size: 13px; transition: all .3s ease; width: 100%; }
                .search-box input:focus { border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,.1); outline: none; }
                .search-box .search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 16px; }
                .search-box .clear-search { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: #6b7280; font-size: 15px; padding: 4px 8px; cursor: pointer; display: none; z-index: 10; }
                .search-box .clear-search:hover { color: #dc2626; }
                .filter-bar select { border-radius: 10px; border: 1.5px solid #e5e7eb; height: 44px; font-size: 13px; padding: 0 12px; width: 100%; transition: border .2s; background: #fff; }
                .filter-bar select:focus { border-color: #667eea; outline: none; }

                /* ========================================================
                   PAGINATION
                   ======================================================== */
                .pagination-container { display: flex; justify-content: space-between; align-items: center; padding: 18px 20px; background: white; border-top: 1px solid #e5e7eb; flex-wrap: wrap; gap: 10px; }
                .pagination .page-link { border: none; color: #374151; margin: 0 3px; border-radius: 9px; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 13px; transition: all .3s ease; }
                .pagination .page-link:hover { background-color: #f3f4f6; color: #667eea; }
                .pagination .page-item.active .page-link { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color: white; }

                /* ========================================================
                   EMPTY / LOADING STATES
                   ======================================================== */
                .empty-state { padding: 60px 20px; text-align: center; }
                .empty-state-icon { font-size: 60px; color: #d1d5db; margin-bottom: 16px; }
                .empty-state-title { font-size: 18px; font-weight: 600; color: #374151; margin-bottom: 6px; }
                .empty-state-description { color: #6b7280; font-size: 13px; max-width: 380px; margin: 0 auto 20px; }
                .loading-state { padding: 60px 20px; text-align: center; }
                .spinner-container { display: inline-block; position: relative; width: 70px; height: 70px; }
                .spinner-ring { position: absolute; width: 100%; height: 100%; border: 4px solid #f3f4f6; border-top-color: #667eea; border-radius: 50%; animation: spin 1s linear infinite; }
                @keyframes spin { to { transform: rotate(360deg); } }

                /* ========================================================
                   SOFT BUTTONS
                   ======================================================== */
                .btn-soft-info    { color: #0dcaf0; background-color: rgba(13,202,240,.1); border-color: transparent; transition: all .2s ease; }
                .btn-soft-info:hover    { color: #fff; background-color: #0dcaf0; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(13,202,240,.2); }
                .btn-soft-warning { color: #ffc107; background-color: rgba(255,193,7,.1);  border-color: transparent; transition: all .2s ease; }
                .btn-soft-warning:hover { color: #fff; background-color: #ffc107; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(255,193,7,.2); }
                .btn-soft-danger  { color: #dc3545; background-color: rgba(220,53,69,.1);  border-color: transparent; transition: all .2s ease; }
                .btn-soft-danger:hover  { color: #fff; background-color: #dc3545; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(220,53,69,.2); }
                .btn-soft-primary { color: #0d6efd; background-color: rgba(13,110,253,.1); border-color: transparent; transition: all .2s ease; }
                .btn-soft-primary:hover { color: #fff; background-color: #0d6efd; transform: translateY(-2px); }

                /* ========================================================
                   VIEW TOGGLE BUTTONS
                   ======================================================== */
                .btn-group-toggle .btn { border-radius: 10px; padding: 9px 18px; font-weight: 600; font-size: 13px; transition: all .3s ease; }
                .btn-group-toggle .btn.active { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); border-color: #667eea; color: white; box-shadow: 0 4px 12px rgba(102,126,234,.3); }

                /* ========================================================
                   PRIMARY GRADIENT BUTTON
                   ======================================================== */
                .btn-primary-gradient { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); border: none; color: white; padding: 10px 22px; border-radius: 11px; font-weight: 600; font-size: 13px; transition: all .3s ease; }
                .btn-primary-gradient:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(102,126,234,.3); color: white; }

                /* ========================================================
                   MODAL HEADERS
                   ======================================================== */
                .modal-header-gradient { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color: white; padding: 22px 28px; border: none; position: relative; overflow: hidden; }
                .modal-header-gradient::before { content: ''; position: absolute; top: -50px; right: -50px; width: 160px; height: 160px; background: rgba(255,255,255,.06); border-radius: 50%; }
                .modal-header-gradient .modal-title { font-weight: 700; font-size: 16px; position: relative; }
                .modal-header-gradient .btn-close { filter: brightness(0) invert(1); opacity: .8; position: relative; }
                .modal-header-gradient .btn-close:hover { opacity: 1; }

                /* ========================================================
                   PROGRESS STEPS
                   ======================================================== */
                .progress-steps { display: flex; justify-content: space-between; position: relative; margin-bottom: 28px; }
                .progress-steps::before { content: ''; position: absolute; top: 50%; left: 0; right: 0; height: 2px; background: #e9ecef; transform: translateY(-50%); z-index: 1; }
                .progress-steps .step { width: 38px; height: 38px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; color: #6b7280; position: relative; z-index: 2; border: 2px solid #e9ecef; }
                .progress-steps .step.active { background: #405189; color: white; border-color: #405189; }

                /* ========================================================
                   INFO CARD (view modal)
                   ======================================================== */
                .info-card { border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; margin-bottom: 14px; }
                .info-card-header { background: #f8fafc; padding: 11px 16px; border-bottom: 1px solid #e5e7eb; }
                .info-card-header h6 { margin: 0; font-size: 13px; font-weight: 700; }
                .info-card-body { padding: 12px 16px; }
                .info-card-body .table th { font-size: 12px; color: #6b7280; font-weight: 600; width: 42%; padding: 5px 0; }
                .info-card-body .table td { font-size: 13px; padding: 5px 0; }

                /* ========================================================
                   DRAG & DROP COLUMNS (SORTABLE FIX)
                   ======================================================== */
                #columnsContainer {
                    display: flex !important;
                    flex-wrap: wrap !important;
                    gap: 8px !important;
                    min-height: 52px;
                    padding: 10px;
                    background: #f8fafc;
                    border: 1.5px solid #e2e8f0;
                    border-radius: 12px;
                    list-style: none;
                }
                .draggable-item {
                    display: inline-flex !important;
                    align-items: center;
                    gap: 6px;
                    background: #fff;
                    border: 1.5px solid #e2e8f0 !important;
                    border-radius: 9px !important;
                    padding: 7px 10px !important;
                    cursor: default;
                    user-select: none;
                    white-space: nowrap;
                    transition: box-shadow .15s;
                    margin: 0 !important;
                    /* override the col-md-4 wrapping that broke drag */
                    width: auto !important;
                    flex: none !important;
                }
                .draggable-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,.08); }
                .drag-handle { cursor: grab !important; color: #9ca3af; padding: 2px 5px; border-radius: 5px; display: inline-flex; align-items: center; font-size: 15px; transition: color .15s, background .15s; }
                .drag-handle:hover { color: #667eea; background: rgba(102,126,234,.1); }
                .drag-handle:active { cursor: grabbing !important; }
                .sortable-ghost  { opacity: .4; background: #e0f2fe !important; border: 2px dashed #667eea !important; }
                .sortable-chosen { box-shadow: 0 8px 24px rgba(0,0,0,.18) !important; transform: scale(1.02); border-color: #667eea !important; z-index: 1000; }
                .sortable-drag   { opacity: .85; }
                .order-badge { font-size: 10px; padding: 1px 5px; border-radius: 8px; background: #667eea; color: #fff; margin-left: 4px; }

                /* ========================================================
                   IMAGE ZOOM MODAL
                   ======================================================== */
                .img-zoom-modal .modal-content { background: transparent; border: none; box-shadow: none; }
                .img-zoom-modal .modal-dialog  { max-width: 90vw; margin: 1.5rem auto; }
                .img-zoom-modal .modal-body    { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 80vh; padding: 20px; }
                .img-zoomed { max-width: 90vw; max-height: 72vh; border-radius: 16px; border: 4px solid #fff; box-shadow: 0 24px 64px rgba(0,0,0,.4); object-fit: contain; cursor: pointer; animation: zoomInAnim .25s ease; }
                @keyframes zoomInAnim { from { opacity: 0; transform: scale(.85); } to { opacity: 1; transform: scale(1); } }
                .img-zoom-modal .btn-close { position: fixed; top: 18px; right: 26px; background: rgba(0,0,0,.7); border-radius: 50%; padding: 12px; filter: brightness(0) invert(1); opacity: 1; z-index: 9999; }
                .img-zoom-modal .btn-close:hover { background: rgba(0,0,0,.9); transform: scale(1.1); }
                .zoom-name { color: #fff; margin-top: 16px; font-size: 17px; font-weight: 700; background: rgba(0,0,0,.5); padding: 7px 22px; border-radius: 40px; }
                .zoom-detail { color: rgba(255,255,255,.78); margin-top: 6px; font-size: 13px; text-align: center; }

                /* ========================================================
                   MISC
                   ======================================================== */
                .dropdown-menu { border: none; box-shadow: 0 10px 40px rgba(0,0,0,.08); border-radius: 12px; padding: 8px; }
                .dropdown-item { border-radius: 8px; padding: 8px 14px; font-size: 13px; transition: all .2s ease; }
                .dropdown-item:hover { background-color: #f8f9fa; }
                .bg-soft-primary { background-color: rgba(67,97,238,.1); color: #4361ee; }
                .bg-soft-success { background-color: rgba(40,167,69,.1);  color: #28a745; }
                .avatar-xl { width: 80px; height: 80px; }
                .avatar-title { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; }
                @media(max-width:768px) { .sm-stat-value { font-size: 22px; } }
            </style>

            <!-- ============================================================
                 HERO
                 ============================================================ -->
            <div class="sm-hero">
                <h1><i class="ri-group-line me-2"></i>Student Management</h1>
                <p>Manage records, registrations, status and term enrolments from one place.</p>
            </div>

            <!-- ============================================================
                 STAT CARDS — ROW 1
                 ============================================================ -->
            <div class="row mb-2">
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-primary">
                        <div class="card-body">
                            <div class="stats-icon"><i class="fas fa-users"></i></div>
                            <div class="stats-content">
                                <span class="stats-label">Total Students</span>
                                <span class="stats-value">{{ $total_population }}</span>
                                <span class="stats-change positive"><i class="fas fa-arrow-up"></i>12% from last term</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-success">
                        <div class="card-body">
                            <div class="stats-icon"><i class="fas fa-user-graduate"></i></div>
                            <div class="stats-content">
                                <span class="stats-label">Active Students</span>
                                <span class="stats-value">{{ $student_status_counts['Active'] ?? 0 }}</span>
                                <span class="stats-change positive"><i class="fas fa-arrow-up"></i>8% from last term</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-warning">
                        <div class="card-body">
                            <div class="stats-icon"><i class="fas fa-user-plus"></i></div>
                            <div class="stats-content">
                                <span class="stats-label">New Admissions</span>
                                <span class="stats-value">{{ $status_counts['New Student'] ?? 0 }}</span>
                                <span class="stats-change positive"><i class="fas fa-arrow-up"></i>15% from last term</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-purple">
                        <div class="card-body">
                            <div class="stats-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                            <div class="stats-content">
                                <span class="stats-label">Staff Count</span>
                                <span class="stats-value">{{ $staff_count }}</span>
                                <span class="stats-change positive"><i class="fas fa-arrow-up"></i>5% from last term</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================
                 STAT CARDS — ROW 2
                 ============================================================ -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-info">
                        <div class="card-body">
                            <div class="stats-icon"><i class="fas fa-mars"></i></div>
                            <div class="stats-content">
                                <span class="stats-label">Male Students</span>
                                <span class="stats-value">{{ $gender_counts['Male'] ?? 0 }}</span>
                                <span class="stats-change">{{ $total_population > 0 ? number_format(($gender_counts['Male'] / $total_population) * 100, 1) : 0 }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-pink">
                        <div class="card-body">
                            <div class="stats-icon"><i class="fas fa-venus"></i></div>
                            <div class="stats-content">
                                <span class="stats-label">Female Students</span>
                                <span class="stats-value">{{ $gender_counts['Female'] ?? 0 }}</span>
                                <span class="stats-change">{{ $total_population > 0 ? number_format(($gender_counts['Female'] / $total_population) * 100, 1) : 0 }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-teal">
                        <div class="card-body">
                            <div class="stats-icon"><i class="fas fa-cross"></i></div>
                            <div class="stats-content">
                                <span class="stats-label">Christians</span>
                                <span class="stats-value">{{ $religion_counts['Christianity'] ?? 0 }}</span>
                                <span class="stats-change">{{ $total_population > 0 ? number_format(($religion_counts['Christianity'] / $total_population) * 100, 1) : 0 }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats-card stats-warning">
                        <div class="card-body">
                            <div class="stats-icon"><i class="fas fa-moon"></i></div>
                            <div class="stats-content">
                                <span class="stats-label">Muslims</span>
                                <span class="stats-value">{{ $religion_counts['Islam'] ?? 0 }}</span>
                                <span class="stats-change">{{ $total_population > 0 ? number_format(($religion_counts['Islam'] / $total_population) * 100, 1) : 0 }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================
                 ALERTS
                 ============================================================ -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i><strong>Validation Error!</strong>
                    <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif

            <!-- ============================================================
                 MAIN PANEL
                 ============================================================ -->
            <div class="data-table-container">

                <!-- Panel Header -->
                <div class="card-header d-flex align-items-center justify-content-between py-3 px-4 border-bottom flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="checkAll">
                            <label class="form-check-label" for="checkAll"></label>
                        </div>
                        <h5 class="mb-0 fw-bold">Student Records</h5>
                        <span class="badge bg-primary bg-gradient rounded-pill" id="totalStudents">0</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <!-- View Toggle -->
                        <div class="btn-group btn-group-toggle" role="group">
                            <button type="button" class="btn btn-outline-primary active" id="tableViewBtn">
                                <i class="fas fa-table me-1"></i>Table
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="cardViewBtn">
                                <i class="fas fa-th-large me-1"></i>Cards
                            </button>
                        </div>
                        <!-- Bulk Actions -->
                        @can('Delete student')
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" id="bulkActionsDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false" disabled>
                                <i class="fas fa-cog me-1"></i>Actions
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="bulkActionsDropdown">
                                <li><a class="dropdown-item text-danger" href="javascript:void(0);" id="deleteMultipleBtn"><i class="fas fa-trash me-2"></i>Delete Selected</a></li>
                                <li><a class="dropdown-item text-primary" href="javascript:void(0);" id="updateCurrentTermBtn"><i class="fas fa-calendar-alt me-2"></i>Update Current Term</a></li>
                            </ul>
                        </div>
                        @endcan
                        @can('Create student')
                        <button type="button" class="btn btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                            <i class="fas fa-user-plus me-1"></i>Add Student
                        </button>
                        @endcan
                        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#printStudentReportModal">
                            <i class="fas fa-file-export me-1"></i>Export
                        </button>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div class="filter-bar">
                    <div class="row g-2">
                        <div class="col-md-3 col-sm-6">
                            <div class="search-box">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" class="form-control" id="search-input" placeholder="Search name or admission…">
                                <button type="button" class="clear-search" id="clear-search" title="Clear search"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <select class="form-control" id="schoolclass-filter">
                                <option value="all">All Classes</option>
                                @foreach($schoolclasses as $class)
                                    <option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <select class="form-control" id="term-filter">
                                <option value="all">All Terms</option>
                                @foreach($schoolterms as $term)
                                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <select class="form-control" id="session-filter">
                                <option value="all">All Sessions</option>
                                @foreach($schoolsessions as $session)
                                    <option value="{{ $session->id }}">{{ $session->session ?? $session->name ?? 'Session '.$session->id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 col-6">
                            <button type="button" class="btn btn-primary w-100" id="filterBtn" style="height:44px;border-radius:10px;">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                        </div>
                        <div class="col-md-1 col-6">
                            <button type="button" class="btn btn-outline-secondary w-100" id="resetFiltersBtn" style="height:44px;border-radius:10px;" title="Reset filters">
                                <i class="fas fa-redo-alt"></i>
                            </button>
                        </div>
                        <div class="col-md-1 col-6">
                            <button type="button" class="btn btn-warning w-100" id="bulkStatusBtn" title="Bulk update status" style="height:44px;border-radius:10px;">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        <div class="col-md-1 col-6">
                            <button type="button" class="btn btn-info text-white w-100" id="manageTermBtn" title="Manage term" style="height:44px;border-radius:10px;">
                                <i class="fas fa-calendar-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TABLE VIEW -->
                <div id="tableView" class="view-container">
                    <div class="table-responsive">
                        <table class="table data-table" id="studentTable">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="checkAllTable">
                                        </div>
                                    </th>
                                    <th width="58">Photo</th>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Status</th>
                                    <th>Gender</th>
                                    <th>Registered</th>
                                    <th width="200" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="studentTableBody">
                                <!-- populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- CARD VIEW -->
                <div id="cardView" class="view-container d-none p-4">
                    <div class="row" id="studentsCardsContainer">
                        <!-- populated by JS -->
                    </div>
                </div>

                <!-- EMPTY STATE -->
                <div id="emptyState" class="empty-state d-none">
                    <div class="empty-state-icon"><i class="fas fa-users-slash"></i></div>
                    <h5 class="empty-state-title">No Students Found</h5>
                    <p class="empty-state-description">Try adjusting your search or filters to find what you're looking for.</p>
                    <button class="btn btn-primary-gradient" id="resetFromEmptyBtn"><i class="fas fa-redo me-2"></i>Reset Filters</button>
                </div>

                <!-- LOADING STATE -->
                <div id="loadingState" class="loading-state">
                    <div class="spinner-container"><div class="spinner-ring"></div></div>
                    <p class="mt-3 text-muted">Loading students…</p>
                </div>

                <!-- PAGINATION -->
                <div class="pagination-container">
                    <div>
                        <span class="text-muted" style="font-size:13px;">
                            Showing <span class="fw-bold" id="showingCount">0</span> to
                            <span class="fw-bold" id="toCount">0</span> of
                            <span class="fw-bold" id="totalCount">0</span> students
                        </span>
                    </div>
                    <nav>
                        <ul class="pagination mb-0" id="pagination">
                            <li class="page-item" id="prevPageLi">
                                <a class="page-link" href="javascript:void(0);" id="prevPage"><i class="fas fa-chevron-left"></i></a>
                            </li>
                            <li class="page-item" id="nextPageLi">
                                <a class="page-link" href="javascript:void(0);" id="nextPage"><i class="fas fa-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div><!-- /data-table-container -->

        </div><!-- /container-fluid -->

        <!-- ================================================================
             IMAGE ZOOM MODAL
             ================================================================ -->
        <div class="modal fade img-zoom-modal" id="imageZoomModal" tabindex="-1" data-bs-backdrop="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="modal-body text-center">
                        <img id="zoomedStudentImg" src="" alt="Student Photo" class="img-zoomed"
                             onclick="bootstrap.Modal.getInstance(document.getElementById('imageZoomModal')).hide()">
                        <div class="zoom-name" id="zoomedStudentName"></div>
                        <div class="zoom-detail" id="zoomedStudentDetail"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================
             UPDATE CURRENT TERM MODAL
             ================================================================ -->
        <div id="updateCurrentTermModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header modal-header-gradient">
                        <h5 class="modal-title"><i class="fas fa-calendar-alt me-2"></i>Register / Update Term</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form id="updateCurrentTermForm">
                            @csrf
                            <div class="alert alert-info border-0 rounded-3">
                                <i class="fas fa-info-circle me-2"></i>
                                Registering/updating term for <strong><span id="selectedStudentsCount">0</span></strong> selected student(s).
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Class</label>
                                <select class="form-control" name="schoolclassId" required>
                                    <option value="">Select Class</option>
                                    @foreach($schoolclasses as $class)
                                        <option value="{{ $class->id }}">{{ $class->class_display }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Term</label>
                                <select class="form-control" name="termId" required>
                                    <option value="">Select Term</option>
                                    @foreach($schoolterms as $term)
                                        <option value="{{ $term->id }}">{{ $term->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Session</label>
                                <select class="form-control" name="sessionId" required>
                                    <option value="">Select Session</option>
                                    @foreach($schoolsessions as $session)
                                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="is_current" id="is_current" value="1" checked>
                                <label class="form-check-label" for="is_current">Mark as current term for student(s)</label>
                            </div>
                            <small class="text-muted d-block mb-2">If checked, previous current term will be unmarked.</small>
                            <div class="alert alert-warning border-0 rounded-3 mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                If a term already exists for a student in this session it will be updated; otherwise a new registration is created.
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary-gradient" id="confirmUpdateCurrentTerm">
                            <i class="fas fa-save me-1"></i>Register / Update Term
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================
             EXPORT / REPORT MODAL
             ================================================================ -->
        <div id="printStudentReportModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header modal-header-gradient">
                        <h5 class="modal-title"><i class="fas fa-file-export me-2"></i>Generate Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form id="printReportForm">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Class</label>
                                    <select class="form-select" name="class_id">
                                        <option value="">— All Classes —</option>
                                        @foreach($schoolclasses as $class)
                                            <option value="{{ $class->id }}">{{ $class->class_display }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="">— All —</option>
                                        <option value="1">Old Students</option>
                                        <option value="2">New Students</option>
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Term</label>
                                    <select class="form-select" name="term_id">
                                        <option value="">— All Terms —</option>
                                        @foreach($schoolterms as $term)
                                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Session</label>
                                    <select class="form-select" name="session_id">
                                        <option value="">— All Sessions —</option>
                                        @foreach($schoolsessions as $session)
                                            <option value="{{ $session->id }}">{{ $session->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- ── DRAG & DROP COLUMNS ── -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-grip-vertical text-primary me-1"></i>
                                    Select &amp; Arrange Columns
                                    <small class="text-muted fw-normal ms-1">— drag the handle to reorder</small>
                                </label>
                                <div class="alert alert-info border-0 rounded-3 py-2 px-3 mb-2" style="font-size:12px;">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Drag the <i class="fas fa-grip-vertical"></i> handle on any item to reorder. Tick to include in report.
                                </div>
                                <!-- Flat flex container — Sortable works directly on inline children -->
                                <div id="columnsContainer">
                                    <input type="hidden" name="columns_order" id="columnsOrderInput" value="">
                                    @php
                                        $availableColumns = [
                                            'photo'          => 'Photo',
                                            'admissionNo'    => 'Admission No',
                                            'lastname'       => 'Last Name',
                                            'firstname'      => 'First Name',
                                            'othername'      => 'Other Name',
                                            'gender'         => 'Gender',
                                            'dateofbirth'    => 'Date of Birth',
                                            'age'            => 'Age',
                                            'class'          => 'Class / Arm',
                                            'status'         => 'Student Status',
                                            'admission_date' => 'Admission Date',
                                            'phone_number'   => 'Phone Number',
                                            'state'          => 'State of Origin',
                                            'local'          => 'LGA',
                                            'religion'       => 'Religion',
                                            'blood_group'    => 'Blood Group',
                                            'father_name'    => "Father's Name",
                                            'mother_name'    => "Mother's Name",
                                            'guardian_phone' => 'Guardian Phone',
                                            'term'           => 'Term',
                                            'session'        => 'Session',
                                        ];
                                    @endphp
                                    @foreach($availableColumns as $key => $label)
                                        <div class="draggable-item" data-column="{{ $key }}">
                                            <span class="drag-handle" title="Drag to reorder">
                                                <i class="fas fa-grip-vertical"></i>
                                            </span>
                                            <input class="form-check-input column-checkbox m-0" type="checkbox"
                                                   name="columns[]" value="{{ $key }}" id="col_{{ $key }}"
                                                   {{ in_array($key, ['admissionNo','lastname','firstname','class','gender']) ? 'checked' : '' }}>
                                            <label class="form-check-label mb-0" for="col_{{ $key }}" style="font-size:12px;font-weight:600;cursor:pointer;">
                                                {{ $label }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-2 d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllColumnsBtn">Check All</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllColumnsBtn">Uncheck All</button>
                                </div>
                            </div>

                            <!-- Report Header Options -->
                            <div class="card border-0 bg-light rounded-3 mb-4">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3"><i class="ri-file-info-line me-1 text-primary"></i>Report Options</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="include_header" id="includeHeader" checked>
                                                <label class="form-check-label" for="includeHeader">Include School Header</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="include_logo" id="includeLogo" checked>
                                                <label class="form-check-label" for="includeLogo">Include School Logo</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Page Orientation</label>
                                            <select class="form-select form-select-sm" name="orientation" id="orientation">
                                                <option value="portrait">Portrait</option>
                                                <option value="landscape">Landscape</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Export Format -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Export Format</label>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="format" id="format_pdf" value="pdf" checked>
                                        <label class="form-check-label" for="format_pdf"><i class="ri-file-pdf-2-line text-danger me-1"></i>PDF</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="format" id="format_excel" value="excel">
                                        <label class="form-check-label" for="format_excel"><i class="ri-file-excel-2-line text-success me-1"></i>Excel</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview -->
                            <div class="alert alert-info border-0 rounded-3 small mb-0">
                                <i class="ri-information-fill me-1"></i>
                                <strong>Column order preview:</strong>
                                <span id="columnOrderPreview">admissionNo, lastname, firstname, class, gender</span>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success" id="generateReportBtn">
                            <i class="ri-printer-line me-1"></i>Generate &amp; Download
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================
             ADD STUDENT MODAL
             ================================================================ -->
        <div id="addStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header modal-header-gradient">
                        <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Student Registration</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form class="tablelist-form" id="addStudentForm" enctype="multipart/form-data" autocomplete="off" method="POST" action="{{ route('student.store') }}">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="progress-steps mb-4">
                                <div class="step active">1</div>
                                <div class="step">2</div>
                                <div class="step">3</div>
                                <div class="step">4</div>
                            </div>
                            <div class="row g-3">
                                <!-- Academic Details -->
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Academic Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Admission Number Mode <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="admissionMode" id="admissionAuto" value="auto" required onchange="toggleAdmissionInput()"><label class="form-check-label" for="admissionAuto"><i class="fas fa-magic me-1"></i>Auto Generate</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="admissionMode" id="admissionManual" value="manual" required onchange="toggleAdmissionInput()"><label class="form-check-label" for="admissionManual"><i class="fas fa-edit me-1"></i>Manual Entry</label></div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Admission Number <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <select class="form-control" id="admissionYear" name="admissionYear" required onchange="updateAdmissionNumber()">
                                                        @for($year = date('Y'); $year >= date('Y') - 5; $year--)<option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>@endfor
                                                    </select>
                                                    <input type="text" id="admissionNo" name="admissionNo" class="form-control" placeholder="TCC/YYYY/0001" required>
                                                </div>
                                                <small class="text-muted">Format: TCC/YYYY/0001 (e.g., TCC/2024/0871)</small>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Admission Date <span class="text-danger">*</span></label>
                                                <input type="date" id="admissionDate" name="admissionDate" class="form-control" required max="{{ date('Y-m-d') }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Class <span class="text-danger">*</span></label>
                                                <select id="schoolclassid" name="schoolclassid" class="form-control" required>
                                                    <option value="">Select Class</option>
                                                    @foreach($schoolclasses as $class)<option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>@endforeach
                                                </select>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Term <span class="text-danger">*</span></label>
                                                        <select id="termid" name="termid" class="form-control" required>
                                                            <option value="">Select Term</option>
                                                            @foreach($schoolterms as $term)<option value="{{ $term->id }}">{{ $term->name }}</option>@endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                                                        <select id="sessionid" name="sessionid" class="form-control" required>
                                                            <option value="">Select Session</option>
                                                            @foreach($schoolsessions as $session)<option value="{{ $session->id }}">{{ $session->name }}</option>@endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Student Status <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="statusId" id="statusOld" value="1" required><label class="form-check-label" for="statusOld">Old Student</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="statusId" id="statusNew" value="2" required><label class="form-check-label" for="statusNew">New Student</label></div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Activity Status <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="student_status" id="statusActive" value="Active" required><label class="form-check-label" for="statusActive">Active</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="student_status" id="statusInactive" value="Inactive" required><label class="form-check-label" for="statusInactive">Inactive</label></div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Student Category <span class="text-danger">*</span></label>
                                                <select id="student_category" name="student_category" class="form-control" required>
                                                    <option value="">Select Category</option>
                                                    <option value="Day">Day Student</option>
                                                    <option value="Boarding">Boarding Student</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Personal Details -->
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>Personal Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3 text-center">
                                                <div class="upload-area border border-2 border-dashed border-primary rounded p-3">
                                                    <img id="addStudentAvatar" src="https://via.placeholder.com/120x120/667eea/ffffff?text=Photo" alt="Avatar Preview" class="rounded-circle mb-2" style="width:110px;height:110px;object-fit:cover;border:4px solid #667eea;box-shadow:0 4px 8px rgba(0,0,0,.1);" />
                                                    <div>
                                                        <label for="avatar" class="btn btn-outline-primary btn-sm"><i class="fas fa-camera me-1"></i>Choose Photo</label>
                                                        <input type="file" id="avatar" name="avatar" class="d-none" accept=".png,.jpg,.jpeg" onchange="previewImage(this)">
                                                        <div class="form-text mt-1">Max 2MB (PNG, JPG, JPEG)</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-3 mb-3">
                                                    <label class="form-label fw-semibold">Title</label>
                                                    <select id="title" name="title" class="form-control"><option value="">—</option><option value="Master">Master</option><option value="Miss">Miss</option></select>
                                                </div>
                                                <div class="col-9 mb-3">
                                                    <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                                    <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Last name" required>
                                                </div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6 mb-3">
                                                    <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                                    <input type="text" id="firstname" name="firstname" class="form-control" placeholder="First name" required>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label class="form-label fw-semibold">Other Names</label>
                                                    <input type="text" id="othername" name="othername" class="form-control" placeholder="Middle name(s)">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="gender" id="genderMale" value="Male" required><label class="form-check-label" for="genderMale"><i class="fas fa-male text-primary me-1"></i>Male</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="gender" id="genderFemale" value="Female" required><label class="form-check-label" for="genderFemale"><i class="fas fa-female text-danger me-1"></i>Female</label></div>
                                                </div>
                                            </div>
                                            <div class="row g-2 mb-3">
                                                <div class="col-7">
                                                    <label class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label>
                                                    <input type="date" id="addDOB" name="dateofbirth" class="form-control" required onchange="calculateAge(this.value,'addAgeInput')">
                                                </div>
                                                <div class="col-5">
                                                    <label class="form-label fw-semibold">Age <span class="text-danger">*</span></label>
                                                    <input type="number" id="addAgeInput" name="age" class="form-control" readonly required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Place of Birth <span class="text-danger">*</span></label>
                                                <input type="text" id="placeofbirth" name="placeofbirth" class="form-control" placeholder="e.g., Lagos, Nigeria" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Phone Number</label>
                                                <input type="text" id="phone_number" name="phone_number" class="form-control" placeholder="+234 xxx xxx xxxx">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Email</label>
                                                <input type="email" id="email" name="email" class="form-control" placeholder="student@example.com">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Future Ambition <span class="text-danger">*</span></label>
                                                <textarea id="future_ambition" name="future_ambition" class="form-control" rows="2" required></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Permanent Address <span class="text-danger">*</span></label>
                                                <textarea id="permanent_address" name="permanent_address" class="form-control" rows="2" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Additional Info -->
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Additional Information</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3"><label class="form-label fw-semibold">Nationality <span class="text-danger">*</span></label><input type="text" id="nationality" name="nationality" class="form-control" required></div>
                                            <div class="row g-2">
                                                <div class="col-6 mb-3"><label class="form-label fw-semibold">State of Origin <span class="text-danger">*</span></label><select id="addState" name="state" class="form-control" required><option value="">Select State</option></select></div>
                                                <div class="col-6 mb-3"><label class="form-label fw-semibold">Local Government <span class="text-danger">*</span></label><select id="addLocal" name="local" class="form-control" required disabled><option value="">Select LGA</option></select></div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6 mb-3"><label class="form-label fw-semibold">City</label><input type="text" id="city" name="city" class="form-control"></div>
                                                <div class="col-6 mb-3"><label class="form-label fw-semibold">Religion <span class="text-danger">*</span></label><select id="religion" name="religion" class="form-control" required><option value="">Select</option><option value="Christianity">Christianity</option><option value="Islam">Islam</option><option value="Others">Others</option></select></div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6 mb-3"><label class="form-label fw-semibold">Blood Group</label><select id="blood_group" name="blood_group" class="form-control"><option value="">Select</option><option>A+</option><option>A-</option><option>B+</option><option>B-</option><option>AB+</option><option>AB-</option><option>O+</option><option>O-</option></select></div>
                                                <div class="col-6 mb-3"><label class="form-label fw-semibold">Mother Tongue</label><input type="text" id="mother_tongue" name="mother_tongue" class="form-control"></div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6 mb-3"><label class="form-label fw-semibold">NIN Number</label><input type="text" id="nin_number" name="nin_number" class="form-control" maxlength="11"></div>
                                                <div class="col-6 mb-3">
                                                    <label class="form-label fw-semibold">School House <span class="text-danger">*</span></label>
                                                    <select id="school_house" name="schoolhouseid" class="form-control" required>
                                                        <option value="">Select House</option>
                                                        @foreach($schoolhouses as $schoolhouse)<option value="{{ $schoolhouse->id }}">{{ $schoolhouse->house }}</option>@endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Parent & Previous School -->
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm mb-3">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0"><i class="fas fa-users me-2"></i>Parent / Guardian Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3"><label class="form-label fw-semibold">Father's Name</label><input type="text" id="father_name" name="father_name" class="form-control"></div>
                                            <div class="row g-2">
                                                <div class="col-6 mb-3"><label class="form-label fw-semibold">Father's Phone</label><input type="text" id="father_phone" name="father_phone" class="form-control"></div>
                                                <div class="col-6 mb-3"><label class="form-label fw-semibold">Father's Occupation</label><input type="text" id="father_occupation" name="father_occupation" class="form-control"></div>
                                            </div>
                                            <div class="mb-3"><label class="form-label fw-semibold">Father's City</label><input type="text" id="father_city" name="father_city" class="form-control"></div>
                                            <div class="mb-3"><label class="form-label fw-semibold">Mother's Name</label><input type="text" id="mother_name" name="mother_name" class="form-control"></div>
                                            <div class="mb-3"><label class="form-label fw-semibold">Mother's Phone</label><input type="text" id="mother_phone" name="mother_phone" class="form-control"></div>
                                            <div class="mb-3"><label class="form-label fw-semibold">Parent's Email</label><input type="email" id="parent_email" name="parent_email" class="form-control"></div>
                                            <div class="mb-3"><label class="form-label fw-semibold">Parent's Address</label><textarea id="parent_address" name="parent_address" class="form-control" rows="2"></textarea></div>
                                        </div>
                                    </div>
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-secondary text-white">
                                            <h6 class="mb-0"><i class="fas fa-school me-2"></i>Previous School Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3"><label class="form-label fw-semibold">Last School Attended</label><input type="text" id="last_school" name="last_school" class="form-control"></div>
                                            <div class="mb-3"><label class="form-label fw-semibold">Last Class Attended</label><input type="text" id="last_class" name="last_class" class="form-control"></div>
                                            <div class="mb-3"><label class="form-label fw-semibold">Reason for Leaving</label><textarea id="reason_for_leaving" name="reason_for_leaving" class="form-control" rows="2"></textarea></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-danger d-none mt-3" id="alert-error-msg"></div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Cancel</button>
                            <button type="submit" class="btn btn-primary" id="add-btn"><i class="fas fa-save me-1"></i>Register Student</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ================================================================
             EDIT STUDENT MODAL
             ================================================================ -->
        <div id="editStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header modal-header-gradient">
                        <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form class="tablelist-form" id="editStudentForm" enctype="multipart/form-data" autocomplete="off" method="POST"
                          action="{{ route('student.update', ':id') }}"
                          data-base-action="{{ route('student.update', ':id') }}">
                        @csrf
                        @method('PATCH')
                        <div class="modal-body p-4">
                            <input type="hidden" id="editStudentId" name="id">
                            <div class="progress-steps mb-4">
                                <div class="step">1</div><div class="step">2</div><div class="step">3</div><div class="step">4</div>
                            </div>
                            <div class="row g-3">
                                <!-- Academic Details -->
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Academic Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Admission Number Mode <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="admissionMode" id="editAdmissionAuto" value="auto" required onchange="toggleAdmissionInput('edit')"><label class="form-check-label" for="editAdmissionAuto"><i class="fas fa-magic me-1"></i>Auto Generate</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="admissionMode" id="editAdmissionManual" value="manual" required onchange="toggleAdmissionInput('edit')"><label class="form-check-label" for="editAdmissionManual"><i class="fas fa-edit me-1"></i>Manual Entry</label></div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Admission Number <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <select class="form-control" id="editAdmissionYear" name="admissionYear" required onchange="updateAdmissionNumber('edit')">
                                                        @for($year = date('Y'); $year >= date('Y') - 5; $year--)<option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>@endfor
                                                    </select>
                                                    <input type="text" id="editAdmissionNo" name="admissionNo" class="form-control" placeholder="TCC/YYYY/0001" required>
                                                </div>
                                                <small class="text-muted">Format: TCC/YYYY/0001</small>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Admission Date <span class="text-danger">*</span></label>
                                                <input type="date" id="editAdmissionDate" name="admissionDate" class="form-control" required max="{{ date('Y-m-d') }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Class <span class="text-danger">*</span></label>
                                                <select id="editSchoolclassid" name="schoolclassid" class="form-control" required>
                                                    <option value="">Select Class</option>
                                                    @foreach($schoolclasses as $class)<option value="{{ $class->id }}">{{ $class->schoolclass }} - {{ $class->arm }}</option>@endforeach
                                                </select>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Term <span class="text-danger">*</span></label>
                                                        <select id="editTermid" name="termid" class="form-control" required>
                                                            <option value="">Select Term</option>
                                                            @foreach($schoolterms as $term)<option value="{{ $term->id }}">{{ $term->name }}</option>@endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                                                        <select id="editSessionid" name="sessionid" class="form-control" required>
                                                            <option value="">Select Session</option>
                                                            @foreach($schoolsessions as $session)<option value="{{ $session->id }}">{{ $session->name }}</option>@endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Student Status <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="statusId" id="editStatusOld" value="1" required><label class="form-check-label" for="editStatusOld">Old Student</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="statusId" id="editStatusNew" value="2" required><label class="form-check-label" for="editStatusNew">New Student</label></div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Activity Status <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="student_status" id="editStatusActive" value="Active" required><label class="form-check-label" for="editStatusActive">Active</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="student_status" id="editStatusInactive" value="Inactive" required><label class="form-check-label" for="editStatusInactive">Inactive</label></div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Student Category <span class="text-danger">*</span></label>
                                                <select id="editStudentCategory" name="student_category" class="form-control" required>
                                                    <option value="">Select Category</option>
                                                    <option value="Day">Day Student</option>
                                                    <option value="Boarding">Boarding Student</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Personal Details -->
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>Personal Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3 text-center">
                                                <div class="upload-area border border-2 border-dashed border-info rounded p-3">
                                                    <img id="editStudentAvatar" src="{{ asset('theme/layouts/assets/media/avatars/blank.png') }}" alt="Avatar Preview" class="rounded-circle mb-2" style="width:110px;height:110px;object-fit:cover;border:4px solid #0dcaf0;box-shadow:0 4px 8px rgba(0,0,0,.1);cursor:pointer;" onclick="document.getElementById('editAvatar').click()" />
                                                    <div>
                                                        <label for="editAvatar" class="btn btn-outline-info btn-sm"><i class="fas fa-camera me-1"></i>Choose Photo</label>
                                                        <input type="file" id="editAvatar" name="avatar" class="d-none" accept=".png,.jpg,.jpeg" onchange="previewImage(this,'editStudentAvatar')">
                                                        <div class="form-text mt-1">Max 2MB (PNG, JPG, JPEG)</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-3 mb-3">
                                                    <label class="form-label fw-semibold">Title</label>
                                                    <select id="editTitle" name="title" class="form-control"><option value="">—</option><option value="Master">Master</option><option value="Miss">Miss</option></select>
                                                </div>
                                                <div class="col-9 mb-3">
                                                    <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                                    <input type="text" id="editLastname" name="lastname" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                                    <input type="text" id="editFirstname" name="firstname" class="form-control" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label fw-semibold">Other Names</label>
                                                    <input type="text" id="editOthername" name="othername" class="form-control">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="gender" id="editGenderMale" value="Male" required><label class="form-check-label" for="editGenderMale"><i class="fas fa-male text-primary me-1"></i>Male</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="radio" name="gender" id="editGenderFemale" value="Female" required><label class="form-check-label" for="editGenderFemale"><i class="fas fa-female text-danger me-1"></i>Female</label></div>
                                                </div>
                                            </div>
                                            <div class="row g-2 mb-3">
                                                <div class="col-7">
                                                    <label class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label>
                                                    <input type="date" id="editDOB" name="dateofbirth" class="form-control" required onchange="calculateAge(this.value,'editAgeInput')">
                                                </div>
                                                <div class="col-5">
                                                    <label class="form-label fw-semibold">Age <span class="text-danger">*</span></label>
                                                    <input type="number" id="editAgeInput" name="age" class="form-control" readonly required>
                                                </div>
                                            </div>
                                            <div class="mb-3"><label class="form-label fw-semibold">Place of Birth <span class="text-danger">*</span></label><input type="text" id="editPlaceofbirth" name="placeofbirth" class="form-control" required></div>
                                            <div class="mb-3"><label class="form-label fw-semibold">Phone Number</label><input type="text" id="editPhoneNumber" name="phone_number" class="form-control"></div>
                                            <div class="mb-3"><label class="form-label fw-semibold">Email</label><input type="email" id="editEmail" name="email" class="form-control"></div>
                                            <div class="mb-3"><label class="form-label fw-semibold">Future Ambition <span class="text-danger">*</span></label><textarea id="editFutureAmbition" name="future_ambition" class="form-control" rows="2" required></textarea></div>
                                            <div class="mb-3"><label class="form-label fw-semibold">Permanent Address <span class="text-danger">*</span></label><textarea id="editPermanentAddress" name="permanent_address" class="form-control" rows="2" required></textarea></div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Additional Info -->
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Additional Information</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3"><label class="form-label fw-semibold">Nationality <span class="text-danger">*</span></label><input type="text" id="editNationality" name="nationality" class="form-control" required></div>
                                            <div class="row g-2">
                                                <div class="col-6 mb-3"><label class="form-label fw-semibold">State of Origin <span class="text-danger">*</span></label><select id="editState" name="state" class="form-control" required><option value="">Select State</option></select></div>
                                                <div class="col-6 mb-3"><label class="form-label fw-semibold">Local Government <span class="text-danger">*</span></label><select id="editLocal" name="local" class="form-control" required disabled><option value="">Select LGA</option></select></div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6 mb-3"><label class="form-label fw-semibold">City</label><input type="text" id="editCity" name="city" class="form-control"></div>
                                                <div class="col-6 mb-3"><label class="form-label fw-semibold">Religion <span class="text-danger">*</span></label><select id="editReligion" name="religion" class="form-control" required><option value="">Select</option><option value="Christianity">Christianity</option><option value="Islam">Islam</option><option value="Others">Others</option></select></div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6 mb-3"><label class="form-label fw-semibold">Blood Group</label><select id="editBloodGroup" name="blood_group" class="form-control"><option value="">Select</option><option>A+</option><option>A-</option><option>B+</option><option>B-</option><option>AB+</option><option>AB-</option><option>O+</option><option>O-</option></select></div>
                                                <div class="col-6 mb-3"><label class="form-label fw-semibold">Mother Tongue</label><input type="text" id="editMotherTongue" name="mother_tongue" class="form-control"></div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6 mb-3"><label class="form-label fw-semibold">NIN Number</label><input type="text" id="editNinNumber" name="nin_number" class="form-control" maxlength="11"></div>
                                                <div class="col-6 mb-3">
                                                    <label class="form-label fw-semibold">School House <span class="text-danger">*</span></label>
                                                    <select id="editSchoolHouse" name="schoolhouseid" class="form-control" required>
                                                        <option value="">Select House</option>
                                                        @foreach($schoolhouses as $schoolhouse)<option value="{{ $schoolhouse->id }}">{{ $schoolhouse->house }}</option>@endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Parent & Previous School -->
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm mb-3">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0"><i class="fas fa-users me-2"></i>Parent / Guardian Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3"><label class="form-label fw-semibold">Father's Name</label><input type="text" id="editFatherName" name="father_name" class="form-control"></div>
                                            <div class="row g-2">
                                                <div class="col-6 mb-3"><label class="form-label fw-semibold">Father's Phone</label><input type="text" id="editFatherPhone" name="father_phone" class="form-control"></div>
                                                <div class="col-6 mb-3"><label class="form-label fw-semibold">Father's Occupation</label><input type="text" id="editFatherOccupation" name="father_occupation" class="form-control"></div>
                                            </div>
                                            <div class="mb-3"><label class="form-label fw-semibold">Father's City</label><input type="text" id="editFatherCity" name="father_city" class="form-control"></div>
                                            <div class="mb-3"><label class="form-label fw-semibold">Mother's Name</label><input type="text" id="editMotherName" name="mother_name" class="form-control"></div>
                                            <div class="mb-3"><label class="form-label fw-semibold">Mother's Phone</label><input type="text" id="editMotherPhone" name="mother_phone" class="form-control"></div>
                                            <div class="mb-3"><label class="form-label fw-semibold">Parent's Email</label><input type="email" id="editParentEmail" name="parent_email" class="form-control"></div>
                                            <div class="mb-3"><label class="form-label fw-semibold">Parent's Address</label><textarea id="editParentAddress" name="parent_address" class="form-control" rows="2"></textarea></div>
                                        </div>
                                    </div>
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-secondary text-white">
                                            <h6 class="mb-0"><i class="fas fa-school me-2"></i>Previous School Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3"><label class="form-label fw-semibold">Last School Attended</label><input type="text" id="editLastSchool" name="last_school" class="form-control"></div>
                                            <div class="mb-3"><label class="form-label fw-semibold">Last Class Attended</label><input type="text" id="editLastClass" name="last_class" class="form-control"></div>
                                            <div class="mb-3"><label class="form-label fw-semibold">Reason for Leaving</label><textarea id="editReasonForLeaving" name="reason_for_leaving" class="form-control" rows="2"></textarea></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-danger d-none mt-3" id="edit-alert-error-msg"></div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Cancel</button>
                            <button type="submit" class="btn btn-primary" id="edit-btn"><i class="fas fa-save me-1"></i>Update Student</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ================================================================
             VIEW STUDENT MODAL
             ================================================================ -->
        <div id="viewStudentModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header modal-header-gradient">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fas fa-graduation-cap fa-xl"></i>
                            <div>
                                <h4 class="modal-title mb-0">Student Profile</h4>
                                <small style="color:rgba(255,255,255,.7)">Complete student information and records</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <!-- Profile Header -->
                        <div class="bg-light p-4 border-bottom">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="position-relative">
                                        <img id="viewStudentPhoto"
                                             src="{{ asset('theme/layouts/assets/media/avatars/blank.png') }}"
                                             alt="Student Photo"
                                             class="rounded-circle border border-4 border-white shadow"
                                             style="width:110px;height:110px;object-fit:cover;cursor:pointer;"
                                             onclick="openZoomFromViewPhoto(this)">
                                        <span class="position-absolute bottom-0 end-0 rounded-circle border border-2 border-white"
                                              id="studentStatusIndicator"
                                              style="width:18px;height:18px;background:#16a34a;display:block;"></span>
                                    </div>
                                </div>
                                <div class="col">
                                    <h2 class="fw-bold mb-2" id="viewFullName" style="color:var(--sm-primary,#1e3a5f)">—</h2>
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <span class="badge bg-primary bg-gradient px-3 py-2"><i class="fas fa-id-card me-1"></i><span id="viewAdmissionNumber">—</span></span>
                                        <span class="badge bg-info bg-gradient px-3 py-2" id="viewClassBadge"><i class="fas fa-school me-1"></i><span id="viewClassDisplay">—</span></span>
                                        <span class="badge px-3 py-2" id="viewStudentTypeBadge" style="background:#fef3c7;color:#92400e;"><i class="fas fa-user-tag me-1"></i><span id="viewStudentType">—</span></span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-4 text-muted" style="font-size:13px;">
                                        <div><i class="fas fa-calendar-alt me-1"></i>Admitted: <span id="viewAdmittedDate">—</span></div>
                                        <div><i class="fas fa-venus-mars me-1"></i><span id="viewGenderText">—</span></div>
                                        <div><i class="fas fa-birthday-cake me-1"></i>Age: <span id="viewAge">—</span> years</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Tabs -->
                        <div class="px-4 pt-3">
                            <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#personalInfo" role="tab"><i class="fas fa-user-circle me-1"></i>Personal Details</a></li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#academicInfo" role="tab"><i class="fas fa-graduation-cap me-1"></i>Academic Info</a></li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#familyInfo" role="tab"><i class="fas fa-users me-1"></i>Family &amp; Guardian</a></li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#additionalInfo" role="tab"><i class="fas fa-info-circle me-1"></i>Additional Info</a></li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#termHistory" role="tab"><i class="fas fa-history me-1"></i>Term History</a></li>
                            </ul>
                        </div>
                        <!-- Tab Content -->
                        <div class="tab-content p-4">
                            <!-- 1. Personal -->
                            <div class="tab-pane fade show active" id="personalInfo" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <div class="info-card-header"><h6><i class="fas fa-id-badge me-2 text-primary"></i>Basic Information</h6></div>
                                            <div class="info-card-body">
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr><th>Full Name:</th><td class="fw-semibold" id="viewFullNameDetail">—</td></tr>
                                                    <tr><th>Title:</th><td id="viewTitle">—</td></tr>
                                                    <tr><th>Date of Birth:</th><td><span id="viewDOB">—</span> (<span id="viewAgeDetail">—</span> years)</td></tr>
                                                    <tr><th>Place of Birth:</th><td id="viewPlaceOfBirth">—</td></tr>
                                                    <tr><th>Gender:</th><td id="viewGenderDetail">—</td></tr>
                                                    <tr><th>Blood Group:</th><td id="viewBloodGroupDetail">—</td></tr>
                                                    <tr><th>Religion:</th><td id="viewReligionDetail">—</td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <div class="info-card-header"><h6><i class="fas fa-address-card me-2 text-primary"></i>Contact Information</h6></div>
                                            <div class="info-card-body">
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr><th>Phone:</th><td><i class="fas fa-phone-alt me-1 text-muted"></i><span id="viewPhoneNumber">—</span></td></tr>
                                                    <tr><th>Email:</th><td><i class="fas fa-envelope me-1 text-muted"></i><span id="viewEmailAddress">—</span></td></tr>
                                                    <tr><th>Address:</th><td id="viewPermanentAddress">—</td></tr>
                                                    <tr><th>City:</th><td id="viewCity">—</td></tr>
                                                    <tr><th>State of Origin:</th><td id="viewStateOrigin">—</td></tr>
                                                    <tr><th>LGA:</th><td id="viewLGA">—</td></tr>
                                                    <tr><th>Nationality:</th><td id="viewNationality">—</td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <div class="info-card">
                                            <div class="info-card-header"><h6><i class="fas fa-rocket me-2 text-primary"></i>Future Ambition</h6></div>
                                            <div class="info-card-body"><p class="mb-0 fst-italic" id="viewFutureAmbition">—</p></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 2. Academic -->
                            <div class="tab-pane fade" id="academicInfo" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <div class="info-card-header"><h6><i class="fas fa-graduation-cap me-2 text-success"></i>Current Academic Status</h6></div>
                                            <div class="info-card-body">
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr><th>Admission No:</th><td class="fw-bold text-primary" id="viewAdmissionNo">—</td></tr>
                                                    <tr><th>Admission Date:</th><td id="viewAdmissionDate">—</td></tr>
                                                    <tr><th>Class:</th><td><span class="badge bg-info" id="viewCurrentClass">—</span></td></tr>
                                                    <tr><th>Arm:</th><td id="viewArm">—</td></tr>
                                                    <tr><th>Student Category:</th><td><span class="badge bg-secondary" id="viewStudentCategory">—</span></td></tr>
                                                    <tr><th>Student Status:</th><td id="viewStudentStatus">—</td></tr>
                                                    <tr><th>School House:</th><td id="viewSchoolHouse">—</td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <div class="info-card-header"><h6><i class="fas fa-calendar-alt me-2 text-success"></i>Current Term Information</h6></div>
                                            <div class="info-card-body">
                                                <div id="currentTermAlert" class="mb-3"></div>
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr><th>Current Term:</th><td id="viewCurrentTerm">—</td></tr>
                                                    <tr><th>Current Session:</th><td id="viewCurrentSession">—</td></tr>
                                                    <tr><th>Status in Current Term:</th><td id="viewCurrentTermStatus">—</td></tr>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="info-card">
                                            <div class="info-card-header"><h6><i class="fas fa-school me-2 text-success"></i>Previous School</h6></div>
                                            <div class="info-card-body">
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr><th>Last School:</th><td id="viewLastSchool">—</td></tr>
                                                    <tr><th>Last Class:</th><td id="viewLastClass">—</td></tr>
                                                    <tr><th>Reason for Leaving:</th><td><em id="viewReasonForLeaving">—</em></td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 3. Family -->
                            <div class="tab-pane fade" id="familyInfo" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <div class="info-card-header"><h6><i class="fas fa-user-tie me-2 text-primary"></i>Father's Information <span class="badge bg-primary ms-1" id="fatherStatusBadge"></span></h6></div>
                                            <div class="info-card-body">
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr><th>Full Name:</th><td class="fw-semibold" id="viewFatherFullName">—</td></tr>
                                                    <tr><th>Phone:</th><td><span id="viewFatherPhone">—</span><a href="javascript:void(0)" onclick="callNumber('viewFatherPhone')" class="ms-2 text-success"><i class="fas fa-phone-alt"></i></a></td></tr>
                                                    <tr><th>Occupation:</th><td id="viewFatherOccupation">—</td></tr>
                                                    <tr><th>City:</th><td id="viewFatherCityState">—</td></tr>
                                                    <tr><th>Email:</th><td id="viewFatherEmail">—</td></tr>
                                                    <tr><th>Address:</th><td id="viewFatherAddress">—</td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <div class="info-card-header"><h6><i class="fas fa-user-tie me-2 text-danger"></i>Mother's Information <span class="badge bg-danger ms-1" id="motherStatusBadge"></span></h6></div>
                                            <div class="info-card-body">
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr><th>Full Name:</th><td class="fw-semibold" id="viewMotherFullName">—</td></tr>
                                                    <tr><th>Phone:</th><td><span id="viewMotherPhone">—</span><a href="javascript:void(0)" onclick="callNumber('viewMotherPhone')" class="ms-2 text-success"><i class="fas fa-phone-alt"></i></a></td></tr>
                                                    <tr><th>Occupation:</th><td id="viewMotherOccupation">—</td></tr>
                                                    <tr><th>Email:</th><td id="viewMotherEmail">—</td></tr>
                                                    <tr><th>Address:</th><td id="viewMotherAddress">—</td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <div class="info-card">
                                            <div class="info-card-header"><h6><i class="fas fa-user-shield me-2 text-warning"></i>Emergency Contact / Guardian</h6></div>
                                            <div class="info-card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <table class="table table-borderless table-sm mb-0">
                                                            <tr><th>Guardian Name:</th><td class="fw-semibold" id="viewGuardianName">—</td></tr>
                                                            <tr><th>Phone:</th><td id="viewGuardianPhone">—</td></tr>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <table class="table table-borderless table-sm mb-0">
                                                            <tr><th>Parent's Email:</th><td id="viewParentEmail">—</td></tr>
                                                            <tr><th>Parent's Address:</th><td id="viewParentAddress">—</td></tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 4. Additional -->
                            <div class="tab-pane fade" id="additionalInfo" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <div class="info-card-header"><h6><i class="fas fa-notes-medical me-2 text-info"></i>Medical &amp; Personal</h6></div>
                                            <div class="info-card-body">
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr><th>Blood Group:</th><td id="viewBloodGroupAdditional">—</td></tr>
                                                    <tr><th>NIN Number:</th><td id="viewNIN">—</td></tr>
                                                    <tr><th>Mother Tongue:</th><td id="viewMotherTongue">—</td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 5. Term History -->
                            <div class="tab-pane fade" id="termHistory" role="tabpanel">
                                <div class="info-card">
                                    <div class="info-card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="fas fa-history me-2 text-primary"></i>Term Registration History</h6>
                                        <button class="btn btn-sm btn-outline-primary" onclick="refreshTermHistory()"><i class="fas fa-sync-alt me-1"></i>Refresh</button>
                                    </div>
                                    <div class="info-card-body">
                                        <div id="termHistoryLoading" class="text-center py-4">
                                            <div class="spinner-container"><div class="spinner-ring"></div></div>
                                            <p class="mt-2 text-muted">Loading term history…</p>
                                        </div>
                                        <div id="termHistoryContent" style="display:none;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <span class="text-muted small" id="studentProfileLastUpdated"></span>
                            <div>
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Close</button>
                                <button type="button" class="btn btn-primary" onclick="editStudentFromView()"><i class="fas fa-edit me-1"></i>Edit Student</button>
                                <button type="button" class="btn btn-success" onclick="printStudentProfile()"><i class="fas fa-print me-1"></i>Print Profile</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /page-content -->
</div><!-- /main-content -->

<!-- ====================================================================
     SCRIPTS
     ==================================================================== -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
// ============================================================================
// STUDENT MANAGEMENT SYSTEM — COMPLETE FIXED VERSION WITH NEW FEATURES
// ============================================================================

(function() {
    'use strict';

    // ============================================================================
    // GLOBAL CONFIGURATION
    // ============================================================================
    const CONFIG = {
        DEFAULT_PER_PAGE: 25,
        PER_PAGE_OPTIONS: [25, 50, 100, 250, 500],
        SEARCH_DEBOUNCE_DELAY: 500,
        MAX_API_RETRIES: 3,
        CACHE_DURATION: 300000,
        LAZY_LOAD_IMAGES: true,
        ENABLE_LOGGING: true
    };

    // ============================================================================
    // STATE MANAGEMENT
    // ============================================================================
    const AppState = {
        pagination: {
            currentPage: 1,
            perPage: CONFIG.DEFAULT_PER_PAGE,
            total: 0,
            lastPage: 1,
            from: 0,
            to: 0,
            data: []
        },
        filters: {
            search: '',
            class: 'all',
            status: 'all',
            gender: 'all',
            session: 'all',
            term: 'all'
        },
        ui: {
            currentView: 'table',
            isLoading: false,
            selectedStudents: new Set(),
            lastUpdated: null
        },
        cache: {
            students: new Map(),
            stats: null,
            classes: null
        },
        bulkStatusFilters: null,
        termFilters: null,
        bulkStatusData: null
    };

    // ============================================================================
    // NIGERIAN STATES AND LGAS
    // ============================================================================
    const NIGERIAN_STATES = [
        { name: "Abia", lgas: ["Aba North","Aba South","Arochukwu","Bende","Ikwuano","Isiala Ngwa North","Isiala Ngwa South","Isuikwuato","Obi Ngwa","Ohafia","Osisioma","Ugwunagbo","Ukwa East","Ukwa West","Umuahia North","Umuahia South","Umu Nneochi"] },
        { name: "Adamawa", lgas: ["Demsa","Fufure","Ganye","Gayuk","Gombi","Grie","Hong","Jada","Lamurde","Madagali","Maiha","Mayo Belwa","Michika","Mubi North","Mubi South","Numan","Shelleng","Song","Toungo","Yola North","Yola South"] },
        { name: "Akwa Ibom", lgas: ["Abak","Eastern Obolo","Eket","Esit Eket","Essien Udim","Etim Ekpo","Etinan","Ibeno","Ibesikpo Asutan","Ibiono-Ibom","Ika","Ikono","Ikot Abasi","Ikot Ekpene","Ini","Itu","Mbo","Mkpat-Enin","Nsit-Atai","Nsit-Ibom","Nsit-Ubium","Obot Akara","Okobo","Onna","Oron","Oruk Anam","Udung-Uko","Ukanafun","Uruan","Urue-Offong/Oruko","Uyo"] },
        { name: "Anambra", lgas: ["Aguata","Anambra East","Anambra West","Anaocha","Awka North","Awka South","Ayamelum","Dunukofia","Ekwusigo","Idemili North","Idemili South","Ihiala","Njikoka","Nnewi North","Nnewi South","Ogbaru","Onitsha North","Onitsha South","Orumba North","Orumba South","Oyi"] },
        { name: "Bauchi", lgas: ["Alkaleri","Bauchi","Bogoro","Damban","Darazo","Dass","Gamawa","Ganjuwa","Giade","Itas/Gadau","Jama'are","Katagum","Kirfi","Misau","Ningi","Shira","Tafawa Balewa","Toro","Warji","Zaki"] },
        { name: "Bayelsa", lgas: ["Brass","Ekeremor","Kolokuma/Opokuma","Nembe","Ogbia","Sagbama","Southern Ijaw","Yenagoa"] },
        { name: "Benue", lgas: ["Ado","Agatu","Apa","Buruku","Gboko","Guma","Gwer East","Gwer West","Katsina-Ala","Konshisha","Kwande","Logo","Makurdi","Obi","Ogbadibo","Ohimini","Oju","Okpokwu","Oturkpo","Tarka","Ukum","Ushongo","Vandeikya"] },
        { name: "Borno", lgas: ["Abadam","Askira/Uba","Bama","Bayo","Biu","Chibok","Damboa","Dikwa","Gubio","Guzamala","Gwoza","Hawul","Jere","Kaga","Kala/Balge","Konduga","Kukawa","Kwaya Kusar","Mafa","Magumeri","Maiduguri","Marte","Mobbar","Monguno","Ngala","Nganzai","Shani"] },
        { name: "Cross River", lgas: ["Abi","Akamkpa","Akpabuyo","Bakassi","Bekwarra","Biase","Boki","Calabar Municipal","Calabar South","Etung","Ikom","Obanliku","Obubra","Obudu","Odukpani","Ogoja","Yakuur","Yala"] },
        { name: "Delta", lgas: ["Aniocha North","Aniocha South","Bomadi","Burutu","Ethiope East","Ethiope West","Ika North East","Ika South","Isoko North","Isoko South","Ndokwa East","Ndokwa West","Okpe","Oshimili North","Oshimili South","Patani","Sapele","Udu","Ughelli North","Ughelli South","Ukwuani","Uvwie","Warri North","Warri South","Warri South West"] },
        { name: "Ebonyi", lgas: ["Abakaliki","Afikpo North","Afikpo South","Ebonyi","Ezza North","Ezza South","Ikwo","Ishielu","Ivo","Izzi","Ohaozara","Ohaukwu","Onicha"] },
        { name: "Edo", lgas: ["Akoko-Edo","Egor","Esan Central","Esan North-East","Esan South-East","Esan West","Etsako Central","Etsako East","Etsako West","Igueben","Ikpoba Okha","Orhionmwon","Oredo","Ovia North-East","Ovia South-West","Owan East","Owan West","Uhunmwonde"] },
        { name: "Ekiti", lgas: ["Ado Ekiti","Efon","Ekiti East","Ekiti South-West","Ekiti West","Emure","Gbonyin","Ido Osi","Ijero","Ikere","Ilejemeje","Irepodun/Ifelodun","Ise/Orun","Moba","Oye"] },
        { name: "Enugu", lgas: ["Aninri","Awgu","Enugu East","Enugu North","Enugu South","Ezeagu","Igbo Etiti","Igbo Eze North","Igbo Eze South","Isi Uzo","Nkanu East","Nkanu West","Nsukka","Oji River","Udenu","Udi","Uzo Uwani"] },
        { name: "FCT", lgas: ["Abaji","Bwari","Gwagwalada","Kuje","Kwali","Municipal Area Council"] },
        { name: "Gombe", lgas: ["Akko","Balanga","Billiri","Dukku","Funakaye","Gombe","Kaltungo","Kwami","Nafada","Shongom","Yamaltu/Deba"] },
        { name: "Imo", lgas: ["Aboh Mbaise","Ahiazu Mbaise","Ehime Mbano","Ezinihitte","Ideato North","Ideato South","Ihitte/Uboma","Ikeduru","Isiala Mbano","Isu","Mbaitoli","Ngor Okpala","Njaba","Nkwerre","Nwangele","Obowo","Oguta","Ohaji/Egbema","Okigwe","Orlu","Orsu","Oru East","Oru West","Owerri Municipal","Owerri North","Owerri West","Unuimo"] },
        { name: "Jigawa", lgas: ["Auyo","Babura","Biriniwa","Birnin Kudu","Buji","Dutse","Gagarawa","Garki","Gumel","Guri","Gwaram","Gwiwa","Hadejia","Jahun","Kafin Hausa","Kazaure","Kiri Kasama","Kiyawa","Kaugama","Maigatari","Malam Madori","Miga","Ringim","Roni","Sule Tankarkar","Taura","Yankwashi"] },
        { name: "Kaduna", lgas: ["Birnin Gwari","Chikun","Giwa","Igabi","Ikara","Jaba","Jema'a","Kachia","Kaduna North","Kaduna South","Kagarko","Kajuru","Kaura","Kauru","Kubau","Kudan","Lere","Makarfi","Sabon Gari","Sanga","Soba","Zangon Kataf","Zaria"] },
        { name: "Kano", lgas: ["Ajingi","Albasu","Bagwai","Bebeji","Bichi","Bunkure","Dala","Dambatta","Dawakin Kudu","Dawakin Tofa","Doguwa","Fagge","Gabasawa","Garko","Garun Mallam","Gaya","Gezawa","Gwale","Gwarzo","Kabo","Kano Municipal","Karaye","Kibiya","Kiru","Kumbotso","Kunchi","Kura","Madobi","Makoda","Minjibir","Nasarawa","Rano","Rimin Gado","Rogo","Shanono","Sumaila","Takai","Tarauni","Tofa","Tsanyawa","Tudun Wada","Ungogo","Warawa","Wudil"] },
        { name: "Katsina", lgas: ["Bakori","Batagarawa","Batsari","Baure","Bindawa","Charanchi","Dan Musa","Dandume","Danja","Daura","Dutsi","Dutsin Ma","Faskari","Funtua","Ingawa","Jibia","Kafur","Kaita","Kankara","Kankia","Katsina","Kurfi","Kusada","Mai'Adua","Malumfashi","Mani","Mashi","Matazu","Musawa","Rimi","Sabuwa","Safana","Sandamu","Zango"] },
        { name: "Kebbi", lgas: ["Aleiro","Arewa Dandi","Argungu","Augie","Bagudo","Birnin Kebbi","Bunza","Dandi","Fakai","Gwandu","Jega","Kalgo","Koko/Besse","Maiyama","Ngaski","Sakaba","Shanga","Suru","Danko/Wasagu","Yauri","Zuru"] },
        { name: "Kogi", lgas: ["Adavi","Ajaokuta","Ankpa","Bassa","Dekina","Ibaji","Idah","Igalamela Odolu","Ijumu","Kabba/Bunu","Kogi","Lokoja","Mopa Muro","Ofu","Ogori/Magongo","Okehi","Okene","Olamaboro","Omala","Yagba East","Yagba West"] },
        { name: "Kwara", lgas: ["Asa","Baruten","Edu","Ekiti","Ifelodun","Ilorin East","Ilorin South","Ilorin West","Irepodun","Isin","Kaiama","Moro","Offa","Oke Ero","Oyun","Pategi"] },
        { name: "Lagos", lgas: ["Agege","Ajeromi-Ifelodun","Alimosho","Amuwo-Odofin","Apapa","Badagry","Epe","Eti Osa","Ibeju-Lekki","Ifako-Ijaiye","Ikeja","Ikorodu","Kosofe","Lagos Island","Lagos Mainland","Mushin","Ojo","Oshodi-Isolo","Shomolu","Surulere"] },
        { name: "Nasarawa", lgas: ["Akwanga","Awe","Doma","Karu","Keana","Keffi","Kokona","Lafia","Nasarawa","Nasarawa Egon","Obi","Toto","Wamba"] },
        { name: "Niger", lgas: ["Agaie","Agwara","Bida","Borgu","Bosso","Chanchaga","Edati","Gbako","Gurara","Katcha","Kontagora","Lapai","Lavun","Magama","Mariga","Mashegu","Mokwa","Moya","Paikoro","Rafi","Rijau","Shiroro","Suleja","Tafa","Wushishi"] },
        { name: "Ogun", lgas: ["Abeokuta North","Abeokuta South","Ado-Odo/Ota","Egbado North","Egbado South","Ewekoro","Ifo","Ijebu East","Ijebu North","Ijebu North East","Ijebu Ode","Ikenne","Imeko Afon","Ipokia","Obafemi Owode","Odeda","Odogbolu","Ogun Waterside","Remo North","Shagamu"] },
        { name: "Ondo", lgas: ["Akoko North-East","Akoko North-West","Akoko South-East","Akoko South-West","Akure North","Akure South","Ese Odo","Idanre","Ifedore","Ilaje","Ile Oluji/Okeigbo","Irele","Odigbo","Okitipupa","Ondo East","Ondo West","Ose","Owo"] },
        { name: "Osun", lgas: ["Aiyedade","Aiyedire","Atakunmosa East","Atakunmosa West","Boluwaduro","Boripe","Ede North","Ede South","Egbedore","Ejigbo","Ife Central","Ife East","Ife North","Ife South","Ifedayo","Ifelodun","Ila","Ilesa East","Ilesa West","Irepodun","Irewole","Isokan","Iwo","Obokun","Odo Otin","Ola Oluwa","Olorunda","Oriade","Orolu","Osogbo"] },
        { name: "Oyo", lgas: ["Afijio","Akinyele","Atiba","Atisbo","Egbeda","Ibadan North","Ibadan North-East","Ibadan North-West","Ibadan South-East","Ibadan South-West","Ibarapa Central","Ibarapa East","Ibarapa North","Ido","Irepo","Iseyin","Itesiwaju","Iwajowa","Kajola","Lagelu","Ogbomosho North","Ogbomosho South","Ogo Oluwa","Olorunsogo","Oluyole","Ona Ara","Orelope","Ori Ire","Oyo East","Oyo West","Saki East","Saki West","Surulere"] },
        { name: "Plateau", lgas: ["Bokkos","Barkin Ladi","Bassa","Jos East","Jos North","Jos South","Kanam","Kanke","Langtang North","Langtang South","Mangu","Mikang","Pankshin","Qua'an Pan","Riyom","Shendam","Wase"] },
        { name: "Rivers", lgas: ["Abua/Odual","Ahoada East","Ahoada West","Akuku-Toru","Andoni","Asari-Toru","Bonny","Degema","Eleme","Emohua","Etche","Gokana","Ikwerre","Khana","Obio/Akpor","Ogba/Egbema/Ndoni","Ogu/Bolo","Okrika","Omuma","Opobo/Nkoro","Oyigbo","Port Harcourt","Tai"] },
        { name: "Sokoto", lgas: ["Binji","Bodinga","Dange Shuni","Gada","Goronyo","Gudu","Gwadabawa","Illela","Isa","Kebbe","Kware","Rabah","Sabon Birni","Shagari","Silame","Sokoto North","Sokoto South","Tambuwal","Tangaza","Tureta","Wamako","Wurno","Yabo"] },
        { name: "Taraba", lgas: ["Ardo Kola","Bali","Donga","Gashaka","Gassol","Ibi","Jalingo","Karim Lamido","Kumi","Lau","Sardauna","Takum","Ussa","Wukari","Yorro","Zing"] },
        { name: "Yobe", lgas: ["Bade","Bursari","Damaturu","Fika","Fune","Geidam","Gujba","Gulani","Jakusko","Karasuwa","Machina","Nangere","Nguru","Potiskum","Tarmuwa","Yunusari","Yusufari"] },
        { name: "Zamfara", lgas: ["Anka","Bakura","Birnin Magaji/Kiyaw","Bukkuyum","Bungudu","Gummi","Gusau","Kaura Namoda","Maradun","Maru","Shinkafi","Talata Mafara","Chafe","Zurmi"] }
    ];

    // ============================================================================
    // UTILITY FUNCTIONS
    // ============================================================================
    const Utils = {
        log: function(message, data, level = 'info') {
            if (!CONFIG.ENABLE_LOGGING) return;
            const ts = new Date().toISOString();
            const fn = level === 'error' ? console.error : console.log;
            data ? fn(`[${ts}] ${message}:`, data) : fn(`[${ts}] ${message}`);
        },
        escapeHtml: function(text) {
            if (!text) return '';
            return text.toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
        },
        formatDate: function(dateString, format = 'short') {
            if (!dateString) return 'N/A';
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return 'N/A';
                return format === 'long'
                    ? date.toLocaleDateString('en-US', {year:'numeric',month:'long',day:'numeric'})
                    : date.toLocaleDateString('en-US', {year:'numeric',month:'short',day:'numeric'});
            } catch { return 'N/A'; }
        },
        calculateAge: function(dateOfBirth) {
            if (!dateOfBirth) return 'N/A';
            try {
                const dob = new Date(dateOfBirth);
                if (isNaN(dob.getTime())) return 'N/A';
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                const m = today.getMonth() - dob.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
                return age;
            } catch { return 'N/A'; }
        },
        getInitials: function(firstName, lastName) {
            const f = firstName && firstName.length > 0 ? firstName.charAt(0).toUpperCase() : '';
            const l = lastName  && lastName.length  > 0 ? lastName.charAt(0).toUpperCase()  : '';
            return (f + l) || 'ST';
        },
        debounce: function(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        },
        showLoading: function() {
            const loadingEl  = document.getElementById('loadingState');
            const tableView  = document.getElementById('tableView');
            const cardView   = document.getElementById('cardView');
            const emptyState = document.getElementById('emptyState');
            if (loadingEl)  loadingEl.classList.remove('d-none');
            if (tableView)  tableView.classList.add('d-none');
            if (cardView)   cardView.classList.add('d-none');
            if (emptyState) emptyState.classList.add('d-none');
            AppState.ui.isLoading = true;
        },
        hideLoading: function() {
            const loadingEl  = document.getElementById('loadingState');
            if (loadingEl)   loadingEl.classList.add('d-none');
            const tableView  = document.getElementById('tableView');
            const cardView   = document.getElementById('cardView');
            const emptyState = document.getElementById('emptyState');
            if (AppState.pagination.data && AppState.pagination.data.length > 0) {
                if (tableView && AppState.ui.currentView === 'table') tableView.classList.remove('d-none');
                if (cardView  && AppState.ui.currentView === 'card')  cardView.classList.remove('d-none');
                if (emptyState) emptyState.classList.add('d-none');
            } else {
                if (tableView)  tableView.classList.add('d-none');
                if (cardView)   cardView.classList.add('d-none');
                if (emptyState) emptyState.classList.remove('d-none');
            }
            AppState.ui.isLoading = false;
        },
        showError: function(message, title = 'Error') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title, text: message, icon: 'error', confirmButtonText: 'OK', customClass: { confirmButton: 'btn btn-primary' } });
            } else { alert(`${title}: ${message}`); }
        },
        showSuccess: function(message, title = 'Success') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title, text: message, icon: 'success', confirmButtonText: 'OK', timer: 2000, timerProgressBar: true });
            } else { alert(`${title}: ${message}`); }
        },
        showConfirm: async function(title, text, confirmText = 'Yes', cancelText = 'Cancel') {
            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({ title, text, icon: 'warning', showCancelButton: true, confirmButtonText: confirmText, cancelButtonText, customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light' } });
                return result.isConfirmed;
            }
            return confirm(`${title}: ${text}`);
        },
        initializeTooltips: function() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
            }
        },
        ensureAxios: function() {
            if (typeof axios === 'undefined') { this.showError('Axios library is missing. Please refresh the page.'); return false; }
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) { this.showError('CSRF token not found. Please refresh the page.'); return false; }
            axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            return true;
        }
    };

    // ============================================================================
    // API SERVICE
    // ============================================================================
    const ApiService = {
        async getStudents(page = 1, perPage = null, filters = null) {
            if (!Utils.ensureAxios()) throw new Error('Axios not available');
            const params = new URLSearchParams();
            params.append('page', page);
            const itemsPerPage = perPage || AppState.pagination.perPage || CONFIG.DEFAULT_PER_PAGE;
            params.append('per_page', itemsPerPage);
            const currentFilters = filters || AppState.filters;
            if (currentFilters.search && currentFilters.search.trim() !== '') params.append('search', currentFilters.search.trim());
            if (currentFilters.class   !== 'all' && currentFilters.class)   params.append('class_id',   currentFilters.class);
            if (currentFilters.status  !== 'all' && currentFilters.status)  params.append('status',      currentFilters.status);
            if (currentFilters.gender  !== 'all' && currentFilters.gender)  params.append('gender',      currentFilters.gender);
            if (currentFilters.session !== 'all' && currentFilters.session) params.append('session_id',  currentFilters.session);
            try {
                const response = await axios.get(`/students/optimized?${params.toString()}`);
                if (response.data.success) return response.data.data;
                throw new Error(response.data.message || 'Failed to fetch students');
            } catch (error) { Utils.log('API Error - getStudents', error, 'error'); throw error; }
        },
        async getStudent(id) {
            if (!Utils.ensureAxios()) throw new Error('Axios not available');
            try {
                const response = await axios.get(`/student/${id}/edit`);
                if (response.data.success && response.data.student) return response.data.student;
                throw new Error(response.data.message || 'Failed to fetch student');
            } catch (error) { Utils.log('API Error - getStudent', error, 'error'); throw error; }
        },
        async deleteStudent(id) {
            if (!Utils.ensureAxios()) throw new Error('Axios not available');
            try { return (await axios.delete(`/student/${id}/destroy`)).data; }
            catch (error) { Utils.log('API Error - deleteStudent', error, 'error'); throw error; }
        },
        async deleteMultipleStudents(ids) {
            if (!Utils.ensureAxios()) throw new Error('Axios not available');
            try { return (await axios.post('/students/destroy-multiple', { ids })).data; }
            catch (error) { Utils.log('API Error - deleteMultipleStudents', error, 'error'); throw error; }
        },
        async getStudentActiveTerm(studentId) {
            if (!Utils.ensureAxios()) throw new Error('Axios not available');
            try { return (await axios.get(`/student-current-term/student/${studentId}/active`)).data; }
            catch (error) { Utils.log('API Error - getStudentActiveTerm', error, 'error'); return { success: false, data: null }; }
        },
        async getStudentAllTerms(studentId) {
            if (!Utils.ensureAxios()) throw new Error('Axios not available');
            try { return (await axios.get(`/student/${studentId}/all-terms`)).data; }
            catch (error) { Utils.log('API Error - getStudentAllTerms', error, 'error'); return { success: false, data: [] }; }
        },
        async updateBulkCurrentTerm(data) {
            if (!Utils.ensureAxios()) throw new Error('Axios not available');
            try { return (await axios.post('/student-current-term/bulk-update', data)).data; }
            catch (error) { Utils.log('API Error - updateBulkCurrentTerm', error, 'error'); throw error; }
        },
        async getStudentsByClassAndSession(classId, sessionId, termId = null) {
            if (!Utils.ensureAxios()) throw new Error('Axios not available');
            try {
                const params = { class_id: classId, session_id: sessionId };
                if (termId) params.term_id = termId;
                return (await axios.get('/students/by-class-session', { params })).data;
            } catch (error) { Utils.log('API Error - getStudentsByClassAndSession', error, 'error'); throw error; }
        },
        async bulkUpdateStatus(data) {
            if (!Utils.ensureAxios()) throw new Error('Axios not available');
            try { return (await axios.post('/students/bulk-update-status', data)).data; }
            catch (error) { Utils.log('API Error - bulkUpdateStatus', error, 'error'); throw error; }
        },
        async getStudentsInTerm(params) {
            if (!Utils.ensureAxios()) throw new Error('Axios not available');
            try { return (await axios.get('/students-in-term', { params })).data; }
            catch (error) { Utils.log('API Error - getStudentsInTerm', error, 'error'); throw error; }
        },
        async removeStudentFromTerm(registrationId) {
            if (!Utils.ensureAxios()) throw new Error('Axios not available');
            try { return (await axios.post('/students/remove-from-term', { registration_id: registrationId })).data; }
            catch (error) { Utils.log('API Error - removeStudentFromTerm', error, 'error'); throw error; }
        },
        async bulkRemoveFromTerm(registrationIds) {
            if (!Utils.ensureAxios()) throw new Error('Axios not available');
            try { return (await axios.post('/students/bulk-remove-from-term', { registration_ids: registrationIds })).data; }
            catch (error) { Utils.log('API Error - bulkRemoveFromTerm', error, 'error'); throw error; }
        }
    };

    // ============================================================================
    // FILTER MANAGER
    // ============================================================================
    const FilterManager = {
        searchTimeout: null,
        initializeFilters: function() {
            const searchInput  = document.getElementById('search-input');
            const classFilter  = document.getElementById('schoolclass-filter');
            const sessionFilter= document.getElementById('session-filter');
            const termFilter   = document.getElementById('term-filter');
            const filterBtn    = document.getElementById('filterBtn');
            const resetBtn     = document.getElementById('resetFiltersBtn');
            const clearSearchBtn = document.getElementById('clear-search');
            const resetFromEmptyBtn = document.getElementById('resetFromEmptyBtn');
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    if (clearSearchBtn) clearSearchBtn.style.display = e.target.value.length > 0 ? 'block' : 'none';
                    if (this.searchTimeout) clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => this.applyFilters(), CONFIG.SEARCH_DEBOUNCE_DELAY);
                });
                searchInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') { clearTimeout(this.searchTimeout); this.applyFilters(); } });
            }
            if (clearSearchBtn) clearSearchBtn.addEventListener('click', () => this.clearSearch());
            if (classFilter)   classFilter.addEventListener('change',  () => this.applyFilters());
            if (termFilter)    termFilter.addEventListener('change',   () => this.applyFilters());
            if (sessionFilter) sessionFilter.addEventListener('change',() => this.applyFilters());
            if (filterBtn)     filterBtn.addEventListener('click',     () => this.applyFilters());
            if (resetBtn)      resetBtn.addEventListener('click',      () => this.resetFilters());
            if (resetFromEmptyBtn) resetFromEmptyBtn.addEventListener('click', () => this.resetFilters());
            if (searchInput && clearSearchBtn) clearSearchBtn.style.display = searchInput.value.length > 0 ? 'block' : 'none';
        },
        clearSearch: function() {
            const searchInput  = document.getElementById('search-input');
            const clearSearchBtn = document.getElementById('clear-search');
            if (searchInput) { searchInput.value = ''; if (clearSearchBtn) clearSearchBtn.style.display = 'none'; }
            if (this.searchTimeout) clearTimeout(this.searchTimeout);
            this.applyFilters();
        },
        applyFilters: function() {
            const searchInput  = document.getElementById('search-input');
            const classFilter  = document.getElementById('schoolclass-filter');
            const sessionFilter= document.getElementById('session-filter');
            const termFilter   = document.getElementById('term-filter');
            AppState.filters = {
                search:  searchInput  ? searchInput.value.trim()  : '',
                class:   classFilter  ? classFilter.value         : 'all',
                session: sessionFilter? sessionFilter.value       : 'all',
                term:    termFilter   ? termFilter.value          : 'all',
                status:  'all',
                gender:  'all'
            };
            AppState.pagination.currentPage = 1;
            StudentManager.fetchStudents();
        },
        resetFilters: function() {
            const searchInput  = document.getElementById('search-input');
            const classFilter  = document.getElementById('schoolclass-filter');
            const sessionFilter= document.getElementById('session-filter');
            const termFilter   = document.getElementById('term-filter');
            const clearSearchBtn = document.getElementById('clear-search');
            if (searchInput)   searchInput.value  = '';
            if (classFilter)   classFilter.value  = 'all';
            if (termFilter)    termFilter.value   = 'all';
            if (sessionFilter) sessionFilter.value= 'all';
            if (clearSearchBtn) clearSearchBtn.style.display = 'none';
            AppState.filters = { search:'', class:'all', status:'all', gender:'all', session:'all', term:'all' };
            AppState.pagination.currentPage = 1;
            StudentManager.fetchStudents();
        }
    };

    // ============================================================================
    // STATE AND LGA MANAGER
    // ============================================================================
    const StateLGAManager = {
        initializeAddStateDropdown: function() {
            const stateSelect = document.getElementById('addState');
            const lgaSelect   = document.getElementById('addLocal');
            if (!stateSelect || !lgaSelect) return;
            stateSelect.innerHTML = '<option value="">Select State</option>';
            lgaSelect.innerHTML = '<option value="">Select LGA</option>';
            lgaSelect.disabled = true;
            NIGERIAN_STATES.forEach(state => {
                const option = document.createElement('option');
                option.value = state.name; option.textContent = state.name;
                stateSelect.appendChild(option);
            });
            stateSelect.addEventListener('change', (e) => {
                const selectedState = e.target.value;
                lgaSelect.innerHTML = '<option value="">Select LGA</option>';
                if (selectedState) {
                    const state = NIGERIAN_STATES.find(s => s.name === selectedState);
                    lgaSelect.disabled = false;
                    if (state) state.lgas.forEach(lga => {
                        const option = document.createElement('option');
                        option.value = lga; option.textContent = lga;
                        lgaSelect.appendChild(option);
                    });
                } else { lgaSelect.disabled = true; }
            });
        },
        initializeEditStateDropdown: function() {
            const stateSelect = document.getElementById('editState');
            const lgaSelect   = document.getElementById('editLocal');
            if (!stateSelect || !lgaSelect) return;
            stateSelect.innerHTML = '<option value="">Select State</option>';
            NIGERIAN_STATES.forEach(state => {
                const option = document.createElement('option');
                option.value = state.name; option.textContent = state.name;
                stateSelect.appendChild(option);
            });
            lgaSelect.innerHTML = '<option value="">Select LGA</option>';
            lgaSelect.disabled = true;
            stateSelect.addEventListener('change', (e) => {
                const selectedState = e.target.value;
                lgaSelect.innerHTML = '<option value="">Select LGA</option>';
                if (selectedState) {
                    const state = NIGERIAN_STATES.find(s => s.name === selectedState);
                    lgaSelect.disabled = false;
                    if (state) state.lgas.forEach(lga => {
                        const option = document.createElement('option');
                        option.value = lga; option.textContent = lga;
                        lgaSelect.appendChild(option);
                    });
                } else { lgaSelect.disabled = true; }
            });
        },
        setEditStateAndLGA: function(stateName, lgaName) {
            const stateSelect = document.getElementById('editState');
            const lgaSelect   = document.getElementById('editLocal');
            if (!stateSelect || !lgaSelect) return false;
            if (stateSelect.options.length <= 1) {
                NIGERIAN_STATES.forEach(state => {
                    const option = document.createElement('option');
                    option.value = state.name; option.textContent = state.name;
                    stateSelect.appendChild(option);
                });
            }
            if (stateName && stateName !== '') {
                stateSelect.value = stateName;
                const changeEvent = new Event('change', { bubbles: true });
                stateSelect.dispatchEvent(changeEvent);
                setTimeout(() => {
                    if (lgaName && lgaName !== '') lgaSelect.value = lgaName;
                }, 300);
            }
            return true;
        }
    };

    // ============================================================================
    // ADMISSION NUMBER MANAGER
    // ============================================================================
    const AdmissionNumberManager = {
        async updateAdmissionNumber(prefix = '') {
            const yearSelect   = document.getElementById(`${prefix ? prefix + 'A' : 'a'}dmissionYear`);
            const admissionNoInput = document.getElementById(`${prefix ? prefix + 'A' : 'a'}dmissionNo`);
            const modeSelector = prefix
                ? `input[name="admissionMode"][id^="${prefix}"]:checked`
                : 'input[name="admissionMode"]:checked';
            const admissionMode = document.querySelector(modeSelector) ||
                document.querySelector(`#${prefix}AdmissionAuto`) ||
                document.querySelector(`#${prefix}admissionAuto`);
            if (!yearSelect || !admissionNoInput) return;
            const year = yearSelect.value;
            const baseFormat = `TCC/${year}/`;
            if (admissionMode && admissionMode.value === 'auto' && admissionMode.checked) {
                admissionNoInput.readOnly = true;
                try {
                    const response = await axios.get(`/students/last-admission-number?year=${year}`);
                    if (response.data.success) admissionNoInput.value = response.data.admissionNo;
                    else admissionNoInput.value = `${baseFormat}0871`;
                } catch { admissionNoInput.value = `${baseFormat}0871`; }
            } else {
                admissionNoInput.readOnly = false;
                if (!admissionNoInput.value || admissionNoInput.value === `${baseFormat}AUTO`) {
                    admissionNoInput.value = `${baseFormat}0871`;
                } else if (!admissionNoInput.value.startsWith(baseFormat)) {
                    const numericPart = admissionNoInput.value.split('/').pop() || '0871';
                    const numericValue = Math.max(871, parseInt(numericPart) || 871);
                    admissionNoInput.value = `${baseFormat}${numericValue.toString().padStart(4, '0')}`;
                }
            }
        },
        toggleAdmissionInput: function(prefix = '') {
            const modeId = prefix ? `${prefix}AdmissionAuto` : 'admissionAuto';
            const modeEl = document.getElementById(modeId);
            const admissionNoInput = document.getElementById(`${prefix ? prefix + 'A' : 'a'}dmissionNo`);
            const yearSelect = document.getElementById(`${prefix ? prefix + 'A' : 'a'}dmissionYear`);
            if (!admissionNoInput || !yearSelect) return;
            const year = yearSelect.value;
            const baseFormat = `TCC/${year}/`;
            if (modeEl && modeEl.checked) {
                admissionNoInput.readOnly = true;
                this.updateAdmissionNumber(prefix);
            } else {
                admissionNoInput.readOnly = false;
                if (!admissionNoInput.value || admissionNoInput.value === `${baseFormat}AUTO`) {
                    admissionNoInput.value = `${baseFormat}0871`;
                } else if (!admissionNoInput.value.startsWith(baseFormat)) {
                    const numericPart = admissionNoInput.value.split('/').pop() || '0871';
                    const numericValue = Math.max(871, parseInt(numericPart) || 871);
                    admissionNoInput.value = `${baseFormat}${numericValue.toString().padStart(4, '0')}`;
                }
            }
        }
    };

    // ============================================================================
    // EDIT FORM MANAGER
    // ============================================================================
    const EditFormManager = {
        populateEditForm: function(student) {
            Utils.log('Populating edit form', student);

            const studentIdField = document.getElementById('editStudentId');
            if (studentIdField) studentIdField.value = student.id || '';

            // Update form action
            const form = document.getElementById('editStudentForm');
            if (form && student.id) {
                const baseAction = form.dataset.baseAction;
                if (baseAction) form.action = baseAction.replace(':id', student.id);
                else form.action = form.action.replace(/\/\d+\/([^\/]+)$/, '/' + student.id + '/$1').replace(/\/\d+$/, '/' + student.id);
            }

            const set = (id, v) => { const el = document.getElementById(id); if (el) el.value = v || ''; };
            const chk = (name, v) => { const el = document.querySelector(`input[name="${name}"][value="${v}"]`); if (el) el.checked = true; };

            set('editAdmissionNo',    student.admissionNo);
            set('editAdmissionYear',  student.admissionYear || new Date().getFullYear());

            const admissionDate = student.admissionDate || student.admission_date || '';
            if (admissionDate) set('editAdmissionDate', admissionDate.split(' ')[0].split('T')[0]);

            // Admission mode — default to manual so existing no is shown
            const manualEl = document.getElementById('editAdmissionManual');
            const autoEl   = document.getElementById('editAdmissionAuto');
            if (manualEl) { manualEl.checked = true; }
            if (autoEl)   { autoEl.checked = false; }
            const admissionNoInput = document.getElementById('editAdmissionNo');
            if (admissionNoInput) admissionNoInput.readOnly = false;

            set('editSchoolclassid',  student.schoolclassid);
            set('editTermid',         student.termid);
            set('editSessionid',      student.sessionid);
            set('editStudentCategory',student.student_category);
            set('editTitle',          student.title);
            set('editLastname',       student.lastname);
            set('editFirstname',      student.firstname);
            set('editOthername',      student.othername);
            set('editPlaceofbirth',   student.placeofbirth);
            set('editPhoneNumber',    student.phone_number);
            set('editEmail',          student.email);
            set('editFutureAmbition', student.future_ambition);
            set('editPermanentAddress', student.permanent_address);
            set('editNationality',    student.nationality);
            set('editCity',           student.city);
            set('editReligion',       student.religion);
            set('editBloodGroup',     student.blood_group);
            set('editMotherTongue',   student.mother_tongue);
            set('editNinNumber',      student.nin_number);
            set('editFatherName',     student.father_name);
            set('editFatherPhone',    student.father_phone);
            set('editFatherOccupation',student.father_occupation);
            set('editFatherCity',     student.father_city);
            set('editMotherName',     student.mother_name);
            set('editMotherPhone',    student.mother_phone);
            set('editParentEmail',    student.parent_email);
            set('editParentAddress',  student.parent_address);
            set('editLastSchool',     student.last_school);
            set('editLastClass',      student.last_class);
            set('editReasonForLeaving',student.reason_for_leaving);

            // DOB & age
            const dobInput = document.getElementById('editDOB');
            if (dobInput && student.dateofbirth) {
                const dv = student.dateofbirth.split(' ')[0].split('T')[0];
                dobInput.value = dv;
                const ageEl = document.getElementById('editAgeInput');
                if (ageEl) ageEl.value = Utils.calculateAge(dv) || student.age || '';
            }

            // Radio buttons
            if (student.statusId == 1) chk('statusId', '1');
            else if (student.statusId == 2) chk('statusId', '2');
            if (student.student_status === 'Active') chk('student_status', 'Active');
            else if (student.student_status === 'Inactive') chk('student_status', 'Inactive');
            if (student.gender === 'Male') chk('gender', 'Male');
            else if (student.gender === 'Female') chk('gender', 'Female');

            // School House
            const houseEl = document.getElementById('editSchoolHouse');
            if (houseEl) {
                const houseValue = student.schoolhouseid || student.schoolhouse || student.school_house || null;
                if (houseValue) houseEl.value = houseValue;
            }

            // State and LGA
            if (student.state) StateLGAManager.setEditStateAndLGA(student.state, student.local);

            // Avatar
            const avatarImg = document.getElementById('editStudentAvatar');
            if (avatarImg) {
                avatarImg.src = (student.picture && student.picture !== 'unnamed.jpg')
                    ? `/storage/images/student_avatars/${student.picture}`
                    : '{{ asset("theme/layouts/assets/media/avatars/blank.png") }}';
            }

            Utils.log('Edit form populated successfully');
        }
    };

    // ============================================================================
    // VIEW MODAL MANAGER
    // ============================================================================
    const ViewModalManager = {
        currentStudentId: null,
        populateEnhancedViewModal: function(student) {
            Utils.log('Populating view modal', student);
            this.currentStudentId = student.id;

            const setText = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
            const fullName = [student.lastname, student.firstname, student.othername].filter(Boolean).join(' ') || '—';

            setText('viewFullName',        fullName);
            setText('viewFullNameDetail',  fullName);
            setText('viewAdmissionNumber', student.admissionNo || '—');
            setText('viewAdmissionNo',     student.admissionNo || '—');
            setText('viewTitle',           student.title       || '—');
            setText('viewDOB',             Utils.formatDate(student.dateofbirth, 'long'));
            setText('viewAge',             student.age || Utils.calculateAge(student.dateofbirth));
            setText('viewAgeDetail',       student.age || Utils.calculateAge(student.dateofbirth));
            setText('viewPlaceOfBirth',    student.placeofbirth    || '—');
            setText('viewGenderDetail',    student.gender          || '—');
            setText('viewGenderText',      student.gender          || '—');
            setText('viewBloodGroupDetail',    student.blood_group || '—');
            setText('viewBloodGroupAdditional',student.blood_group || '—');
            setText('viewReligionDetail',  student.religion        || '—');
            setText('viewPhoneNumber',     student.phone_number    || '—');
            setText('viewEmailAddress',    student.email           || '—');
            setText('viewPermanentAddress',student.permanent_address || '—');
            setText('viewCity',            student.city            || '—');
            setText('viewStateOrigin',     student.state           || '—');
            setText('viewLGA',             student.local           || '—');
            setText('viewNationality',     student.nationality     || '—');
            setText('viewFutureAmbition',  student.future_ambition || '—');
            setText('viewAdmissionDate',   Utils.formatDate(student.admission_date || student.admissionDate, 'long'));
            setText('viewAdmittedDate',    Utils.formatDate(student.admission_date || student.admissionDate, 'short'));
            const classDisplay = `${student.schoolclass || ''} ${student.arm || ''}`.trim() || '—';
            setText('viewCurrentClass',    classDisplay);
            setText('viewClassDisplay',    classDisplay);
            const classBadge = document.getElementById('viewClassBadge');
            if (classBadge) classBadge.innerHTML = `<i class="fas fa-school me-1"></i>${classDisplay}`;
            setText('viewArm',             student.arm             || '—');
            setText('viewStudentCategory', student.student_category|| '—');
            const studentType = student.statusId == 2 ? 'New Student' : student.statusId == 1 ? 'Old Student' : '—';
            setText('viewStudentType',     studentType);
            const typeBadge = document.getElementById('viewStudentTypeBadge');
            if (typeBadge) {
                if (student.statusId == 2) { typeBadge.style.background = '#fef3c7'; typeBadge.style.color = '#92400e'; typeBadge.innerHTML = `<i class="fas fa-star me-1"></i><span id="viewStudentType">New Student</span>`; }
                else { typeBadge.style.background = '#ede9fe'; typeBadge.style.color = '#5b21b6'; typeBadge.innerHTML = `<i class="fas fa-history me-1"></i><span id="viewStudentType">Old Student</span>`; }
            }
            setText('viewStudentStatus',   student.student_status  || '—');
            setText('viewSchoolHouse',     student.school_house    || '—');
            setText('viewLastSchool',      student.last_school     || '—');
            setText('viewLastClass',       student.last_class      || '—');
            setText('viewReasonForLeaving',student.reason_for_leaving || '—');
            setText('viewFatherFullName',  student.father_name     || '—');
            setText('viewFatherPhone',     student.father_phone    || '—');
            setText('viewFatherOccupation',student.father_occupation || '—');
            setText('viewFatherCityState', student.father_city     || '—');
            setText('viewFatherEmail',     student.parent_email    || '—');
            setText('viewFatherAddress',   student.parent_address  || '—');
            setText('viewMotherFullName',  student.mother_name     || '—');
            setText('viewMotherPhone',     student.mother_phone    || '—');
            setText('viewMotherOccupation',student.mother_occupation || '—');
            setText('viewMotherEmail',     student.parent_email    || '—');
            setText('viewMotherAddress',   student.parent_address  || '—');
            setText('viewParentEmail',     student.parent_email    || '—');
            setText('viewParentAddress',   student.parent_address  || '—');
            setText('viewNIN',             student.nin_number      || '—');
            setText('viewMotherTongue',    student.mother_tongue   || '—');
            setText('viewGuardianName',    student.guardian_name   || (student.father_name || student.mother_name || '—'));
            setText('viewGuardianPhone',   student.guardian_phone  || student.father_phone || '—');

            const fatherBadge = document.getElementById('fatherStatusBadge');
            if (fatherBadge) { fatherBadge.textContent = student.father_name ? 'Available' : 'Not Provided'; fatherBadge.className = `badge ms-1 ${student.father_name ? 'bg-success' : 'bg-secondary'}`; }
            const motherBadge = document.getElementById('motherStatusBadge');
            if (motherBadge) { motherBadge.textContent = student.mother_name ? 'Available' : 'Not Provided'; motherBadge.className = `badge ms-1 ${student.mother_name ? 'bg-success' : 'bg-secondary'}`; }

            // Status indicator
            const si = document.getElementById('studentStatusIndicator');
            if (si) si.style.background = student.student_status === 'Active' ? '#16a34a' : '#6b7280';

            // Photo
            const photoEl = document.getElementById('viewStudentPhoto');
            if (photoEl) {
                const initials = Utils.getInitials(student.firstname, student.lastname);
                if (student.picture && student.picture !== 'unnamed.jpg') {
                    photoEl.src = `/storage/images/student_avatars/${student.picture}`;
                    photoEl.dataset.zoomSrc    = `/storage/images/student_avatars/${student.picture}`;
                } else {
                    photoEl.src = '{{ asset("theme/layouts/assets/media/avatars/blank.png") }}';
                    photoEl.dataset.zoomSrc    = '';
                }
                photoEl.dataset.zoomName   = fullName;
                photoEl.dataset.zoomInit   = initials;
                photoEl.dataset.zoomDetail = `${Utils.escapeHtml(student.admissionNo || '')} &bull; ${Utils.escapeHtml(classDisplay)} &bull; ${Utils.escapeHtml(student.gender || '')}`;
            }

            // Fetch term info
            this.fetchStudentTermInfo(student.id);

            // Reset term history
            const thc = document.getElementById('termHistoryContent');
            const thl = document.getElementById('termHistoryLoading');
            if (thc) { thc.style.display = 'none'; thc.innerHTML = ''; }
            if (thl) thl.style.display = 'block';
        },
        async fetchStudentTermInfo(studentId) {
            try {
                const response = await ApiService.getStudentActiveTerm(studentId);
                const currentTermAlert = document.getElementById('currentTermAlert');
                if (response.success && response.data) {
                    const d = response.data;
                    const setText = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v || '—'; };
                    setText('viewCurrentTerm',   d.term?.term    || '—');
                    setText('viewCurrentSession',d.session?.session || '—');
                    const status = document.getElementById('viewCurrentTermStatus');
                    if (status) status.innerHTML = d.is_current
                        ? '<span class="badge bg-success">Current Active Term</span>'
                        : '<span class="badge bg-warning text-dark">Registered (Not Current)</span>';
                    if (currentTermAlert) currentTermAlert.innerHTML = `<div class="alert alert-success border-0 rounded-3 py-2 small"><i class="fas fa-check-circle me-1"></i><strong>Enrolled:</strong> ${d.schoolClass?.schoolclass || ''} ${d.schoolClass?.armRelation?.arm || ''} &bull; ${d.term?.term || ''} &bull; ${d.session?.session || ''}</div>`;
                } else {
                    ['viewCurrentTerm','viewCurrentSession'].forEach(id => { const el = document.getElementById(id); if (el) el.textContent = '—'; });
                    const status = document.getElementById('viewCurrentTermStatus');
                    if (status) status.innerHTML = '<span class="badge bg-secondary">Not Registered</span>';
                    if (currentTermAlert) currentTermAlert.innerHTML = `<div class="alert alert-warning border-0 rounded-3 py-2 small"><i class="fas fa-exclamation-triangle me-1"></i>No active term registration found.</div>`;
                }
            } catch (e) { Utils.log('Error fetching term info', e, 'error'); }
        }
    };

    // ============================================================================
    // PAGINATION MANAGER
    // ============================================================================
    const PaginationManager = {
        updatePaginationUI: function(pagination) {
            const paginationContainer = document.getElementById('pagination');
            if (!paginationContainer) return;

            const showingCount = document.getElementById('showingCount');
            const toCount      = document.getElementById('toCount');
            const totalCount   = document.getElementById('totalCount');
            const totalStudentsEl = document.getElementById('totalStudents');

            if (showingCount) showingCount.textContent = pagination.from   || 0;
            if (toCount)      toCount.textContent      = pagination.to     || 0;
            if (totalCount)   totalCount.textContent   = pagination.total  || 0;
            if (totalStudentsEl) totalStudentsEl.textContent = pagination.total || 0;

            // Remove existing page number items only (keep prev/next)
            paginationContainer.querySelectorAll('.page-item:not(#prevPageLi):not(#nextPageLi)').forEach(el => el.remove());

            if (pagination.last_page > 1) {
                const startPage = Math.max(1, pagination.current_page - 2);
                const endPage   = Math.min(pagination.last_page, pagination.current_page + 2);
                const addItem = (n) => {
                    const li = document.createElement('li');
                    li.className = `page-item ${n === pagination.current_page ? 'active' : ''}`;
                    const a = document.createElement('a');
                    a.className = 'page-link'; a.href = 'javascript:void(0);'; a.textContent = n;
                    a.onclick = (e) => { e.preventDefault(); AppState.pagination.currentPage = n; StudentManager.fetchStudents(); };
                    li.appendChild(a);
                    paginationContainer.querySelector('#nextPageLi').before(li);
                };
                const addEllipsis = () => {
                    const li = document.createElement('li'); li.className = 'page-item disabled';
                    li.innerHTML = '<span class="page-link">…</span>';
                    paginationContainer.querySelector('#nextPageLi').before(li);
                };
                if (startPage > 1) { addItem(1); if (startPage > 2) addEllipsis(); }
                for (let i = startPage; i <= endPage; i++) addItem(i);
                if (endPage < pagination.last_page) { if (endPage < pagination.last_page - 1) addEllipsis(); addItem(pagination.last_page); }
            }

            // Prev/Next
            const prevPageBtn = document.getElementById('prevPage');
            const nextPageBtn = document.getElementById('nextPage');
            if (prevPageBtn) {
                prevPageBtn.parentElement.classList.toggle('disabled', pagination.current_page <= 1);
                prevPageBtn.onclick = (e) => { e.preventDefault(); if (pagination.current_page > 1) { AppState.pagination.currentPage = pagination.current_page - 1; StudentManager.fetchStudents(); } };
            }
            if (nextPageBtn) {
                nextPageBtn.parentElement.classList.toggle('disabled', pagination.current_page >= pagination.last_page);
                nextPageBtn.onclick = (e) => { e.preventDefault(); if (pagination.current_page < pagination.last_page) { AppState.pagination.currentPage = pagination.current_page + 1; StudentManager.fetchStudents(); } };
            }
        }
    };

    // ============================================================================
    // RENDER MANAGER
    // ============================================================================
    const RenderManager = {
        renderTableView: function(students) {
            const tbody = document.getElementById('studentTableBody');
            if (!tbody) return;
            if (!students || students.length === 0) { tbody.innerHTML = ''; return; }
            const fragment = document.createDocumentFragment();
            students.forEach(student => {
                const row = document.createElement('tr');
                row.dataset.id = student.id;

                const initials  = Utils.getInitials(student.firstname, student.lastname);
                // Full name includes othername
                const fullName  = [student.lastname, student.firstname, student.othername].filter(Boolean).join(' ');
                const avatarSrc = student.picture && student.picture !== 'unnamed.jpg'
                    ? `/storage/images/student_avatars/${student.picture}` : null;

                const avatarHtml = avatarSrc
                    ? `<img src="${avatarSrc}" alt="${Utils.escapeHtml(fullName)}"
                            class="tbl-avatar"
                            data-zoom-src="${avatarSrc}"
                            data-zoom-name="${Utils.escapeHtml(fullName)}"
                            data-zoom-init="${Utils.escapeHtml(initials)}"
                            data-zoom-detail="${Utils.escapeHtml(student.admissionNo||'')} &bull; ${Utils.escapeHtml(student.schoolclass||'')} ${Utils.escapeHtml(student.arm||'')} &bull; ${Utils.escapeHtml(student.gender||'')}"
                            onclick="handleAvatarZoom(this)">`
                    : `<div class="tbl-avatar-init"
                             data-zoom-src=""
                             data-zoom-name="${Utils.escapeHtml(fullName)}"
                             data-zoom-init="${Utils.escapeHtml(initials)}"
                             data-zoom-detail="${Utils.escapeHtml(student.admissionNo||'')} &bull; ${Utils.escapeHtml(student.schoolclass||'')} ${Utils.escapeHtml(student.arm||'')} &bull; ${Utils.escapeHtml(student.gender||'')}"
                             onclick="handleAvatarZoom(this)">${Utils.escapeHtml(initials)}</div>`;

                const statusBadge = student.student_status === 'Active'
                    ? `<span class="badge bg-success bg-gradient px-2 py-1 rounded-pill"><i class="fas fa-circle me-1" style="font-size:7px;"></i>Active</span>`
                    : `<span class="badge bg-secondary bg-gradient px-2 py-1 rounded-pill"><i class="fas fa-circle me-1" style="font-size:7px;"></i>${Utils.escapeHtml(student.student_status || 'Inactive')}</span>`;

                const typeBadge = student.statusId == 2
                    ? `<span class="badge bg-warning text-dark px-2 py-1 rounded-pill ms-1" style="font-size:10px;"><i class="fas fa-star me-1" style="font-size:9px;"></i>New</span>`
                    : `<span class="badge bg-light text-secondary px-2 py-1 rounded-pill ms-1" style="font-size:10px;border:1px solid #dee2e6;"><i class="fas fa-history me-1" style="font-size:9px;"></i>Old</span>`;

                row.innerHTML = `
                    <td><div class="form-check"><input class="form-check-input student-checkbox" type="checkbox" value="${student.id}"></div></td>
                    <td>${avatarHtml}</td>
                    <td>
                        <div style="font-weight:600;font-size:13px;color:#1e293b;">${Utils.escapeHtml(fullName)}</div>
                        <div style="display:flex;gap:6px;align-items:center;margin-top:3px;flex-wrap:wrap;">
                            <span style="background:#f1f5f9;color:#475569;padding:2px 7px;border-radius:20px;font-size:11px;">
                                <i class="fas fa-id-card" style="font-size:9px;margin-right:3px;"></i>${Utils.escapeHtml(student.admissionNo || 'N/A')}
                            </span>
                            ${typeBadge}
                        </div>
                    </td>
                    <td>
                        <div style="font-weight:600;font-size:13px;">${Utils.escapeHtml(student.schoolclass || '')} ${Utils.escapeHtml(student.arm || '')}</div>
                        <small class="text-muted">${Utils.escapeHtml(student.student_category || '')}</small>
                    </td>
                    <td>${statusBadge}</td>
                    <td><span style="display:flex;align-items:center;gap:5px;font-size:13px;"><i class="fas fa-${student.gender === 'Male' ? 'mars text-primary' : 'venus text-danger'}"></i>${Utils.escapeHtml(student.gender || 'N/A')}</span></td>
                    <td style="font-size:12px;color:#6b7280;">${Utils.formatDate(student.created_at, 'short')}</td>
                    <td>
                        <div class="d-flex gap-1 justify-content-end">
                            <button class="btn btn-sm btn-soft-info view-student-btn" data-student-id="${student.id}" title="View"><i class="fas fa-eye"></i></button>
                            <button class="btn btn-sm btn-soft-warning edit-student-btn" data-student-id="${student.id}" title="Edit"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-soft-danger delete-student-btn" data-student-id="${student.id}" title="Delete"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </td>`;
                fragment.appendChild(row);
            });
            tbody.innerHTML = '';
            tbody.appendChild(fragment);
            this.updateCheckAllState();
        },

        renderCardView: function(students) {
            const container = document.getElementById('studentsCardsContainer');
            if (!container) return;
            if (!students || students.length === 0) { container.innerHTML = ''; return; }
            const fragment = document.createDocumentFragment();
            students.forEach(student => {
                const col = document.createElement('div');
                col.className = 'col-xl-3 col-lg-4 col-md-6 mb-4';
                const initials  = Utils.getInitials(student.firstname, student.lastname);
                const fullName  = [student.lastname, student.firstname, student.othername].filter(Boolean).join(' ');
                const avatarSrc = student.picture && student.picture !== 'unnamed.jpg'
                    ? `/storage/images/student_avatars/${student.picture}` : null;

                const avatarHtml = avatarSrc
                    ? `<div class="avatar-container"
                             data-zoom-src="${avatarSrc}"
                             data-zoom-name="${Utils.escapeHtml(fullName)}"
                             data-zoom-init="${Utils.escapeHtml(initials)}"
                             data-zoom-detail="${Utils.escapeHtml(student.admissionNo||'')} &bull; ${Utils.escapeHtml(student.schoolclass||'')} ${Utils.escapeHtml(student.arm||'')}"
                             onclick="handleAvatarZoom(this)">
                            <img src="${avatarSrc}" alt="${Utils.escapeHtml(fullName)}" class="avatar">
                       </div>`
                    : `<div class="avatar-container"
                             data-zoom-src=""
                             data-zoom-name="${Utils.escapeHtml(fullName)}"
                             data-zoom-init="${Utils.escapeHtml(initials)}"
                             data-zoom-detail="${Utils.escapeHtml(student.admissionNo||'')} &bull; ${Utils.escapeHtml(student.schoolclass||'')} ${Utils.escapeHtml(student.arm||'')}"
                             onclick="handleAvatarZoom(this)">
                            <div class="avatar-initials">${Utils.escapeHtml(initials)}</div>
                       </div>`;

                const actBadge  = student.student_status === 'Active'
                    ? `<span class="status-badge status-active"><i class="fas fa-check-circle"></i>Active</span>`
                    : `<span class="status-badge status-inactive"><i class="fas fa-pause-circle"></i>Inactive</span>`;
                const typeBadge = student.statusId == 2
                    ? `<span class="status-badge status-new ms-1"><i class="fas fa-star"></i>New Student</span>`
                    : `<span class="status-badge status-old ms-1"><i class="fas fa-history"></i>Old Student</span>`;

                col.innerHTML = `
                    <div class="student-profile-card" data-id="${student.id}">
                        <div class="checkbox-container">
                            <div class="form-check"><input class="form-check-input student-checkbox" type="checkbox" value="${student.id}"></div>
                        </div>
                        ${avatarHtml}
                        <div class="card-header">
                            <div class="header-content">
                                <h5 class="student-name">${Utils.escapeHtml(fullName)}</h5>
                                <span class="student-admission">${Utils.escapeHtml(student.admissionNo || 'N/A')}</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div>${actBadge}${typeBadge}</div>
                            <div class="student-info-grid mt-2">
                                <div class="info-item"><span class="info-label">Class</span><span class="info-value">${Utils.escapeHtml(student.schoolclass || '')} ${Utils.escapeHtml(student.arm || '')}</span></div>
                                <div class="info-item"><span class="info-label">Gender</span><span class="info-value">${Utils.escapeHtml(student.gender || 'N/A')}</span></div>
                                <div class="info-item"><span class="info-label">Age</span><span class="info-value">${Utils.escapeHtml(String(student.age || Utils.calculateAge(student.dateofbirth) || 'N/A'))}</span></div>
                                <div class="info-item"><span class="info-label">Registered</span><span class="info-value">${Utils.formatDate(student.created_at, 'short')}</span></div>
                            </div>
                            <div class="action-buttons">
                                <button class="action-btn view-btn view-student-btn" data-student-id="${student.id}"><i class="fas fa-eye me-1"></i>View</button>
                                <button class="action-btn edit-btn edit-student-btn" data-student-id="${student.id}"><i class="fas fa-edit me-1"></i>Edit</button>
                                <button class="action-btn delete-btn delete-student-btn" data-student-id="${student.id}"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </div>
                    </div>`;
                fragment.appendChild(col);
            });
            container.innerHTML = '';
            container.appendChild(fragment);
            this.updateCheckAllState();
        },

        updateCheckAllState: function() {
            const totalCheckboxes   = document.querySelectorAll('.student-checkbox').length;
            const checkedCheckboxes = document.querySelectorAll('.student-checkbox:checked').length;
            ['checkAll','checkAllTable'].forEach(id => {
                const el = document.getElementById(id);
                if (el) { el.checked = totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes; el.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes; }
            });
            const bulkActionsDropdown = document.getElementById('bulkActionsDropdown');
            if (bulkActionsDropdown) {
                bulkActionsDropdown.disabled = checkedCheckboxes === 0;
                bulkActionsDropdown.innerHTML = checkedCheckboxes > 0
                    ? `<i class="fas fa-cog me-1"></i>Actions (${checkedCheckboxes})`
                    : `<i class="fas fa-cog me-1"></i>Actions`;
            }
        },

        toggleView: function(viewType) {
            AppState.ui.currentView = viewType;
            const tableView   = document.getElementById('tableView');
            const cardView    = document.getElementById('cardView');
            const tableViewBtn= document.getElementById('tableViewBtn');
            const cardViewBtn = document.getElementById('cardViewBtn');
            if (!tableView || !cardView) return;
            if (viewType === 'table') {
                tableView.classList.remove('d-none'); cardView.classList.add('d-none');
                tableViewBtn?.classList.add('active'); cardViewBtn?.classList.remove('active');
                if (AppState.pagination.data.length > 0) this.renderTableView(AppState.pagination.data);
            } else {
                tableView.classList.add('d-none'); cardView.classList.remove('d-none');
                tableViewBtn?.classList.remove('active'); cardViewBtn?.classList.add('active');
                if (AppState.pagination.data.length > 0) this.renderCardView(AppState.pagination.data);
            }
        }
    };

    // ============================================================================
    // SELECTION MANAGER
    // ============================================================================
    const SelectionManager = {
        initializeCheckboxes: function() {
            document.getElementById('checkAll')?.addEventListener('change', (e) => this.handleSelectAll(e));
            document.getElementById('checkAllTable')?.addEventListener('change', (e) => this.handleSelectAll(e));
            document.addEventListener('change', (e) => {
                if (e.target.classList.contains('student-checkbox')) {
                    const parent = e.target.closest('.student-profile-card, tr');
                    if (parent) parent.classList.toggle('selected', e.target.checked);
                    RenderManager.updateCheckAllState();
                }
            });
        },
        handleSelectAll: function(e) {
            const isChecked = e.target.checked;
            document.querySelectorAll('.student-checkbox').forEach(checkbox => {
                checkbox.checked = isChecked;
                const parent = checkbox.closest('.student-profile-card, tr');
                if (parent) parent.classList.toggle('selected', isChecked);
            });
            RenderManager.updateCheckAllState();
        },
        getSelectedStudentIds: function() {
            return Array.from(document.querySelectorAll('.student-checkbox:checked')).map(cb => cb.value);
        },
        clearAllSelections: function() {
            document.querySelectorAll('.student-checkbox').forEach(checkbox => {
                checkbox.checked = false;
                const parent = checkbox.closest('.student-profile-card, tr');
                if (parent) parent.classList.remove('selected');
            });
            AppState.ui.selectedStudents.clear();
            ['checkAll','checkAllTable'].forEach(id => { const el = document.getElementById(id); if (el) { el.checked = false; el.indeterminate = false; } });
            RenderManager.updateCheckAllState();
        }
    };

    // ============================================================================
    // STUDENT MANAGER
    // ============================================================================
    const StudentManager = {
        async fetchStudents() {
            Utils.showLoading();
            try {
                const paginationData = await ApiService.getStudents(
                    AppState.pagination.currentPage,
                    AppState.pagination.perPage,
                    AppState.filters
                );
                AppState.pagination = {
                    currentPage: paginationData.current_page,
                    lastPage:    paginationData.last_page,
                    total:       paginationData.total,
                    from:        paginationData.from,
                    to:          paginationData.to,
                    data:        paginationData.data
                };
                if (AppState.ui.currentView === 'table') RenderManager.renderTableView(paginationData.data);
                else RenderManager.renderCardView(paginationData.data);
                PaginationManager.updatePaginationUI(paginationData);
                SelectionManager.clearAllSelections();
                paginationData.data.forEach(s => AppState.cache.students.set(s.id.toString(), s));
            } catch (error) {
                Utils.log('Error fetching students', error, 'error');
                Utils.showError('Failed to load students. Please try again.');
            } finally { Utils.hideLoading(); }
        },

        async viewStudent(id) {
            try {
                Utils.showLoading();
                let student = AppState.cache.students.get(id.toString());
                if (!student) {
                    student = await ApiService.getStudent(id);
                    if (student && student.id) AppState.cache.students.set(id.toString(), student);
                }
                Utils.hideLoading();
                if (student) {
                    ViewModalManager.populateEnhancedViewModal(student);
                    new bootstrap.Modal(document.getElementById('viewStudentModal')).show();
                } else { Utils.showError('Student data not found.'); }
            } catch (error) { Utils.hideLoading(); Utils.log('Error viewing student', error, 'error'); Utils.showError('Failed to load student data.'); }
        },

        async editStudent(id) {
            try {
                Utils.showLoading();
                StateLGAManager.initializeEditStateDropdown();
                const student = await ApiService.getStudent(id);
                if (!student || !student.id) throw new Error('Invalid student data');
                Utils.hideLoading();
                EditFormManager.populateEditForm(student);
                new bootstrap.Modal(document.getElementById('editStudentModal')).show();
            } catch (error) { Utils.hideLoading(); Utils.log('Error editing student', error, 'error'); Utils.showError('Failed to load student for editing: ' + (error.message || '')); }
        },

        async deleteStudent(id) {
            if (!await Utils.showConfirm('Delete Student', "You won't be able to revert this!", 'Yes, delete it!')) return;
            try {
                await ApiService.deleteStudent(id);
                AppState.cache.students.delete(id.toString());
                await this.fetchStudents();
                Utils.showSuccess('Student has been deleted.');
            } catch (error) { Utils.log('Error deleting student', error, 'error'); Utils.showError('Failed to delete student.'); }
        },

        async deleteMultiple() {
            const selectedIds = SelectionManager.getSelectedStudentIds();
            if (selectedIds.length === 0) { Utils.showError('Please select at least one student.', 'No Selection'); return; }
            if (!await Utils.showConfirm(`Delete ${selectedIds.length} Students?`, "This action cannot be undone!", 'Yes, delete them!')) return;
            try {
                await ApiService.deleteMultipleStudents(selectedIds);
                selectedIds.forEach(id => AppState.cache.students.delete(id.toString()));
                await this.fetchStudents();
                Utils.showSuccess(`${selectedIds.length} student(s) have been deleted.`);
                SelectionManager.clearAllSelections();
            } catch (error) { Utils.log('Error deleting multiple', error, 'error'); Utils.showError('Failed to delete selected students.'); }
        }
    };

    // ============================================================================
    // CURRENT TERM MANAGER
    // ============================================================================
    const CurrentTermManager = {
        showUpdateCurrentTermModal: function() {
            const selectedIds = SelectionManager.getSelectedStudentIds();
            if (selectedIds.length === 0) { Utils.showError('Please select at least one student.', 'No Selection'); return; }
            document.getElementById('updateCurrentTermForm')?.reset();
            const selectedCountEl = document.getElementById('selectedStudentsCount');
            if (selectedCountEl) selectedCountEl.textContent = selectedIds.length;
            new bootstrap.Modal(document.getElementById('updateCurrentTermModal')).show();
        },
        async updateCurrentTerm() {
            const selectedIds = SelectionManager.getSelectedStudentIds();
            const form  = document.getElementById('updateCurrentTermForm');
            if (!form) return;
            const classId   = form.querySelector('[name="schoolclassId"]')?.value;
            const termId    = form.querySelector('[name="termId"]')?.value;
            const sessionId = form.querySelector('[name="sessionId"]')?.value;
            if (!classId || !termId || !sessionId) { Utils.showError('Please select class, term, and session.', 'Missing Fields'); return; }
            Swal.fire({ title: 'Updating…', text: 'Please wait…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const response = await ApiService.updateBulkCurrentTerm({ student_ids: selectedIds, schoolclassId: classId, termId, sessionId, is_current: true });
                bootstrap.Modal.getInstance(document.getElementById('updateCurrentTermModal'))?.hide();
                Swal.close();
                Utils.showSuccess(response.message || `Term updated for ${selectedIds.length} student(s).`);
                await StudentManager.fetchStudents();
            } catch (error) {
                Swal.close();
                Utils.showError(error.response?.data?.message || error.message || 'Failed to update term.');
            }
        }
    };

    // ============================================================================
    // BULK STATUS MANAGER
    // ============================================================================
    const BulkStatusManager = {
        showUpdateStatusModal: function() {
            const classId   = document.getElementById('schoolclass-filter')?.value;
            const sessionId = document.getElementById('session-filter')?.value;
            if (!classId || classId === 'all' || !sessionId || sessionId === 'all') {
                Utils.showError('Please select both a class and a session first.', 'Selection Required'); return;
            }
            AppState.bulkStatusFilters = { class_id: classId, session_id: sessionId };
            Swal.fire({ title: 'Loading Students…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            ApiService.getStudentsByClassAndSession(classId, sessionId)
                .then(response => {
                    Swal.close();
                    if (response.success) { AppState.bulkStatusData = response; this.renderStatusUpdateModal(response.students, response.stats); }
                    else Utils.showError(response.message || 'Failed to load students.');
                })
                .catch(error => { Swal.close(); Utils.showError(error.response?.data?.message || error.message); });
        },
        renderStatusUpdateModal: function(students, stats) {
            document.getElementById('bulkStatusUpdateModal')?.remove();
            const modalHtml = `
            <div class="modal fade" id="bulkStatusUpdateModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header modal-header-gradient">
                            <h5 class="modal-title"><i class="fas fa-sync-alt me-2"></i>Bulk Update Student Status</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-3 mb-4">
                                <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm"><div class="card-body py-3"><h2 class="text-primary fw-bold mb-1">${stats.total}</h2><div class="text-muted small">Total</div></div></div></div>
                                <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm"><div class="card-body py-3"><h2 class="text-success fw-bold mb-1">${stats.active}</h2><div class="text-muted small">Active</div></div></div></div>
                                <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm"><div class="card-body py-3"><h2 class="text-secondary fw-bold mb-1">${stats.inactive}</h2><div class="text-muted small">Inactive</div></div></div></div>
                                <div class="col-6 col-md-3"><div class="card text-center border-0 shadow-sm"><div class="card-body py-3"><h2 class="text-warning fw-bold mb-1">${stats.new_students}</h2><div class="text-muted small">New Students</div></div></div></div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                <div class="form-check"><input class="form-check-input" type="checkbox" id="selectAllBSCheckbox"><label class="form-check-label fw-semibold" for="selectAllBSCheckbox">Select All</label></div>
                                <div class="d-flex gap-2">
                                    <div class="dropdown">
                                        <button class="btn btn-outline-success btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="fas fa-user-check me-1"></i>Activity Status</button>
                                        <ul class="dropdown-menu shadow border-0">
                                            <li><a class="dropdown-item" href="#" onclick="BulkStatusManager.bulkUpdateStatus('activity_status','Active')"><i class="fas fa-check-circle text-success me-2"></i>Active</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="BulkStatusManager.bulkUpdateStatus('activity_status','Inactive')"><i class="fas fa-pause-circle text-secondary me-2"></i>Inactive</a></li>
                                        </ul>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-warning btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="fas fa-user-tag me-1"></i>Student Type</button>
                                        <ul class="dropdown-menu shadow border-0">
                                            <li><a class="dropdown-item" href="#" onclick="BulkStatusManager.bulkUpdateStatus('student_type','old')"><i class="fas fa-history text-secondary me-2"></i>Old Student</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="BulkStatusManager.bulkUpdateStatus('student_type','new')"><i class="fas fa-star text-warning me-2"></i>New Student</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table data-table align-middle">
                                    <thead><tr><th width="50"><div class="form-check"><input class="form-check-input" type="checkbox" id="selectAllBSTable"></div></th><th>Student</th><th>Admission No</th><th>Class</th><th>Activity</th><th>Type</th><th>Actions</th></tr></thead>
                                    <tbody id="statusUpdateTableBody">${this.renderStudentRows(students)}</tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="BulkStatusManager.refreshData()"><i class="fas fa-sync-alt me-1"></i>Refresh</button>
                        </div>
                    </div>
                </div>
            </div>`;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            // Select all checkboxes
            document.getElementById('selectAllBSCheckbox')?.addEventListener('change', e => { document.querySelectorAll('.bs-checkbox').forEach(cb => cb.checked = e.target.checked); });
            document.getElementById('selectAllBSTable')?.addEventListener('change',    e => { document.querySelectorAll('.bs-checkbox').forEach(cb => cb.checked = e.target.checked); });
            new bootstrap.Modal(document.getElementById('bulkStatusUpdateModal')).show();
        },
        renderStudentRows: function(students) {
            if (!students || students.length === 0) return '<tr><td colspan="7" class="text-center py-4 text-muted">No students found</td></tr>';
            return students.map(student => {
                const studentId = student.id ? parseInt(student.id) : null;
                if (!studentId) return '';
                const initials = Utils.getInitials(student.firstname, student.lastname);
                const fullName = [student.lastname, student.firstname, student.othername].filter(Boolean).join(' ');
                const actBadge = student.student_status === 'Active'
                    ? '<span class="badge bg-success px-2 py-1"><i class="fas fa-check-circle me-1" style="font-size:9px;"></i>Active</span>'
                    : '<span class="badge bg-secondary px-2 py-1"><i class="fas fa-pause-circle me-1" style="font-size:9px;"></i>Inactive</span>';
                const typeBadge = student.statusId == 2
                    ? '<span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-star me-1" style="font-size:9px;"></i>New</span>'
                    : '<span class="badge bg-light text-secondary px-2 py-1" style="border:1px solid #dee2e6;"><i class="fas fa-history me-1" style="font-size:9px;"></i>Old</span>';
                return `
                    <tr data-student-id="${studentId}">
                        <td><div class="form-check"><input class="form-check-input bs-checkbox" type="checkbox" value="${studentId}"></div></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="tbl-avatar-init" style="width:36px;height:36px;border-radius:8px;font-size:13px;flex-shrink:0;">${Utils.escapeHtml(initials)}</div>
                                <div><div style="font-weight:600;font-size:13px;">${Utils.escapeHtml(fullName)}</div></div>
                            </div>
                        </td>
                        <td style="font-size:12px;font-family:monospace;">${Utils.escapeHtml(student.admissionNo || 'N/A')}</td>
                        <td style="font-size:13px;">${Utils.escapeHtml(student.schoolclass || '')} ${Utils.escapeHtml(student.arm || '')}</td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                ${actBadge}
                                <button class="btn btn-sm btn-soft-warning ms-1" style="padding:2px 7px;" onclick="BulkStatusManager.toggleIndividualStatus(this,'activity')" data-student-id="${studentId}" data-current="${student.student_status || 'Inactive'}" title="Toggle"><i class="fas fa-exchange-alt" style="font-size:11px;"></i></button>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                ${typeBadge}
                                <button class="btn btn-sm btn-soft-info ms-1" style="padding:2px 7px;" onclick="BulkStatusManager.toggleIndividualStatus(this,'type')" data-student-id="${studentId}" data-current="${student.statusId || 1}" title="Toggle"><i class="fas fa-exchange-alt" style="font-size:11px;"></i></button>
                            </div>
                        </td>
                        <td><button class="btn btn-sm btn-soft-info" onclick="StudentManager.viewStudent(${studentId})"><i class="fas fa-eye"></i></button></td>
                    </tr>`;
            }).join('');
        },
        getBSSelectedIds: function() {
            return Array.from(document.querySelectorAll('.bs-checkbox:checked'))
                .map(cb => parseInt(cb.value)).filter(v => !isNaN(v));
        },
        async toggleIndividualStatus(button, type) {
            const studentId  = button.dataset.studentId;
            const current    = button.dataset.current;
            const updateType = type === 'activity' ? 'activity_status' : 'student_type';
            const newValue   = type === 'activity'
                ? (current === 'Active' ? 'Inactive' : 'Active')
                : (current == 1 ? 'new' : 'old');
            const displayVal = type === 'activity' ? newValue : (newValue === 'new' ? 'New Student' : 'Old Student');
            const result = await Swal.fire({ title:'Confirm Update', text:`Change status to "${displayVal}"?`, icon:'question', showCancelButton:true, confirmButtonText:'Yes, update' });
            if (!result.isConfirmed) return;
            Swal.fire({ title:'Updating…', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
            try {
                const response = await ApiService.bulkUpdateStatus({ student_ids:[studentId], update_type:updateType, value:newValue });
                Swal.close();
                if (response.success) { Utils.showSuccess('Status updated.'); this.refreshData(); }
            } catch (error) { Swal.close(); Utils.showError('Failed to update status.'); }
        },
        async bulkUpdateStatus(updateType, value) {
            const selectedIds = this.getBSSelectedIds();
            if (selectedIds.length === 0) { Utils.showError('Please select at least one student.', 'No Selection'); return; }
            const displayValue = updateType === 'student_type' ? (value === 'old' ? 'Old Student' : 'New Student') : value;
            if (!await Utils.showConfirm(`Update ${selectedIds.length} student(s)?`, `Set to "${displayValue}"?`, 'Yes, update')) return;
            Swal.fire({ title:'Updating…', html:`Updating ${selectedIds.length} student(s)…`, allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
            try {
                const response = await ApiService.bulkUpdateStatus({ student_ids: selectedIds, update_type: updateType, value });
                Swal.close();
                if (response.success) { Utils.showSuccess(response.message); this.refreshData(); }
            } catch (error) { Swal.close(); Utils.showError(error.response?.data?.message || error.message || 'Failed to update.'); }
        },
        async refreshData() {
            if (!AppState.bulkStatusFilters) return;
            Swal.fire({ title:'Refreshing…', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
            try {
                const response = await ApiService.getStudentsByClassAndSession(AppState.bulkStatusFilters.class_id, AppState.bulkStatusFilters.session_id);
                if (response.success) {
                    const tbody = document.getElementById('statusUpdateTableBody');
                    if (tbody) tbody.innerHTML = this.renderStudentRows(response.students);
                    const cards = document.querySelectorAll('#bulkStatusUpdateModal .card-body h2');
                    const s = response.stats;
                    if (cards.length >= 4) { cards[0].textContent=s.total; cards[1].textContent=s.active; cards[2].textContent=s.inactive; cards[3].textContent=s.new_students; }
                }
                Swal.close();
            } catch (error) { Swal.close(); Utils.showError('Failed to refresh data.'); }
        }
    };

    // ============================================================================
    // TERM REGISTRATION MANAGER
    // ============================================================================
    const TermRegistrationManager = {
        showTermStudentsModal: function() {
            const termId    = document.getElementById('term-filter')?.value;
            const sessionId = document.getElementById('session-filter')?.value;
            if (!termId || termId === 'all' || !sessionId || sessionId === 'all') {
                Utils.showError('Please select both a term and a session first.', 'Selection Required'); return;
            }
            AppState.termFilters = {
                term_id:    termId,
                session_id: sessionId,
                class_id:   document.getElementById('schoolclass-filter')?.value !== 'all' ? document.getElementById('schoolclass-filter')?.value : null
            };
            Swal.fire({ title:'Loading…', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
            ApiService.getStudentsInTerm(AppState.termFilters)
                .then(response => {
                    Swal.close();
                    if (response.success) this.renderTermStudentsModal(response.students, response.total);
                    else Utils.showError(response.message || 'Failed to load students.');
                })
                .catch(error => { Swal.close(); Utils.showError(error.response?.data?.message || error.message); });
        },
        renderTermStudentsModal: function(students, total) {
            document.getElementById('termStudentsModal')?.remove();
            const modalHtml = `
            <div class="modal fade" id="termStudentsModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header modal-header-gradient">
                            <h5 class="modal-title"><i class="fas fa-calendar-alt me-2"></i>Term Registration Management</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="alert alert-info border-0 rounded-3 d-flex align-items-center gap-3 mb-4">
                                <i class="fas fa-info-circle fa-2x"></i>
                                <div><strong>Total Registered: ${total}</strong><br><small>Manage term registrations below.</small></div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                <div class="form-check"><input class="form-check-input" type="checkbox" id="selectAllTRMCheckbox"><label class="form-check-label fw-semibold" for="selectAllTRMCheckbox">Select All</label></div>
                                <button class="btn btn-danger btn-sm" onclick="TermRegistrationManager.bulkRemoveFromTerm()"><i class="fas fa-user-minus me-1"></i>Remove Selected</button>
                            </div>
                            <div class="row" id="termStudentsContainer">${this.renderStudentCards(students)}</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="TermRegistrationManager.refreshData()"><i class="fas fa-sync-alt me-1"></i>Refresh</button>
                        </div>
                    </div>
                </div>
            </div>`;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            document.getElementById('selectAllTRMCheckbox')?.addEventListener('change', e => { document.querySelectorAll('.term-student-checkbox').forEach(cb => cb.checked = e.target.checked); });
            new bootstrap.Modal(document.getElementById('termStudentsModal')).show();
        },
        renderStudentCards: function(students) {
            if (!students || students.length === 0) return '<div class="col-12"><div class="alert alert-warning rounded-3 text-center">No students registered for this term.</div></div>';
            return students.map(student => {
                const initials = ((student.firstname||'').charAt(0) + (student.lastname||'').charAt(0)).toUpperCase() || 'ST';
                const currentBadge = student.is_current ? '<span class="badge bg-success position-absolute top-0 end-0 m-2" style="font-size:10px;">Current</span>' : '';
                return `<div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                    <div class="student-profile-card" data-registration-id="${student.registration_id}">
                        <div class="checkbox-container">
                            <div class="form-check"><input class="form-check-input term-student-checkbox" type="checkbox" value="${student.registration_id}"></div>
                        </div>
                        <div class="card-header" style="position:relative;">
                            ${currentBadge}
                            <div class="avatar-container" style="position:absolute;top:12px;right:12px;width:62px;height:62px;">
                                <div class="avatar-initials" style="font-size:22px;">${Utils.escapeHtml(initials)}</div>
                            </div>
                            <div class="header-content">
                                <h5 class="student-name" style="font-size:15px;">${Utils.escapeHtml(student.fullname || '')}</h5>
                                <span class="student-admission">${Utils.escapeHtml(student.admissionNo || 'N/A')}</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="student-info-grid">
                                <div class="info-item"><span class="info-label">Class</span><span class="info-value">${Utils.escapeHtml(student.class || '')} ${Utils.escapeHtml(student.arm || '')}</span></div>
                                <div class="info-item"><span class="info-label">Gender</span><span class="info-value">${Utils.escapeHtml(student.gender || '—')}</span></div>
                            </div>
                            <button class="btn btn-outline-danger btn-sm w-100 mt-2"
                                    onclick="TermRegistrationManager.removeSingleStudent(${student.registration_id},'${Utils.escapeHtml(student.fullname || '')}')">
                                <i class="fas fa-user-minus me-1"></i>Remove from Term
                            </button>
                        </div>
                    </div>
                </div>`;
            }).join('');
        },
        getTRMSelectedIds: function() {
            return Array.from(document.querySelectorAll('.term-student-checkbox:checked')).map(cb => cb.value);
        },
        async removeSingleStudent(registrationId, studentName) {
            if (!await Utils.showConfirm('Remove Student?', `Remove "${studentName}" from this term?`, 'Yes, remove')) return;
            Swal.fire({ title:'Removing…', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
            try {
                const response = await ApiService.removeStudentFromTerm(registrationId);
                Swal.close();
                if (response.success) {
                    Utils.showSuccess(response.message);
                    const card = document.querySelector(`.student-profile-card[data-registration-id="${registrationId}"]`);
                    card?.closest('.col-xl-3,.col-lg-4,.col-md-6')?.remove();
                }
            } catch (error) { Swal.close(); Utils.showError('Failed to remove student.'); }
        },
        async bulkRemoveFromTerm() {
            const selectedIds = this.getTRMSelectedIds();
            if (selectedIds.length === 0) { Utils.showError('Please select at least one student.', 'No Selection'); return; }
            if (!await Utils.showConfirm(`Remove ${selectedIds.length} student(s)?`, 'This will remove their term registration.', 'Yes, remove all')) return;
            Swal.fire({ title:'Removing…', html:`Removing ${selectedIds.length} student(s)…`, allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
            try {
                const response = await ApiService.bulkRemoveFromTerm(selectedIds);
                Swal.close();
                if (response.success) { Utils.showSuccess(response.message); this.refreshData(); }
            } catch (error) { Swal.close(); Utils.showError('Failed to remove students.'); }
        },
        async refreshData() {
            if (!AppState.termFilters) return;
            Swal.fire({ title:'Refreshing…', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
            try {
                const response = await ApiService.getStudentsInTerm(AppState.termFilters);
                if (response.success) {
                    const container = document.getElementById('termStudentsContainer');
                    if (container) container.innerHTML = this.renderStudentCards(response.students);
                }
                Swal.close();
            } catch (error) { Swal.close(); Utils.showError('Failed to refresh data.'); }
        }
    };

    // ============================================================================
    // REPORT MANAGER — FIXED SORTABLE (flat flex items, no col wrappers)
    // ============================================================================
    const ReportManager = {
        sortableInstance: null,

        initializeReportModal: function() {
            const container = document.getElementById('columnsContainer');
            if (!container) return;

            if (typeof Sortable === 'undefined') {
                console.error('Sortable library not loaded!');
                return;
            }

            // Destroy existing instance
            if (this.sortableInstance) {
                try { this.sortableInstance.destroy(); } catch(e) {}
                this.sortableInstance = null;
            }

            // Create Sortable on the flat flex container
            // The draggable items are direct children with class .draggable-item
            this.sortableInstance = new Sortable(container, {
                animation: 200,
                handle: '.drag-handle',
                draggable: '.draggable-item',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                filter: 'input, label',  // do not start drag on input/label clicks
                preventOnFilter: false,
                onStart: function() { document.body.style.cursor = 'grabbing'; },
                onEnd: () => { document.body.style.cursor = ''; this.updateColumnOrder(); this.updatePreview(); }
            });

            // Checkbox change events
            container.querySelectorAll('.column-checkbox').forEach(cb => {
                cb.addEventListener('change', () => { this.updateColumnOrder(); this.updatePreview(); });
            });

            // Select/Deselect all
            document.getElementById('selectAllColumnsBtn')?.addEventListener('click', () => {
                container.querySelectorAll('.column-checkbox').forEach(cb => cb.checked = true);
                this.updateColumnOrder(); this.updatePreview();
            });
            document.getElementById('deselectAllColumnsBtn')?.addEventListener('click', () => {
                container.querySelectorAll('.column-checkbox').forEach(cb => cb.checked = false);
                this.updateColumnOrder(); this.updatePreview();
            });

            this.updateColumnOrder();
            this.updatePreview();
        },

        updateColumnOrder: function() {
            const container  = document.getElementById('columnsContainer');
            const orderInput = document.getElementById('columnsOrderInput');
            if (!container || !orderInput) return;
            const items = container.querySelectorAll('.draggable-item');
            const checkedInOrder = Array.from(items)
                .filter(el => el.querySelector('.column-checkbox')?.checked)
                .map(el => el.dataset.column);
            orderInput.value = checkedInOrder.join(',');
            // Update order badges
            let n = 0;
            items.forEach(el => {
                let badge = el.querySelector('.order-badge');
                if (el.querySelector('.column-checkbox')?.checked) {
                    n++;
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'order-badge';
                        el.appendChild(badge);
                    }
                    badge.textContent = n;
                } else {
                    badge?.remove();
                }
            });
        },

        updatePreview: function() {
            const previewEl = document.getElementById('columnOrderPreview');
            if (!previewEl) return;
            const container = document.getElementById('columnsContainer');
            if (!container) return;
            const items = container.querySelectorAll('.draggable-item');
            const labels = Array.from(items)
                .filter(el => el.querySelector('.column-checkbox')?.checked)
                .map(el => {
                    const key   = el.dataset.column;
                    const label = el.querySelector('label');
                    return label ? label.textContent.trim() : key;
                });
            previewEl.textContent = labels.length ? labels.join(', ') : 'No columns selected';
        },

        async generateReport() {
            const form = document.getElementById('printReportForm');
            if (!form) { Utils.showError('Report form not found'); return; }

            const selectedColumns = Array.from(form.querySelectorAll('.column-checkbox:checked')).map(cb => cb.value);
            if (selectedColumns.length === 0) { Utils.showError('Please select at least one column.', 'No Columns Selected'); return; }

            const formData = new FormData(form);
            const params   = {};
            for (const [k, v] of formData.entries()) {
                if (k === 'columns[]') { params.columns = params.columns ? params.columns + ',' + v : v; }
                else if (k === 'columns_order') { const oi = document.getElementById('columnsOrderInput'); params.columns_order = oi?.value || v; }
                else if (v) { params[k] = v; }
            }
            if (!params.format) { const r = form.querySelector('input[name="format"]:checked'); params.format = r?.value || 'pdf'; }
            if (!params.orientation) { params.orientation = document.getElementById('orientation')?.value || 'portrait'; }
            params.include_header = form.querySelector('input[name="include_header"]')?.checked ? '1' : '0';
            params.include_logo   = form.querySelector('input[name="include_logo"]')?.checked   ? '1' : '0';
            params.exclude_photos = '0';

            bootstrap.Modal.getInstance(document.getElementById('printStudentReportModal'))?.hide();

            Swal.fire({ title:'Generating Report…', html:'Please wait while your report is being generated…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            try {
                const response = await axios({ method:'GET', url:'/students/report', params, responseType:'blob', timeout: 120000 });
                Swal.close();
                const url  = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href  = url;
                let filename = `student-report-${new Date().toISOString().split('T')[0]}.${params.format === 'excel' ? 'xlsx' : 'pdf'}`;
                const cd = response.headers['content-disposition'];
                if (cd) { const m = cd.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/); if (m && m[1]) filename = m[1].replace(/['"]/g, ''); }
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
                Utils.showSuccess(`Report generated: ${filename}`);
            } catch (error) {
                Swal.close();
                let errorMessage = 'Failed to generate report.';
                if (error.response?.data instanceof Blob) {
                    try { const t = await error.response.data.text(); const j = JSON.parse(t); errorMessage = j.message || errorMessage; } catch(e) {}
                } else if (error.response?.data?.message) { errorMessage = error.response.data.message; }
                Utils.showError(errorMessage, 'Report Generation Failed');
            }
        }
    };

    // ================================================================
    // FORM SUBMISSIONS
    // ================================================================
    document.getElementById('addStudentForm')?.addEventListener('submit', async e => {
        e.preventDefault();
        const form = e.target;
        const fd = new FormData(form);
        Swal.fire({title:'Saving…',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
        try {
            const r = await axios.post(form.action, fd, {headers:{'Content-Type':'multipart/form-data'}});
            if (r.data.success) {
                bootstrap.Modal.getInstance(document.getElementById('addStudentModal'))?.hide();
                await SM.fetch(); U.ok(r.data.message||'Student registered.');
                form.reset();
                const av = document.getElementById('addStudentAvatar'); if (av) av.src = 'https://via.placeholder.com/120x120/2563eb/ffffff?text=Photo';
            }
        } catch(err) { Swal.close(); U.err(err.response?.data?.message||'Failed to save student.'); }
    });

    document.getElementById('editStudentForm')?.addEventListener('submit', async e => {
        e.preventDefault();
        const form = e.target;
        const fd = new FormData(form);
        fd.append('_method','PATCH');
        Swal.fire({title:'Updating…',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
        try {
            const r = await axios.post(form.action, fd, {headers:{'Content-Type':'multipart/form-data'}});
            if (r.data.success) {
                bootstrap.Modal.getInstance(document.getElementById('editStudentModal'))?.hide();
                await SM.fetch(); U.ok(r.data.message||'Student updated.');
            } else { Swal.close(); U.err(r.data.message||'Failed.'); }
        } catch(err) { Swal.close(); U.err(err.response?.data?.message||'Failed to update student.'); }
    });

    // ================================================================
    // EVENT DELEGATION & INIT
    // ================================================================
    document.addEventListener('click', e => {
        const vb = e.target.closest('.view-student-btn');   if (vb) { e.preventDefault(); SM.view(vb.dataset.studentId); return; }
        const eb = e.target.closest('.edit-student-btn');   if (eb) { e.preventDefault(); SM.edit(eb.dataset.studentId); return; }
        const db = e.target.closest('.delete-student-btn'); if (db) { e.preventDefault(); SM.delete(db.dataset.studentId); return; }
    });

    document.getElementById('deleteMultipleBtn')?.addEventListener('click', e => { e.preventDefault(); SM.deleteMany(); });
    document.getElementById('updateCurrentTermBtn')?.addEventListener('click', e => { e.preventDefault(); CTM.show(); });
    document.getElementById('confirmUpdateCurrentTerm')?.addEventListener('click', () => CTM.update());
    document.getElementById('generateReportBtn')?.addEventListener('click', e => { e.preventDefault(); Report.generate(); });
    document.getElementById('tableViewBtn')?.addEventListener('click', () => Render.toggleView('table'));
    document.getElementById('cardViewBtn')?.addEventListener('click',  () => Render.toggleView('card'));
    document.getElementById('bulkStatusBtn')?.addEventListener('click', e => { e.preventDefault(); BSM.show(); });
    document.getElementById('manageTermBtn')?.addEventListener('click',  e => { e.preventDefault(); TRM.show(); });

    // Report modal — init sortable when it opens
    document.getElementById('printStudentReportModal')?.addEventListener('show.bs.modal', () => {
        setTimeout(() => Report.init(), 200);
    });

    // ================================================================
    // GLOBAL FUNCTIONS (used in templates / onclick attrs)
    // ================================================================
    window.handleAvatarZoom = el => {
        const src    = el.dataset.zoomSrc;
        const name   = el.dataset.zoomName;
        const init   = el.dataset.zoomInit;
        const detail = el.dataset.zoomDetail;
        U.openZoom(src || null, name, init, detail);
    };
    window.openZoomFromView = el => {
        const src    = el.dataset.zoomSrc;
        const name   = el.dataset.zoomName;
        const init   = el.dataset.zoomInit;
        const detail = el.dataset.zoomDetail;
        U.openZoom(src||null, name, init, detail);
    };
    window.updateAdmissionNumber = prefix => {
        const yr = document.getElementById(`${prefix?prefix+'A':'a'}dmissionYear`)?.value || new Date().getFullYear();
        const input = document.getElementById(`${prefix?prefix+'A':'a'}dmissionNo`);
        if (input) { const base = `TCC/${yr}/`; if (!input.value.startsWith(base)) input.value = `${base}0001`; }
    };
    window.toggleAdmissionInput = prefix => {
        const p = prefix ? prefix.charAt(0).toUpperCase() + prefix.slice(1) : '';
        const mode = document.querySelector(`input[name="admissionMode"]#${prefix?prefix:''}${p?'':prefix}AdmissionAuto, input[name="admissionMode"]#${prefix}AdmissionAuto, input[name="admissionMode"]#admissionAuto`);
        const selMode = prefix ? document.querySelector(`input[name="admissionMode"][id^="${prefix}"]:checked`) : document.querySelector('input[name="admissionMode"]:checked');
        const input = document.getElementById(`${prefix?prefix+'A':'a'}dmissionNo`);
        if (input && selMode) input.readOnly = selMode.value === 'auto';
    };
    window.previewImage = (input, targetId='addStudentAvatar') => {
        const f = input.files[0]; if (!f) return;
        const reader = new FileReader();
        reader.onload = e => { const img = document.getElementById(targetId); if (img) img.src = e.target.result; };
        reader.readAsDataURL(f);
    };
    window.calculateAge = (dob, ageInputId) => { const el = document.getElementById(ageInputId); if (el) el.value = U.age(dob)||''; };
    window.callNumber = id => { const p = document.getElementById(id)?.textContent; if (p && p !== '—') window.location.href = `tel:${p}`; };
    window.sendSMS    = id => { const p = document.getElementById(id)?.textContent; if (p && p !== '—') window.location.href = `sms:${p}`; };
    window.sendEmail  = id => { const em = document.getElementById(id)?.textContent; if (em && em !== '—') window.location.href = `mailto:${em}`; };
    window.editStudentFromView = () => {
        const id = ViewModal.currentId;
        if (id) { bootstrap.Modal.getInstance(document.getElementById('viewStudentModal'))?.hide(); SM.edit(id); }
    };
    window.printStudentProfile = () => window.print();
    window.refreshTermHistory  = () => { if (ViewModal.currentId) ViewModal.fetchTerm(ViewModal.currentId); };

    // Expose managers (needed by inline onclick in dynamically created modals)
    window.BulkStatusManager       = BSM;
    window.TermRegistrationManager = TRM;
    window.StudentManager          = { viewStudent: id => SM.view(id) };

    // ================================================================
    // BOOT
    // ================================================================
    function boot() {
        U.csrf();
        Filters.init();
        Selection.init();
        SLM.populate('addState','addLocal');
        SLM.populate('editState','editLocal');
        SM.fetch();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();

})();
</script>
@endsection
