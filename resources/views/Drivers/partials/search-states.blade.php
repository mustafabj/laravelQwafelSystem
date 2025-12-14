@if ($state === 'initial')
    <tr>
        <td colspan="5" class="text-center py-4">
            <i class="fas fa-search text-muted" style="font-size: 2rem;"></i>
            <p class="text-muted mt-2">ابحث عن السائق بالاسم أو رقم الهاتف</p>
            <small class="text-muted">أدخل حرفين على الأقل للبحث</small>
        </td>
    </tr>
@elseif ($state === 'loading')
    <tr>
        <td colspan="5" class="text-center py-4">
            <i class="fas fa-spinner fa-spin text-primary" style="font-size: 2rem;"></i>
            <p class="text-muted mt-2">جاري البحث...</p>
        </td>
    </tr>
@elseif ($state === 'no-results')
    <tr>
        <td colspan="5" class="text-center py-4">
            <i class="fas fa-search text-muted" style="font-size: 2rem;"></i>
            <p class="text-muted mt-2">لا توجد نتائج</p>
            <small class="text-muted">جرب البحث بكلمات مختلفة</small>
        </td>
    </tr>
@elseif ($state === 'error')
    <tr>
        <td colspan="5" class="text-center py-4">
            <i class="fas fa-exclamation-triangle text-danger" style="font-size: 2rem;"></i>
            <p class="text-danger mt-2">حدث خطأ أثناء البحث</p>
            <small class="text-muted">يرجى المحاولة مرة أخرى</small>
        </td>
    </tr>
@endif

