<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-Y5GYZ94XHE"></script>
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'G-Y5GYZ94XHE');
</script>

<script async src="https://www.googletagmanager.com/gtag/js?id=<?= $config['google_ads_id'] ?>"></script>

<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }

    gtag('js', new Date());

    // Google Ads
    gtag('config', '<?= $config['google_ads_id'] ?>');

    // GA4
    gtag('config', '<?= $config['ga4_id'] ?>');

    // Conversão Google Ads
    window.GOOGLE_ADS_SEND_TO = '<?= $config['google_ads_conversion_id'] ?>';
</script>


<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3573552933822285"
    crossorigin="anonymous"></script>