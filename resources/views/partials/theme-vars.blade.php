<style>
    :root {
        --primary-color: {{ $themeSettings['primary'] }};
        --secondary-color: {{ $themeSettings['secondary'] }};
        --primary-hover: {{ $themeSettings['primary_hover'] }};
        --primary-light: {{ $themeSettings['primary_light'] }};
        --primary-rgb: {{ $themeSettings['primary_rgb'] }};
        --secondary-rgb: {{ $themeSettings['secondary_rgb'] }};
    }
</style>
<script>
    window.__themeColors = {
        primary: '{{ $themeSettings['primary'] }}',
        secondary: '{{ $themeSettings['secondary'] }}',
        primaryHover: '{{ $themeSettings['primary_hover'] }}',
        primaryLight: '{{ $themeSettings['primary_light'] }}'
    };
</script>
