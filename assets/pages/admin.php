<?php if(!(isset($_SERVER['HTTP_X_REQUESTED_WITH'])&&strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])==='xmlhttprequest')){header('Location: ../../login.php');exit;} ?>
<style>
    .admin-stats-cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin: 20px 0;
    }

    .stat-card {
        background: rgba(30, 30, 30, 0.4);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 20px;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.4);
    }

    .stat-card .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: rgba(255, 204, 0, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        color: #ffcc00;
        flex-shrink: 0;
    }

    .stat-card.stat-online .stat-icon {
        background: rgba(46, 199, 110, 0.1);
        color: #2ec76e;
    }

    .stat-card.stat-disabled .stat-icon {
        background: rgba(255, 82, 82, 0.1);
        color: #ff5252;
    }

    .stat-card .stat-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .stat-card .stat-number {
        font-size: 2rem;
        font-weight: 800;
        color: #fff;
        line-height: 1;
        letter-spacing: -0.5px;
    }

    .stat-card .stat-label {
        font-size: 0.85rem;
        color: #aaa;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .admin-stats-cards {
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        .stat-card {
            padding: 18px 16px;
            gap: 14px;
        }
        .stat-card .stat-icon {
            width: 44px;
            height: 44px;
            font-size: 1.2rem;
        }
        .stat-card .stat-number {
            font-size: 1.5rem;
        }
    }

    @media (max-width: 480px) {
        .admin-stats-cards {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .stat-card {
            padding: 14px 16px;
        }
        .stat-card .stat-icon {
            width: 40px;
            height: 40px;
            font-size: 1.1rem;
        }
        .stat-card .stat-number {
            font-size: 1.3rem;
        }
    }

    .user_actions button {
        border-radius: 10px;
        padding: 8px 16px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        outline: none;
        font-family: inherit;
    }

    .btn-reset {
        background: rgba(6, 146, 201, 0.1) !important;
        color: #0692c9 !important;
        border: 1.5px solid rgba(6, 146, 201, 0.2) !important;
    }
    .btn-reset:hover {
        background: #0692c9 !important;
        color: #000 !important;
        box-shadow: 0 0 12px rgba(6, 146, 201, 0.4);
        transform: translateY(-2px);
    }

    .btn-disable {
        background: rgba(245, 158, 11, 0.1) !important;
        color: #f59e0b !important;
        border: 1.5px solid rgba(245, 158, 11, 0.2) !important;
    }
    .btn-disable:hover:not(:disabled) {
        background: #f59e0b !important;
        color: #000 !important;
        box-shadow: 0 0 12px rgba(245, 158, 11, 0.4);
        transform: translateY(-2px);
    }

    .btn-enable {
        background: rgba(16, 185, 129, 0.1) !important;
        color: #10b981 !important;
        border: 1.5px solid rgba(16, 185, 129, 0.2) !important;
    }
    .btn-enable:hover:not(:disabled) {
        background: #10b981 !important;
        color: #000 !important;
        box-shadow: 0 0 12px rgba(16, 185, 129, 0.4);
        transform: translateY(-2px);
    }

    .btn-edit {
        background: rgba(99, 102, 241, 0.1) !important;
        color: #818cf8 !important;
        border: 1.5px solid rgba(99, 102, 241, 0.2) !important;
    }
    .btn-edit:hover:not(:disabled) {
        background: #6366f1 !important;
        color: #fff !important;
        box-shadow: 0 0 12px rgba(99, 102, 241, 0.4);
        transform: translateY(-2px);
    }

    .btn-delete {
        background: rgba(239, 68, 68, 0.1) !important;
        color: #ef4444 !important;
        border: 1.5px solid rgba(239, 68, 68, 0.2) !important;
    }
    .btn-delete:hover:not(:disabled) {
        background: #ef4444 !important;
        color: #fff !important;
        box-shadow: 0 0 12px rgba(239, 68, 68, 0.4);
        transform: translateY(-2px);
    }

    .btn-logs {
        background: rgba(168, 85, 247, 0.1) !important;
        color: #a855f7 !important;
        border: 1.5px solid rgba(168, 85, 247, 0.2) !important;
    }
    .btn-logs:hover:not(:disabled) {
        background: #a855f7 !important;
        color: #fff !important;
        box-shadow: 0 0 12px rgba(168, 85, 247, 0.4);
        transform: translateY(-2px);
    }

    .btn-devices {
        background: rgba(255, 204, 0, 0.1) !important;
        color: #ffcc00 !important;
        border: 1.5px solid rgba(255, 204, 0, 0.2) !important;
    }
    .btn-devices:hover:not(:disabled) {
        background: #ffcc00 !important;
        color: #000 !important;
        box-shadow: 0 0 12px rgba(255, 204, 0, 0.4);
        transform: translateY(-2px);
    }

    .user_actions button:disabled {
        opacity: 0.4;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }

    .mou_table_responsive {
        width: 100%;
        overflow-x: auto;
        background: rgba(30, 30, 30, 0.4);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 20px;
        margin-top: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    .mou_admin_table {
        width: 100%;
        border-collapse: collapse;
        text-align: right;
        direction: rtl;
        font-family: inherit;
        color: #fff;
    }

    .mou_admin_table th {
        background: rgba(255, 204, 0, 0.04);
        border-bottom: 2px solid rgba(255, 204, 0, 0.1);
        padding: 14px 12px;
        font-weight: 700;
        font-size: 0.85rem;
        color: #ffcc00;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .mou_admin_table th.sortable {
        cursor: pointer;
        user-select: none;
    }
    .mou_admin_table th.sortable:hover {
        background: rgba(255, 204, 0, 0.08);
    }
    .mou_admin_table th.sortable .sort-icon {
        margin-inline-start: 4px;
        font-size: 0.75rem;
        opacity: 0.5;
    }
    .mou_admin_table th.sortable.active .sort-icon {
        opacity: 1;
        color: #ffcc00;
    }

    .mou_admin_table td {
        padding: 14px 12px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        vertical-align: middle;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .mou_admin_table tbody tr {
        transition: background-color 0.3s ease;
    }

    .mou_admin_table tbody tr:hover {
        background-color: rgba(255, 204, 0, 0.02);
    }

    .mou_admin_table tbody tr:last-child td {
        border-bottom: none;
    }

    @keyframes pulse-green {
        0% { box-shadow: 0 0 0 0 rgba(46, 199, 110, 0.7); }
        70% { box-shadow: 0 0 0 8px rgba(46, 199, 110, 0); }
        100% { box-shadow: 0 0 0 0 rgba(46, 199, 110, 0); }
    }
    .user-row .online-dot {
        position: relative;
    }
    .user-row .online-dot[style*="background-color: rgb(46, 199, 110)"],
    .user-row .online-dot[style*="background-color: #2ec76e"] {
        animation: pulse-green 2s infinite;
    }

    .pagination-bar {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 16px 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        flex-wrap: wrap;
    }
    .pagination-bar .page-btn {
        background: rgba(255, 255, 255, 0.06);
        color: #ccc;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s;
        font-family: inherit;
    }
    .pagination-bar .page-btn:hover:not(:disabled) {
        background: rgba(255, 204, 0, 0.15);
        color: #ffcc00;
        border-color: rgba(255, 204, 0, 0.3);
    }
    .pagination-bar .page-btn:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }
    .pagination-bar .page-btn.active {
        background: rgba(255, 204, 0, 0.2);
        color: #ffcc00;
        border-color: rgba(255, 204, 0, 0.4);
        font-weight: 700;
    }
    .pagination-bar .page-info {
        color: #999;
        font-size: 0.85rem;
    }
    .pagination-bar .per-page-select {
        margin-inline-start: auto;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .pagination-bar .per-page-select .custom-select-wrapper {
        width: auto;
        display: inline-block;
    }

    .search-bar {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .search-bar input {
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        padding: 8px 14px;
        color: #fff;
        font-size: 0.9rem;
        font-family: inherit;
        outline: none;
        width: 200px;
        transition: border-color 0.3s;
    }
    .search-bar input:focus {
        border-color: rgba(255, 204, 0, 0.3);
    }
    .search-bar input::placeholder {
        color: #666;
    }

    .admin-search-toggle-btn.active,
    .full_view_container [data-navigation_url] .header .header-tools .admin-search-toggle-btn.active {
        background: rgba(255, 204, 0, 0.2) !important;
        border-color: rgba(255, 204, 0, 0.4) !important;
    }

    .toggle-group {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.06);
    }

    .toggle-group .toggle-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .toggle-group .toggle-info span {
        color: #fff;
        font-weight: 500;
        font-size: 0.9rem;
    }

    .toggle-group .toggle-info small {
        color: #888;
        font-size: 0.78rem;
    }

    .toggle-switch {
        position: relative;
        width: 46px;
        height: 26px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 13px;
        cursor: pointer;
        transition: background 0.3s;
        flex-shrink: 0;
    }

    .toggle-switch.active {
        background: #ffcc00;
    }

    .toggle-switch .knob {
        position: absolute;
        top: 3px;
        left: 3px;
        width: 20px;
        height: 20px;
        background: #fff;
        border-radius: 50%;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    [dir="rtl"] .toggle-switch .knob {
        left: auto;
        right: 3px;
    }

    .toggle-switch.active .knob {
        transform: translateX(20px);
    }

    [dir="rtl"] .toggle-switch.active .knob {
        transform: translateX(-20px);
    }

    .admin-search-close-btn {
        background: none;
        border: none;
        color: #888;
        cursor: pointer;
        padding: 8px 10px;
        font-size: 1rem;
        transition: color 0.2s;
        flex-shrink: 0;
    }
    .admin-search-close-btn:hover {
        color: #ffcc00;
    }

    .admin-search-sticky {
        display: none;
        position: sticky;
        top: 70px;
        z-index: 99;
        padding: 12px 20px;
        background: rgba(14, 14, 16, 0.92);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        margin-top: -1px;
    }
    .admin-search-sticky.show {
        display: block;
    }
    .full_view_container [data-navigation_url] .header .header-tools {
        margin-inline-start: auto !important;
    }
    .admin-search-sticky .search-bar {
        display: flex;
        align-items: center;
    }
    .admin-search-sticky .search-bar input {
        width: 100%;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        padding: 8px 14px;
        color: #fff;
        font-size: 0.9rem;
        font-family: inherit;
        outline: none;
        transition: border-color 0.3s;
        box-sizing: border-box;
    }
    .admin-search-sticky .search-bar input:focus {
        border-color: rgba(255, 204, 0, 0.3);
    }
    .admin-search-sticky .search-bar input::placeholder {
        color: #666;
    }

    .mou_table_responsive {
        padding-top: 0;
    }

    @media (max-width: 768px) {
        .mou_admin_table th,
        .mou_admin_table td {
            padding: 10px 8px;
            font-size: 0.8rem;
        }
        .user_actions button {
            padding: 6px 10px;
            font-size: 0.75rem;
        }
        .search-bar input {
            width: 140px;
        }
    }

    @media (max-width: 767px) {
        .mou_table_responsive {
            background: transparent;
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
            border: none;
            box-shadow: none;
            border-radius: 0;
            overflow-x: visible;
        }

        .mou_admin_table thead {
            display: none;
        }

        .mou_admin_table tbody tr {
            display: block;
            margin-bottom: 18px;
            background: rgba(30, 30, 35, 0.65);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 204, 0, 0.08);
            border-inline-start: 3px solid rgba(255, 204, 0, 0.3);
            border-radius: 18px;
            padding: 16px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.25);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .mou_admin_table tbody tr:hover {
            background: rgba(30, 30, 35, 0.75);
        }

        .mou_admin_table tbody tr:last-child {
            margin-bottom: 0;
        }

        .mou_admin_table tbody tr:last-child td {
            border-bottom: none;
        }

        .mou_admin_table td {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            font-size: 0.85rem;
            text-align: start;
        }

        .mou_admin_table td:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .mou_admin_table td::before {
            content: attr(data-label);
            font-size: 0.7rem;
            font-weight: 700;
            color: #ffcc00;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            opacity: 0.8;
            flex-shrink: 0;
            margin-inline-end: 12px;
        }

        .mou_admin_table td:first-child {
            justify-content: flex-start;
            padding-top: 0;
            padding-bottom: 4px;
            border-bottom: none;
        }
        .mou_admin_table td:first-child::before {
            display: none;
        }
        .mou_admin_table td:first-child span {
            font-size: 0.7rem;
            background: rgba(255, 204, 0, 0.1);
            color: #ffcc00;
            padding: 2px 10px;
            border-radius: 20px;
        }

        .mou_admin_table td:nth-child(2) {
            justify-content: flex-start;
            gap: 8px;
            padding-top: 4px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255, 204, 0, 0.06);
        }
        .mou_admin_table td:nth-child(2)::before {
            display: none;
        }
        .mou_admin_table td:nth-child(2) .online-dot {
            width: 10px !important;
            height: 10px !important;
        }
        .mou_admin_table td:nth-child(2) img {
            width: 36px !important;
            height: 36px !important;
        }

        .mou_admin_table td:last-child {
            flex-direction: column;
            align-items: stretch;
            padding-top: 12px;
            padding-bottom: 0;
            border-top: 1px solid rgba(255, 204, 0, 0.06);
            margin-top: 4px;
            border-bottom: none;
        }
        .mou_admin_table td:last-child::before {
            display: none;
        }
        .mou_admin_table td:last-child .user_actions {
            display: grid !important;
            grid-template-columns: repeat(6, 1fr);
            gap: 6px;
            width: 100%;
        }
        .mou_admin_table td:last-child .user_actions button {
            padding: 9px 0;
            font-size: 0.85rem;
            justify-content: center;
            border-radius: 10px;
        }

        .header {
            flex-wrap: wrap;
            gap: 10px;
        }
        .header .header-tools .actions .main-actions-group button {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .mou_admin_table td:last-child .user_actions {
            grid-template-columns: repeat(4, 1fr);
        }
        .mou_admin_table tbody tr {
            padding: 12px;
            margin-bottom: 14px;
            border-radius: 14px;
        }

        .pagination-bar {
            flex-wrap: wrap;
            justify-content: center;
        }
    }

    .logs-table-wrap {
        max-height: 400px;
        overflow-y: auto;
    }
    .logs-table-wrap table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }
    .logs-table-wrap th {
        background: rgba(255, 204, 0, 0.06);
        color: #ffcc00;
        padding: 10px 12px;
        text-align: right;
        font-weight: 600;
        border-bottom: 1px solid rgba(255, 204, 0, 0.1);
        position: sticky;
        top: 0;
        z-index: 1;
        white-space: nowrap;
    }
    .logs-table-wrap td {
        padding: 8px 12px;
        border-bottom: 1px solid rgba(255,255,255,0.04);
        vertical-align: middle;
    }
    .logs-table-wrap tr:hover td {
        background: rgba(255,255,255,0.02);
    }
    .log-success {
        color: #10b981;
    }
    .log-fail {
        color: #ef4444;
    }
    .logs-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        padding: 12px;
        border-top: 1px solid rgba(255,255,255,0.05);
    }
    .logs-pagination button {
        background: rgba(255,255,255,0.06);
        color: #ccc;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 6px;
        padding: 6px 14px;
        font-size: 0.8rem;
        cursor: pointer;
        font-family: inherit;
        transition: all 0.3s;
    }
    .logs-pagination button:hover:not(:disabled) {
        background: rgba(168, 85, 247, 0.15);
        color: #a855f7;
    }
    .logs-pagination button:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }
    .logs-pagination .page-info {
        color: #999;
        font-size: 0.8rem;
    }

    @media (max-width: 767px) {
        .logs-table-wrap table thead {
            display: none;
        }
        .logs-table-wrap table tr {
            display: block;
            margin-bottom: 12px;
            background: rgba(30, 30, 35, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            padding: 12px;
        }
        .logs-table-wrap table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            font-size: 0.8rem;
        }
        .logs-table-wrap table td:last-child {
            border-bottom: none;
        }
        .logs-table-wrap table td::before {
            content: attr(data-label);
            font-weight: 600;
            font-size: 0.7rem;
            color: #a855f7;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            opacity: 0.8;
            margin-inline-end: 10px;
            flex-shrink: 0;
        }
        .logs-table-wrap table td[data-label="المتصفح"],
        .logs-table-wrap table td[data-label="User Agent"] {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }
        .logs-table-wrap table td[data-label="المتصفح"]::before,
        .logs-table-wrap table td[data-label="User Agent"]::before {
            display: block;
            margin-bottom: 2px;
        }
        .logs-table-wrap {
            max-height: none;
        }
    }
