<?php

class AppStoreScraper {
    private $lookup_url = 'https://itunes.apple.com/lookup?country=JP&id=';

    public function get_details($id) {
        $url = $this->lookup_url . $id;
        
        $response = wp_remote_get($url, array(
            'user-agent' => 'Mozilla/5.0 (WordPress; App Store Links Plugin)',
            'timeout'    => 10
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['results']) || !is_array($data['results'])) {
            return false;
        }

        $app = $data['results'][0];

        // Format review count
        $review_count_raw = isset($app['userRatingCount']) ? $app['userRatingCount'] : 0;
        $review_count_formatted = '';
        if ($review_count_raw >= 10000) {
            $review_count_formatted = number_format($review_count_raw / 10000, 1) . '万件';
        } else {
            $review_count_formatted = number_format($review_count_raw) . '件';
        }

        return array(
            'appName' => isset($app['trackName']) ? $app['trackName'] : '',
            'iconUrl' => isset($app['artworkUrl512']) ? $app['artworkUrl512'] : (isset($app['artworkUrl100']) ? $app['artworkUrl100'] : ''),
            'developer' => isset($app['artistName']) ? $app['artistName'] : '',
            'price' => isset($app['formattedPrice']) ? $app['formattedPrice'] : '',
            'appStoreUrl' => isset($app['trackViewUrl']) ? $app['trackViewUrl'] : '',
            'rating' => isset($app['averageUserRating']) ? $app['averageUserRating'] : 0,
            'reviewCount' => $review_count_formatted,
            'store' => 'ios',
            'trackId' => $id
        );
    }
}
