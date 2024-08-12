<?php
/**
 * Plugin name: Post Gallery
 * Description: Create image galleries from post images
 * Version: 1.0
 * Author: Cau Guanabara
 * Author URI: mailto:cauguanabara@gmail.com
 * Text Domain: postgallery
 * Domain Path: /langs/
 * License: Wordpress
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PGAL_PATH', trailingslashit(str_replace("\\", "/", dirname(__FILE__))));
define('PGAL_URL', str_replace("\\", "/", plugin_dir_url(__FILE__)));

class PostGallery {

    public function __construct() {
        add_action('init', function() {
            load_plugin_textdomain('postgallery', false, dirname(plugin_basename(__FILE__)) . '/langs');
        });
        add_action('wp_enqueue_scripts', [$this, 'add_css']);
        add_action('admin_enqueue_scripts', [$this, 'add_media_button']);
        add_shortcode('post-gallery', [$this, 'scode']);
    }

    public function add_media_button($hook) {
        if ($hook == 'upload.php') {
            wp_enqueue_script('custom-media-button', plugin_dir_url(__FILE__) . 'assets/js/media-button.js', ['jquery'], '1.0', true);
            wp_localize_script('custom-media-button', 'pgStrings', [
                "buttonLabel" => __('Gallery shortcode', 'postgallery'),
                "promptText" => __('Copy the shortcode and paste into your publication', 'postgallery')
            ]);
        }
    }

    public function add_css() {
        wp_enqueue_style('post-gallery-css', PGAL_URL . 'assets/css/post-gallery.css');
    }

    public function photoswipe_init($gallery = '.gallery', $children = 'a') {
        ?>
        <link rel="stylesheet" href="<?php print PGAL_URL; ?>assets/photoswipe/photoswipe.css">
        <script defer type="module">
            import PhotoSwipeLightbox from '<?php print PGAL_URL; ?>assets/photoswipe/photoswipe-lightbox.esm.js';
            const lightbox = new PhotoSwipeLightbox({
                gallery: '<?php print $gallery; ?>',
                children: '<?php print $children; ?>',
                showHideAnimationType: 'zoom',
                pswpModule: () => import('<?php print PGAL_URL; ?>assets/photoswipe/photoswipe.esm.js')
            });
            lightbox.init();
        </script>
        <?php
    }

    public function fetch_images($post_ids) {
        $imgs = [];
        foreach ($post_ids as $pid) {
            $imgs[] = get_post($pid);
        }
        return $imgs;
    }

    public function fetch($post_id = 0) {
        if (is_array($post_id)) {
            $images = $this->fetch_images($post_id);
        } else {
            $pid = $post_id ? $post_id : $this->post_id;
            if (!$pid) {
                return [];
            }
            $images = get_attached_media('image', $pid);
        }
        
        if (!count($images)) {
            return [];
        }
        
        $imgs = [];
        foreach ($images as $image) {
            $image_url = wp_get_attachment_url($image->ID);
            $thumb = wp_get_attachment_image($image->ID, 'medium');
            $full = wp_get_attachment_image($image->ID, 'full');
            $size = wp_get_attachment_image_src($image->ID, 'full');

            $imgs[] = [
                'id' => $image->ID,
                'url' => esc_url($image_url),
                'title' => $image->post_title,
                'description' => $image->post_content,
                "thumbnail" => $thumb,
                "full" => $full,
                'width' => $size[1],
                'height' => $size[2]
            ];
        }
        return $imgs;
    }

    public function show($post_id = 0) {
        $imgs = $this->fetch($post_id);
        if (!count($imgs)) {
            return;
        }
        ?>
        <div class="gallery">
            <?php foreach ($imgs as $img) { ?>
                <?php $vcls = $img['height'] > $img['width'] ? ' vert' : ''; ?>
                <a href="<?php print $img['url']; ?>" class="gallery-item<?php print $vcls; ?>" data-pswp-width="<?php print $img['width']; ?>" data-pswp-height="<?php print $img['height']; ?>">
                     <?php print $img['thumbnail']; ?>
                </a>
            <?php } ?>
        </div>
        <?php
        $this->photoswipe_init();
    }

    /**
     * Shortcode
     * 
     * @since    1.0.0
     */
    public function scode($atts) {
        $a = shortcode_atts(['id' => 0, "images" => ""], $atts);
        if (!empty($a['images'])) {
            $a['id'] = explode(",", trim($a['images']));
        }
        if (empty($a['id'])) {
            if (is_singular()) {
                global $post;
                $a['id'] = $post->ID;
            } else {
                return 'No post id.';
            }
        }
        ob_start();
        $this->show($a['id']);
        $content = ob_get_clean();
        if (empty($content)) {
            return $atts['empty_text'] ?? '';
        }

        if (!empty($atts['title'])) {
            $title = $atts['title'];
            $content = "<h2>{$title}</h2>\n{$content}";
        }
        return $content;
    }
}

global $post_gallery;
$post_gallery = new PostGallery();
?>