</style>

<div class="header">
    <span data-i18n="admin.page_title">إدارة المستخدمين</span>

    <div class="header-tools">
        <div class="actions">
            <div class="main-actions-group">
                <button class="add_user_btn" title="إضافة مستخدم جديد" data-i18n-title="admin.add_user"><i class="fas fa-user-plus"></i> <span data-i18n="admin.add_user">إضافة مستخدم</span></button>
                <button class="admin-search-toggle-btn" title="بحث" data-i18n-title="common.search"><i class="fas fa-search"></i> <span data-i18n="common.search">بحث</span></button>
            </div>
        </div>
    </div>
</div>

<div class="admin-stats-cards">
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <span class="stat-number" id="stat-total">0</span>
            <span class="stat-label" data-i18n="admin.total_users">إجمالي المستخدمين</span>
        </div>
    </div>
    <div class="stat-card stat-online">
        <div class="stat-icon"><i class="fas fa-circle" style="color:#2ec76e;"></i></div>
        <div class="stat-info">
            <span class="stat-number" id="stat-online">0</span>
            <span class="stat-label" data-i18n="admin.online_users">المتصلون الآن</span>
        </div>
    </div>
    <div class="stat-card stat-disabled">
        <div class="stat-icon"><i class="fas fa-ban"></i></div>
        <div class="stat-info">
            <span class="stat-number" id="stat-disabled">0</span>
            <span class="stat-label" data-i18n="admin.disabled_users">المعطلون</span>
        </div>
    </div>
