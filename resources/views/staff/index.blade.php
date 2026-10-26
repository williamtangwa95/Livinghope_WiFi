@extends('layouts.app')

@section('title', 'Staff & Permissions | LIVING HOPE Wi-Fi')
@section('eyebrow', 'WORKSPACE / TEAM ACCESS')
@section('page_title', 'Staff & Permissions')

@section('content')
<div class="content">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div>
            <h2 style="margin:0 0 4px 0;font-size:20px;font-weight:700;color:var(--navy);">Attendants & Cashiers</h2>
            <p style="margin:0;font-size:13px;color:var(--muted);">Manage authorized staff members who can sell vouchers and serve Wi-Fi customers at your location.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openStaffModal()">
            ＋ Add New Staff Member
        </button>
    </div>

    <!-- Permission Levels Overview -->
    <div class="grid grid-3" style="margin-bottom:24px;">
        <div class="card stat">
            <span class="stat-label">BUSINESS OWNER</span>
            <div style="font-size:16px;font-weight:700;color:var(--navy);margin:6px 0;">Full Control</div>
            <p style="font-size:12px;color:var(--muted);margin:0;">Manages packages, settings, portal customization, revenue reports, and all staff accounts.</p>
        </div>

        <div class="card stat">
            <span class="stat-label">CASHIER ROLE</span>
            <div style="font-size:16px;font-weight:700;color:var(--navy);margin:6px 0;">Voucher Sales & Print</div>
            <p style="font-size:12px;color:var(--muted);margin:0;">Can generate walk-in vouchers, print tickets, and view active sessions without editing settings.</p>
        </div>

        <div class="card stat">
            <span class="stat-label">OPERATOR ROLE</span>
            <div style="font-size:16px;font-weight:700;color:var(--navy);margin:6px 0;">Hardware & Monitor</div>
            <p style="font-size:12px;color:var(--muted);margin:0;">Access to network device ping tests, AP heartbeat checks, and client disconnects.</p>
        </div>
    </div>

    <!-- Staff Members Table -->
    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;">
            <h3 style="margin:0;font-size:16px;font-weight:700;">Active Staff Accounts ({{ $staff->count() }})</h3>
            <span class="eyebrow">HOTSPOT ATTENDANTS</span>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Staff Name</th>
                        <th>Email / Login</th>
                        <th>Phone</th>
                        <th>Assigned Role</th>
                        <th>Hotspot Venue</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $member)
                        <tr>
                            <td>
                                <strong>{{ $member->name }}</strong>
                                @if($member->id === auth()->id())
                                    <span class="badge badge-active" style="margin-left:6px;font-size:10px;">YOU</span>
                                @endif
                            </td>
                            <td>{{ $member->email }}</td>
                            <td>{{ $member->phone ?? '—' }}</td>
                            <td>
                                <span class="badge badge-available">
                                    {{ ucfirst($member->role) }}
                                </span>
                            </td>
                            <td>{{ $member->business_name ?? 'Primary Hotspot' }}</td>
                            <td>
                                <span class="badge {{ $member->isVerified() ? 'badge-active' : 'badge-expired' }}">
                                    {{ $member->isVerified() ? 'Active' : 'Unverified' }}
                                </span>
                            </td>
                            <td>{{ $member->created_at->format('M d, Y') }}</td>
                            <td>
                                @if($member->id !== auth()->id())
                                    <form action="{{ route('staff.destroy', $member->id) }}" method="POST" onsubmit="return confirm('Remove staff access for {{ $member->name }}?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-secondary btn-sm" style="color:var(--danger);border-color:#fca5a5;">
                                            Revoke
                                        </button>
                                    </form>
                                @else
                                    <span style="font-size:11px;color:var(--muted);">Owner</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:32px;color:var(--muted);">
                                No additional staff members added yet. Click "Add New Staff Member" to add a cashier.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Staff Modal -->
<div class="modal-backdrop" id="staffModal">
    <div class="modal">
        <div class="modal-head">
            <h3>Add New Staff Member</h3>
            <button type="button" class="modal-close" onclick="closeStaffModal()">✕</button>
        </div>
        <form action="{{ route('staff.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="staffName">Staff Full Name *</label>
                <input type="text" id="staffName" name="name" class="field" placeholder="e.g. Asha Mwangi" required>
            </div>

            <div class="form-group">
                <label for="staffEmail">Staff Login Email *</label>
                <input type="email" id="staffEmail" name="email" class="field" placeholder="asha@livinghope.co.tz" required>
            </div>

            <div class="form-group">
                <label for="staffPhone">Phone Number</label>
                <input type="text" id="staffPhone" name="phone" class="field" placeholder="+255 712 000 000">
            </div>

            <div class="form-group">
                <label for="staffRole">Assign Role & Permissions *</label>
                <select id="staffRole" name="role_title" class="field" required>
                    <option value="cashier" selected>Cashier (Voucher Sales, Printing & Walk-in Customers)</option>
                    <option value="operator">Network Operator (Diagnostics & Sessions)</option>
                    <option value="manager">Assistant Manager (Sales + Analytics)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="staffPassword">Temporary Login Password (min 6 characters) *</label>
                <input type="password" id="staffPassword" name="password" class="field" placeholder="••••••••" required>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeStaffModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Staff Account</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openStaffModal() {
        document.getElementById('staffModal').classList.add('active');
    }
    function closeStaffModal() {
        document.getElementById('staffModal').classList.remove('active');
    }
</script>
@endsection
