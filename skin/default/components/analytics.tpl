{if !empty($mc_settings.analytics.value)}
    <script async src="https://www.googletagmanager.com/gtag/js?id={$mc_settings.analytics.value|escape:'html'}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());

        gtag('config', '{$mc_settings.analytics.value|escape:'html'}');
    </script>
{/if}