</div>

<div class="admin-search-sticky">
    <div class="search-bar">
        <input type="text" id="admin-search-input" placeholder="بحث..." data-i18n-placeholder="common.search">
        <button class="admin-search-close-btn"><i class="fas fa-times"></i></button>
    </div>
</div>

<div class="mou_table_responsive">
    <table class="mou_admin_table">
        <thead>
            <tr>
                <th class="sortable" data-sort="id"># <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                <th class="sortable" data-sort="username"><span data-i18n="common.username">اسم المستخدم</span> <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                <th class="sortable" data-sort="email"><span data-i18n="common.email">البريد الإلكتروني</span> <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                <th class="sortable" data-sort="role"><span data-i18n="admin.role">الدور</span> <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                <th class="sortable" data-sort="status"><span data-i18n="admin.status">الحالة</span> <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                <th class="sortable" data-sort="gender"><span data-i18n="profile.gender">الجنس</span> <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                <th class="sortable" data-sort="country"><span data-i18n="profile.country">الدولة</span> <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                <th class="sortable" data-sort="created_at"><span data-i18n="admin.created_at">تاريخ الإنشاء</span> <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                <th class="sortable" data-sort="last_activity"><span data-i18n="admin.last_activity">آخر نشاط</span> <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                <th class="sortable" data-sort="max_devices"><span data-i18n="admin.max_devices">الأجهزة</span> <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                <th data-i18n="admin.actions">الإجراءات</th>
            </tr>
        </thead>
        <tbody id="admin_users_container">
        </tbody>
    </table>
    <div class="pagination-bar" id="pagination-bar">
        <button class="page-btn" id="page-prev" disabled><i class="fas fa-chevron-right"></i> <span data-i18n="admin.prev_page">السابق</span></button>
        <div id="page-numbers" style="display:flex;gap:6px;align-items:center;"></div>
        <button class="page-btn" id="page-next" disabled><span data-i18n="admin.next_page">التالي</span> <i class="fas fa-chevron-left"></i></button>
        <div class="page-info" id="page-info"></div>
        <div class="per-page-select">
            <span style="color:#999;font-size:0.8rem;margin-inline-end:6px;" data-i18n="admin.show">عرض</span>
            <select id="per-page-select" data-custom-select>
                <option value="10">10</option>
                <option value="20" selected>20</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>
