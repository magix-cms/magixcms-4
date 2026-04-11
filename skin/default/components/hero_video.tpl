{strip}
    {* 1. Définition des variables de la vidéo *}
    {$youtube_id = 'lBTCB7yLs8Y'}

    {* Un ratio panoramique parfait pour un Hero Header (ex: 21/9 ou 16/9) *}
    {$hero_ratio = '21 / 9'}

    {* 2. Création propre du JSON de configuration pour le Javascript *}
    {$ytb_options = [
    'videoId' => $youtube_id,
    'playerVars' => [
    'rel' => 0,
    'fs' => 1,
    'autoplay' => 1
    ]
    ]}

    {* 3. La structure HTML *}
    <section class="hero-video-section w-100 position-relative">

        <div class="magix-ytb-container w-100" data-ratio="{$hero_ratio}">

            {* On passe le tableau Smarty en JSON valide pour notre script MagixCore *}
            <picture class="ytb-video-preview" data-ytb='{$ytb_options|json_encode}'>

                {* Source WebP avec Resolution Switching pur *}
                <source type="image/webp" sizes="100vw"
                        srcset="https://i.ytimg.com/vi_webp/{$youtube_id}/maxresdefault.webp 1280w,
                                https://i.ytimg.com/vi_webp/{$youtube_id}/hqdefault.webp 640w,
                                https://i.ytimg.com/vi_webp/{$youtube_id}/mqdefault.webp 480w,
                                https://i.ytimg.com/vi_webp/{$youtube_id}/sddefault.webp 320w">

                {* Image de secours JPEG *}
                {* ATTENTION : On utilise object-fit-cover pour que l'image remplisse le bloc sans se déformer *}
                <img src="https://i.ytimg.com/vi/{$youtube_id}/maxresdefault.jpg"
                     sizes="100vw"
                     srcset="https://i.ytimg.com/vi/{$youtube_id}/maxresdefault.jpg 1280w,
                             https://i.ytimg.com/vi/{$youtube_id}/hqdefault.jpg 640w,
                             https://i.ytimg.com/vi/{$youtube_id}/mqdefault.jpg 480w,
                             https://i.ytimg.com/vi/{$youtube_id}/sddefault.jpg 320w"
                     alt="{#hero_video_alt#|default:'Vidéo de présentation Magix CMS'}"
                     class="img-fluid magix-ytb-cover w-100 h-100 object-fit-cover"
                     fetchpriority="high">

            </picture>

        </div>

        {* 4. Optionnel : Overlay de texte par-dessus la miniature de la vidéo *}
        <div class="position-absolute top-50 start-50 translate-middle text-center w-100 z-2" style="pointer-events: none;">
            <div class="container">
                <h1 class="display-3 fw-bold text-white text-shadow mb-3">Magix CMS 4</h1>
                <p class="lead text-white text-shadow fw-medium">L'excellence SEO par nature.</p>
                {* Le bouton "Play" virtuel de votre CSS prendra le relais visuellement *}
            </div>
        </div>

    </section>
{/strip}