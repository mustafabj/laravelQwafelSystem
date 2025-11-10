<div class="details">
    <h1>
        {{ auth()->user()->office->officeName ?? '' }}
        @if(isset(auth()->user()->office) && auth()->user()->office->officeId != 2)
            السفر
        @endif
        لنقل الركاب والبريد
    </h1>

    <h2>
        {{ auth()->user()->office->officeAddress ?? '' }}
    </h2>

    <h3>
        <span><b>Tel:</b> 062227100</span>
        <span><b>Mob:</b> +962798797100 - +962796713271</span>
        <span><b>Iraq:</b> +9647732248881</span>
    </h3>
</div>