</div>

<div class="mou_popup mou_bottom_pop" id="admin_add_user_popup">
    <div class="mou_popup_container mou_box_shadow">
        <div class="mou_popup_header">
            <span class="mou_pop_up_title" data-i18n="admin.add_user">إضافة حساب جديد</span>
            <span class="close_popup" data-closepopup="admin_add_user_popup"></span>
        </div>
        <div class="mou_popup_body" style="padding: 20px;">
            <form id="admin_add_user_form" autocomplete="off">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="color: #fff; display: block; margin-bottom: 8px; font-weight: 500;" data-i18n="common.username">اسم المستخدم</label>
                        <input type="text" id="admin_username" required placeholder="أدخل اسم المستخدم" autocomplete="off" data-i18n-placeholder="placeholder.username"
                            style="width:100%;padding:12px;border-radius:12px;background:rgba(255,255,255,0.05);border:1.5px solid rgba(255,255,255,0.1);color:#fff;font-family:inherit;font-size:1rem;transition:border-color 0.3s;box-sizing:border-box;" />
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="color: #fff; display: block; margin-bottom: 8px; font-weight: 500;" data-i18n="common.email">البريد الإلكتروني</label>
                        <input type="email" id="admin_email" placeholder="أدخل البريد الإلكتروني" autocomplete="off" data-i18n-placeholder="placeholder.email"
                            style="width:100%;padding:12px;border-radius:12px;background:rgba(255,255,255,0.05);border:1.5px solid rgba(255,255,255,0.1);color:#fff;font-family:inherit;font-size:1rem;transition:border-color 0.3s;box-sizing:border-box;" />
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="color: #fff; display: block; margin-bottom: 8px; font-weight: 500;" data-i18n="common.password">كلمة المرور</label>
                        <input type="password" id="admin_password" required placeholder="أدخل كلمة مرور قوية" autocomplete="new-password" data-i18n-placeholder="placeholder.password"
                            style="width:100%;padding:12px;border-radius:12px;background:rgba(255,255,255,0.05);border:1.5px solid rgba(255,255,255,0.1);color:#fff;font-family:inherit;font-size:1rem;transition:border-color 0.3s;box-sizing:border-box;" />
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="color: #fff; display: block; margin-bottom: 8px; font-weight: 500;" data-i18n="common.confirm_password">تأكيد كلمة المرور</label>
                        <input type="password" id="admin_confirm_password" required placeholder="أعد إدخال كلمة المرور" autocomplete="new-password" data-i18n-placeholder="placeholder.confirm_password"
                            style="width:100%;padding:12px;border-radius:12px;background:rgba(255,255,255,0.05);border:1.5px solid rgba(255,255,255,0.1);color:#fff;font-family:inherit;font-size:1rem;transition:border-color 0.3s;box-sizing:border-box;" />
                    </div>
                </div>
                <div style="margin-top:16px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="color: #fff; display: block; margin-bottom: 8px; font-weight: 500;" data-i18n="admin.role">الدور</label>
                        <select id="admin_role" style="width:100%;padding:12px;border-radius:12px;background:rgba(255,255,255,0.05);border:1.5px solid rgba(255,255,255,0.1);color:#fff;font-family:inherit;font-size:1rem;transition:border-color 0.3s;box-sizing:border-box;">
                            <option value="user" data-i18n="admin.role_user">مستخدم</option>
                            <option value="admin" data-i18n="admin.role_admin">مدير</option>
                        </select>
                    </div>
                </div>
                <div class="mou_popup_footer" style="display: flex; justify-content: flex-end; padding-top: 20px;">
                    <button type="submit" class="btn-primary" style="padding: 12px 30px; font-size: 1rem; font-weight: bold; border-radius: 12px; cursor: pointer; transition: all 0.3s;" data-i18n="admin.add_user_btn">
                        تأكيد الحفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="mou_popup mou_bottom_pop" id="admin_edit_user_popup">
    <div class="mou_popup_container mou_box_shadow">
        <div class="mou_popup_header">
            <span class="mou_pop_up_title" data-i18n="admin.edit_user">تعديل المستخدم</span>
            <span class="close_popup" data-closepopup="admin_edit_user_popup"></span>
        </div>
        <div class="mou_popup_body" style="padding: 20px;">
            <form id="admin_edit_user_form" autocomplete="off">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="color: #fff; display: block; margin-bottom: 8px; font-weight: 500;" data-i18n="common.username">اسم المستخدم</label>
                    <input type="text" id="edit_username" required placeholder="أدخل اسم المستخدم" autocomplete="off" data-i18n-placeholder="placeholder.username"
                        style="width: 100%; padding: 12px; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1.5px solid rgba(255, 255, 255, 0.1); color: #fff; font-family: inherit; font-size: 1rem; transition: border-color 0.3s;" />
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="color: #fff; display: block; margin-bottom: 8px; font-weight: 500;" data-i18n="admin.role">الدور</label>
                    <select id="edit_role" style="width: 100%; padding: 12px; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1.5px solid rgba(255, 255, 255, 0.1); color: #fff; font-family: inherit; font-size: 1rem; transition: border-color 0.3s;">
                        <option value="user">مستخدم</option>
                        <option value="admin">مدير</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 25px;">
                    <label style="color: #fff; display: block; margin-bottom: 8px; font-weight: 500;" data-i18n="admin.password_label">كلمة المرور</label>
                    <input type="password" id="edit_password" placeholder="اتركه فارغاً إذا لم ترد التغيير" autocomplete="new-password" data-i18n-placeholder="placeholder.edit_password_optional"
                        style="width: 100%; padding: 12px; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1.5px solid rgba(255, 255, 255, 0.1); color: #fff; font-family: inherit; font-size: 1rem; transition: border-color 0.3s;" />
                </div>
                <div class="mou_popup_footer" style="display: flex; justify-content: flex-end; padding-top: 10px;">
                    <button type="submit" class="btn-primary" style="padding: 12px 30px; font-size: 1rem; font-weight: bold; border-radius: 12px; cursor: pointer; transition: all 0.3s;" data-i18n="admin.edit_user_btn">
                        حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="mou_popup mou_bottom_pop" id="admin_logs_popup">
    <div class="mou_popup_container mou_box_shadow" style="max-width:900px;">
        <div class="mou_popup_header">
            <span class="mou_pop_up_title" id="logs-popup-title">سجل الدخول</span>
            <span class="close_popup" data-closepopup="admin_logs_popup"></span>
        </div>
        <div class="mou_popup_body" style="padding: 16px;">
            <div class="logs-table-wrap" id="logs-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th data-i18n="admin.logs_time">الوقت</th>
                            <th data-i18n="admin.logs_method">الطريقة</th>
                            <th data-i18n="admin.logs_ip">IP</th>
                            <th data-i18n="admin.logs_country">الدولة</th>
                            <th data-i18n="admin.logs_city">المدينة</th>
                            <th data-i18n="admin.logs_status">الحالة</th>
                            <th data-i18n="admin.logs_reason">السبب</th>
                            <th data-i18n="admin.logs_user_agent">المتصفح</th>
                        </tr>
                    </thead>
                    <tbody id="logs-table-body">
                    </tbody>
                </table>
            </div>
            <div class="logs-pagination" id="logs-pagination">
                <button id="logs-page-prev" disabled><i class="fas fa-chevron-right"></i> <span data-i18n="admin.prev_page">السابق</span></button>
                <span class="page-info" id="logs-page-info"></span>
                <button id="logs-page-next" disabled><span data-i18n="admin.next_page">التالي</span> <i class="fas fa-chevron-left"></i></button>
            </div>
        </div>
    </div>
