<?php

class GooglePlayScraper {
    private $base_url = 'https://play.google.com/store/search';

    public function search($term) {
        $url = $this->base_url . '?q=' . urlencode($term) . '&c=apps&hl=ja&gl=JP';
        
        $response = wp_remote_get($url, array(
            'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36'
        ));

        if (is_wp_error($response)) {
            return array();
        }

        $html = wp_remote_retrieve_body($response);
        if (empty($html)) {
            return array();
        }

        return $this->parse_search_html($html);
    }

    public function get_details($id) {
        $url = 'https://play.google.com/store/apps/details?id=' . urlencode($id) . '&hl=ja&gl=JP';
        
        $response = wp_remote_get($url, array(
            'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36'
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $html = wp_remote_retrieve_body($response);
        if (empty($html)) {
            return false;
        }

        return $this->parse_details_html($html, $id);
    }

    private function parse_search_html($html) {
        $results = array();
        
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);

        // Search for app cards
        $nodes = $xpath->query('//a[contains(@href, "/store/apps/details?id=")]');
        $seen_ids = array();

        foreach ($nodes as $node) {
            if (count($results) >= 5) break;

            $href = $node->getAttribute('href');
            parse_str(parse_url($href, PHP_URL_QUERY), $params);
            $id = isset($params['id']) ? $params['id'] : '';

            if (empty($id) || isset($seen_ids[$id])) continue;
            
            $name = '';
            $icon = '';
            $developer = '';
            
            // 1. Try legacy structure (everything inside <a>)
            
            // Name detection
            $name_node = $xpath->query('.//span[string-length(text()) > 0]', $node)->item(0);
            if ($name_node) {
                $name = $name_node->textContent;
            } else {
                $divs = $xpath->query('.//div[not(*)]', $node);
                foreach($divs as $div) {
                    if (strlen(trim($div->textContent)) > 0) {
                        $name = trim($div->textContent);
                        break;
                    }
                }
            }

            // Icon detection
            $img_node = $xpath->query('.//img', $node)->item(0);
            if ($img_node) {
                $icon = $img_node->getAttribute('src');
                if ($img_node->hasAttribute('data-src')) {
                    $icon = $img_node->getAttribute('data-src');
                }
            }

            // 2. Try New Structure (<a> is empty, content is in sibling/parent)
            if (empty($name) && $node->parentNode) {
                $parent = $node->parentNode;
                
                // Name: Find first leaf node with text content
                // We exclude script/style tags and look for non-empty text
                $leaf_text_nodes = $xpath->query('.//*[not(self::script or self::style) and not(*) and normalize-space(text()) != ""]', $parent);
                
                if ($leaf_text_nodes->length > 0) {
                    $name = trim($leaf_text_nodes->item(0)->textContent);
                    
                    // Developer is typically the second text block (or later)
                    if ($leaf_text_nodes->length > 1) {
                         // Sometimes there is an "Ad" label or rating in between
                         // But usually Developer follows Name closely.
                         $developer = trim($leaf_text_nodes->item(1)->textContent);
                    }
                }

                // Icon: Find image in parent
                $parent_imgs = $xpath->query('.//img', $parent);
                if ($parent_imgs->length > 0) {
                    $icon_node = $parent_imgs->item(0);
                    $icon = $icon_node->getAttribute('src');
                    if ($icon_node->hasAttribute('srcset')) {
                        // srcset usually starts with the 1x image
                        $parts = explode(' ', $icon_node->getAttribute('srcset'));
                        if (!empty($parts[0])) {
                            $icon = $parts[0];
                        }
                    }
                    if (empty($icon) && $icon_node->hasAttribute('data-src')) {
                        $icon = $icon_node->getAttribute('data-src');
                    }
                }
            }

            if ($id && $name) {
                $results[] = array(
                    'trackName' => $name,
                    'trackViewUrl' => 'https://play.google.com/store/apps/details?id=' . $id . '&hl=ja',
                    'artworkUrl512' => $icon,
                    'artistName' => $developer,
                    'store' => 'google_play',
                    'trackId' => $id
                );
                $seen_ids[$id] = true;
            }
        }

        return $results;
    }

    private function parse_details_html($html, $id) {
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);

        // Name (h1)
        $name_node = $xpath->query('//h1[@itemprop="name"]')->item(0);
        if (!$name_node) $name_node = $xpath->query('//h1')->item(0);
        $name = $name_node ? $name_node->textContent : '';

        // Developer
        // Usually in a div with specific class or just look for the first link after name that looks like a developer
        // Modern Google Play structure often puts developer in a specific span or div
        // Let's try finding the "contains ads" or "purchases" and look prior? No.
        // Let's look for a class often associated or just the second logical text block.
        // Often: <div class="Vbfug auoIOc"><a href="/store/apps/dev?id=...">Developer Name</a></div>
        $has_dev_link = $xpath->query('//a[contains(@href, "/store/apps/dev") or contains(@href, "/store/apps/developer")]');
        $developer = '';
        if ($has_dev_link->length > 0) {
            $developer = $has_dev_link->item(0)->textContent;
        }

        // Icon
        // Look for the main image
        // narrowly target "Icon image" to avoid getting the feature graphic (Cover art)
        $img_nodes = $xpath->query('//img[@alt="Icon image"]');
        $icon = '';
        if ($img_nodes->length > 0) {
            $icon = $img_nodes->item(0)->getAttribute('src');
            // Prefer srcset or larger
            if ($img_nodes->item(0)->hasAttribute('srcset')) {
                // Parse srcset? Or just take src which is usually decent.
                // Google Play src is often small, but query params can resize it.
            }
        }
        
        // Fallback for icon: OpenGraph - ONLY if it looks like an icon? 
        // OG image is often the feature graphic, so it's risky. 
        // Better to rely on the specific Alt text.
        if (empty($icon)) {
             // Try searching for img with class "T75of" (common for icons) if alt fails?
             // But classes change. Let's try itemprop="image" inside a smaller container?
             // For now, let's stick to strict "Icon image" to avoid the banner.
        }

        // Rating
        $rating = 0;
        $rating_node = $xpath->query('//div[@itemprop="starRating"]/div[@itemprop="ratingValue"]'); // Microdata
        if ($rating_node->length == 0) {
             // Try searching for aria-label "Rated x stars out of five"
             $star_nodes = $xpath->query('//div[contains(@aria-label, "stars out of five")]');
             if ($star_nodes->length > 0) {
                 $label = $star_nodes->item(0)->getAttribute('aria-label');
                 if (preg_match('/Rated ([0-9.]+)/', $label, $matches)) {
                     $rating = floatval($matches[1]);
                 }
             } else {
                 // Fallback: look for the big number
                 $rating_text_nodes = $xpath->query('//div[contains(@class, "TT9eCd")]');
                 if ($rating_text_nodes->length > 0) {
                     $val = floatval($rating_text_nodes->item(0)->textContent);
                     if ($val > 0) $rating = $val;
                 }
             }
        } else {
            $rating = floatval($rating_node->item(0)->textContent);
        }

        // Review Count
        $review_count = '';
        $review_nodes = $xpath->query('//span[contains(text(), "reviews")]');
        if ($review_nodes->length == 0) {
             // Sometimes it's just a number with "reviews" in a different element, or abbreviated like "1M params"
             // Try to find the element next to the rating?
             // Often: <div class="EHUI5b">1.23M reviews</div>
             // Let's look for text that ends with "reviews" or matches regex
             // Iterate through all spans/divs with short text? Too slow.
             // Let's rely on specific classes if known or aria-label? No.
             
             // Try generalized look for "X reviews"
             $all_text = $xpath->query('//*[text()]'); // Potentially expensive, limit scope if possible.
             // Better: <div class="w7Iutd"> <div class="g1rdde">
        }
        
        // Actually, let's keep it simple. If we can't find it easily via "reviews" text, we might skip or refine later.
        // Google Play often writes "10K reviews" or "1.5M reviews" in the same block as rating.
        // Look for the element with class `g1rdde` (often review count)
        $g1rdde = $xpath->query('//div[contains(@class, "g1rdde")]');
        if ($g1rdde->length > 0) {
             $review_count = $g1rdde->item(0)->textContent;
        }

        return array(
            'trackName' => $name,
            'trackViewUrl' => 'https://play.google.com/store/apps/details?id=' . $id . '&hl=ja',
            'artworkUrl512' => $icon,
            'artistName' => $developer,
            'store' => 'google_play',
            'trackId' => $id,
            'averageUserRating' => $rating,
            'userRatingCount' => $review_count
        );
    }
}
