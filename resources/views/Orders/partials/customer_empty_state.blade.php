@php
    $type = $type ?? 'initial'; // 'initial' or 'no-results'
    $message = $type === 'initial' 
        ? 'ابدأ البحث عن طريق كتابة اسم العميل أو رقم الهاتف'
        : 'لم يتم العثور على عملاء مطابقين لبحثك';
@endphp

<tr class="empty-state">
    <td colspan="9">
        <div class="empty-message">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <p>لا يوجد عملاء</p>
            <span>{{ $message }}</span>
        </div>
    </td>
</tr>