</div>

<div class="mou_popup mou_bottom_pop" id="admin_max_devices_popup">
    <div class="mou_popup_container mou_box_shadow" style="max-width:450px;">
        <div class="mou_popup_header">
            <span class="mou_pop_up_title" data-i18n="admin.set_max_devices">تحديد عدد الأجهزة</span>
            <span class="close_popup" data-closepopup="admin_max_devices_popup"></span>
        </div>
        <div class="mou_popup_body" style="padding: 20px;">
            <form id="admin_max_devices_form" autocomplete="off">
                <input type="hidden" id="max_devices_user_id">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="color: #fff; display: block; margin-bottom: 8px; font-weight: 500;" data-i18n="admin.max_devices_label">عدد الأجهزة المسموح بها</label>
                    <input type="number" id="max_devices_input" min="0" placeholder="0 = بدون حد أقصى" data-i18n-placeholder="settings.max_devices_hint"
                        style="width: 100%; padding: 12px; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1.5px solid rgba(255, 255, 255, 0.1); color: #fff; font-family: inherit; font-size: 1rem; transition: border-color 0.3s; box-sizing: border-box;" />
                    <div class="toggle-group" style="margin-top: 12px;">
                        <div class="toggle-info">
                            <span data-i18n="admin.use_global_default">استخدام الإعداد العام</span>
                            <small data-i18n="settings.max_devices_hint">0 = بدون حد أقصى</small>
                        </div>
                        <div class="toggle-switch active" id="max_devices_reset_toggle">
                            <div class="knob"></div>
                        </div>
                    </div>
                </div>
                <div class="mou_popup_footer" style="display: flex; justify-content: flex-end; padding-top: 10px; gap: 10px;">
                    <button type="submit" class="btn-primary" style="padding: 12px 30px; font-size: 1rem; font-weight: bold; border-radius: 12px; cursor: pointer; transition: all 0.3s;" data-i18n="common.save">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../assets/js/admin.js"></script>