<?php
/**
 * App Store Links Block Template.
 *
 * @var array $attributes Block attributes.
 */

// Use Manager to get dynamic data (cache/fresh)
if (class_exists('AppStoreLinks_Manager')) {
    $attributes = AppStoreLinks_Manager::get_app_data($attributes);
}

$app_name = isset($attributes['appName']) ? $attributes['appName'] : '';
$icon_url = isset($attributes['iconUrl']) ? $attributes['iconUrl'] : '';
$developer = isset($attributes['developer']) ? $attributes['developer'] : '';
$price = isset($attributes['price']) ? $attributes['price'] : '';
$app_store_url = isset($attributes['appStoreUrl']) ? $attributes['appStoreUrl'] : '';
$google_play_url = isset($attributes['googlePlayUrl']) ? $attributes['googlePlayUrl'] : '';
$show_app_store = isset($attributes['showAppStore']) ? $attributes['showAppStore'] : true;
$show_google_play = isset($attributes['showGooglePlay']) ? $attributes['showGooglePlay'] : true;
$last_updated = isset($attributes['lastUpdated']) ? $attributes['lastUpdated'] : '';

if (empty($app_name)) {
    return;
}
?>

<div class="appreach">
    <?php if (!empty($icon_url)) : ?>
        <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr($app_name); ?>" class="appreach__icon" style="">
    <?php endif; ?>
    
    <div class="appreach__detail" style="">
        <p class="appreach__name"><?php echo esc_html($app_name); ?></p>
        <p class="appreach__info">
            <?php if (!empty($developer)) : ?>
                <span class="appreach__developper"><?php echo esc_html($developer); ?></span>
            <?php endif; ?>
            
            <?php if (!empty($price)) : ?>
                <span class="appreach__price"><?php echo esc_html($price); ?></span>
            <?php endif; ?>

            <?php 
            $rating = isset($attributes['rating']) ? (float)$attributes['rating'] : 0;
            $review_count = isset($attributes['reviewCount']) ? $attributes['reviewCount'] : '';
            if ($rating > 0) : 
                $width_percent = $rating * 20;
            ?>
                <span class="appreach__star">
                    <span class="appreach__star__base">★★★★★</span>
                    <span class="appreach__star__evaluate" style="width: <?php echo esc_attr($width_percent); ?>%">★★★★★</span>
                </span>
            <?php endif; ?>

            <?php if (!empty($review_count)) : ?>
                <span style="font-size: 11px; margin-left: 5px; color: #666;">(<?php echo esc_html($review_count); ?>)</span>
            <?php endif; ?>
            
            <span class="appreach__posted">
                posted with <a href="https://technophere.com" title="テクノフィア" target="_blank" rel="nofollow">テクノフィア</a>
            </span>
        </p>
    </div>
    
    <div class="appreach__links" style="">
        <?php if ($show_app_store && !empty($app_store_url)) : ?>
            <a href="<?php echo esc_url($app_store_url); ?>" rel="nofollow" class="appreach__aslink">
                <img src="https://nabettu.github.io/appreach/img/itune_ja.svg" alt="Download on the App Store">
            </a>
        <?php endif; ?>
        
        <?php if ($show_google_play && !empty($google_play_url)) : ?>
            <a href="<?php echo esc_url($google_play_url); ?>" rel="nofollow" class="appreach__gplink">
                <img src="https://nabettu.github.io/appreach/img/gplay_ja.png" alt="Get it on Google Play">
            </a>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($last_updated)) : ?>
        <div class="appreach__date"><?php echo esc_html($last_updated); ?>時点</div>
    <?php endif; ?>
</div>
