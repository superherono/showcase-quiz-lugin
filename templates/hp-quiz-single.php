<?php
get_header();
$path_to_plugin = plugin_dir_url(__FILE__);
$post_settings = get_field('quiz_metrics');
?>

<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/post-page.css">

<div class="quiz-wrapper">
    <main class="page-quiz">
        <section class="page-quiz__top-quiz">
            <div class="page-quiz__container">
                <div class="breadcrumb"><?php if (function_exists('bcn_display')) {
                                            bcn_display();
                                        } ?></div>
                <div class="page-quiz__body">

                    <div class="page-quiz__content">
                        <div class="author-block">
                            <?php
                            $author_id = get_post_field('post_author', get_the_ID());
                            $author_avatar = get_field('author_setting', 'user_' . $author_id);
                            $author_fullname = get_the_author_meta('display_name', $author_id);
                            ?>
                            <img src="<?php if (isset($author_avatar['avatar-mini'])) {
                                            echo $author_avatar['avatar-mini'];
                                        } ?>" alt="Healthypedia" title="healthypedia author" class="author-img">
                            <div class="author-content">
                                <a href="/about-us/#authors" class="author-fullname"><?php echo $author_fullname; ?></a>
                                <div class="date-read">
                                    <span class="post-date"><?php echo get_the_date('d M'); ?></span>
                                    <?php if (isset($post_settings['reading_text'])) {
                                        echo '<span class="post-date time">' . $post_settings['reading_text'] . ' min</span>';
                                    } ?>
                                </div>
                            </div>
                        </div>
                        <div class="top-likes-block">
                            <div class="qunat-like">
                                <?php echo '<div class="post-like-button"></div>'; ?>
                                <span class="likes-val">
                                    <?php
                                    $post_likes = $post_settings['likes_show_real'] ? $post_settings['likes_real'] : $post_settings['likes'];
                                    if (isset($post_likes)) {
                                        echo $post_likes;
                                    };

                                    ?>
                                </span>
                            </div>
                            <div class="share-block"></div>
                        </div>
                        <div class="page-quiz__top">
                            <h1 class="page-quiz__title quiz-plugin-title"><?php the_title(); ?></h1>
                            <p class="page-quiz__descr">
                                <?php
                                $post_excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 18);
                                echo $post_excerpt;
                                ?>
                            </p>
                            <div class="page-quiz__image">
                                <?php
                                $banner_url = get_the_post_thumbnail_url(null);
                                if (!empty($banner_url)) :
                                ?>
                                    <picture>
                                        <img src="<?php echo esc_url($banner_url); ?>" width="906" height="450" alt="Awesome image">
                                    </picture>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="page-quiz__articles">
                            <?php
                            $articles = get_field('quiz_single');

                            if (!empty($articles) > 0) :
                                $index = 1;
                                foreach ($articles as $article) :
                                    $color_scheme = $article['choosed_pallete'] ? strtolower($article['choosed_pallete']) : 'blue';

                                    include(plugin_dir_path(__FILE__) . 'template-parts/quiz-article.php');

                                    ++$index;
                                endforeach;
                            endif;
                            unset($index);
                            ?>
                        </div>
                        <div class="page-quiz__results results-plugin-quiz" hidden>
                            <div class="results-plugin-quiz__content">
                                <div class="results-plugin-quiz__top">
                                    <div class="results-plugin-quiz__images plugin-quiz-results-top">
                                        <div class="plugin-quiz-results-top__medal">
                                            <img class="plugin-quiz-results-top__img-medal" src="<?php echo $path_to_plugin . '../'; ?>assets/img/gold.svg" width="191" height="181" alt="Awesome image">
                                            <p class="plugin-quiz-results-top__digits">
                                                <span class="plugin-quiz-results-top__correct-digits">11</span>
                                                <span class="plugin-quiz-results-top__total-digits">12</span>
                                            </p>
                                            <img class="plugin-quiz-results-top__right-stars" src="<?php echo $path_to_plugin . '../'; ?>assets/img/right-star.svg" width="30" height="26" alt="Awesome image">
                                            <img class="plugin-quiz-results-top__left-stars" src="<?php echo $path_to_plugin . '../'; ?>assets/img/left-star.svg" width="32" height="28" alt="Awesome image">
                                        </div>
                                    </div>
                                    <img class="plugin-quiz-results-top__confetti" src="<?php echo $path_to_plugin . '../'; ?>assets/img/confetti.svg" width="440" height="127" alt="Awesome image">
                                    <img class="plugin-quiz-results-top__results-bg" src="<?php echo $path_to_plugin . '../'; ?>assets/img/bg-results.svg" width="902" height="149" alt="Awesome image">
                                </div>
                                <div class="results-plugin-quiz__descr">
                                    <?php
                                    $results_quiz = get_field('score');
                                    if (!empty($results_quiz['title'])) :
                                    ?>
                                        <h2 class="results-plugin-quiz__title quiz-plugin-title"><?php echo $results_quiz['title']; ?></h2>
                                    <?php endif; ?>
                                    <div class="results-plugin-quiz__text">You have a good knowledge of the macronutrients. Keep learning more with us and stay healthy!</div>
                                </div>
                            </div>
                            <div class="results-plugin-quiz__additional-quiz additional-item-plugin-quiz">
                                <?php

                                if (!empty($results_quiz['show_more_quiz']['title'])) : ?>
                                    <h3 class="additional-item-plugin-quiz__title quiz-plugin-label"><?php echo $results_quiz['show_more_quiz']['title']; ?></h3>
                                <?php endif; ?>

                                <?php if (!$results_quiz['show_random'] && !empty($results_quiz['show_more_quiz']['post_id'][0])) {
                                    $add_post_id = $results_quiz['show_more_quiz']['post_id'][0];
                                } else {
                                    $args = [
                                        'post_type' => 'health-quiz',
                                        'post_status' => 'publish',
                                        'numberposts' => 10,
                                        'fields' => 'ids',
                                        'exclude' => get_the_ID(),
                                    ];
                                    $all_posts = get_posts($args);

                                    if ($all_posts) {
                                        $random_index = array_rand($all_posts, 1);
                                        $add_post_id = $all_posts[$random_index];
                                    }
                                }
                                $add_post_data = get_field('quiz_single', $add_post_id)[0];
                                ?>
                                <a href="<?php echo get_the_permalink($add_post_id); ?>" class="additional-item-plugin-quiz__body">

                                    <?php if (!empty($add_post_data['compact_preview_image'])) : ?>
                                        <div class="additional-item-plugin-quiz__image">
                                            <img src="<?php echo esc_url($add_post_data['compact_preview_image']); ?>" width="140" height="140" alt="Awesome image">
                                        </div>
                                    <?php endif; ?>

                                    <div class="additional-item-plugin-quiz__content">

                                        <?php if (!empty($add_post_data['title'])) : ?>
                                            <h4 class="additional-item-plugin-quiz__question"><?php echo $add_post_data['title']; ?></h4>
                                        <?php endif; ?>
                                        <?php if (!empty($add_post_data['answers']) > 0) : ?>
                                            <div class="additional-item-plugin-quiz__answers">
                                                <?php foreach ($add_post_data['answers'] as $answer) : ?>
                                                    <div class="additional-item-plugin-quiz__answer"><?php echo $answer['label']; ?></div>
                                                <?php endforeach; ?>
                                                <span class="additional-item-plugin-quiz__mobile-text">Tap to start new test</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="page-quiz__bottom">
                            <div class="page-quiz__info info-items">
                                <?php
                                $post_views = $post_settings['views_show_real'] ? $post_settings['views_real'] : $post_settings['views'];
                                if (!empty($post_views)) : ?>
                                    <!-- viewed -->
                                    <div class="info-items__item">
                                        <svg width="32" height="32">
                                            <use xlink:href="<?php echo $path_to_plugin . '../'; ?>assets/img/icons/icons.svg#svg-eye"></use>
                                        </svg>
                                        <span><?php echo $post_views ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php
                                $post_correct_answers = $post_settings['c_answers_show_real'] ? $post_settings['c_answers_real'] : $post_settings['correct_answers'];
                                if (!empty($post_correct_answers)) : ?>
                                    <!-- correct answers -->
                                    <div class="info-items__item">
                                        <svg width="32" height="32">
                                            <use xlink:href="<?php echo $path_to_plugin . '../'; ?>assets/img/icons/icons.svg#svg-succes"></use>
                                        </svg>
                                        <span><?php echo $post_correct_answers; ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php
                                $post_wrong_answers = $post_settings['w_answers_show_real'] ? $post_settings['w_answers_real'] : $post_settings['wrong_answers'];
                                if (!empty($post_wrong_answers)) : ?>
                                    <!-- wrong answers -->
                                    <div class="info-items__item">
                                        <svg width="32" height="32">
                                            <use xlink:href="<?php echo $path_to_plugin . '../'; ?>assets/img/icons/icons.svg#svg-wrong"></use>
                                        </svg>
                                        <span><?php echo $post_wrong_answers; ?></span>
                                    </div>
                                <?php endif; ?>

                                <!-- share -->
                                <div class="info-items__item">
                                    <div class="share-block"></div>
                                </div>
                            </div>

                            <div class="page-quiz__tags tags-quiz">
                                <h2 class="tags-quiz__label">Tags</h2>
                                <ul class="all-tags">
                                    <?php
                                    $categories = get_the_category();
                                    $counter = 1;
                                    $cat_main = '';
                                    foreach ($categories as $category) {
                                        if ($category->cat_ID != 113) {
                                            $cat_main = $category->cat_ID;
                                            $cat_icon = get_term_meta($cat_main, 'category_icon');
                                    ?>
                                            <li><a href="<?php echo get_category_link($category->cat_ID); ?>"><?php echo '<img src="' . wp_get_attachment_image_src($cat_icon[0])[0] . '">' . $category->name . '</a></li>';
                                                                                                            }
                                                                                                        }

                                                                                                        if (get_the_tags(get_the_ID())) {
                                                                                                            foreach (get_the_tags(get_the_ID()) as $tag) {
                                                                                                                if ($tag->term_group != '2') {
                                                                                                                ?>
                                            <li>
                                                <a href="<?php echo get_tag_link($tag->term_id); ?>">
                                                    <?php echo $tag->name; ?>
                                                </a>
                                            </li>
                                <?php
                                                                                                                }
                                                                                                            }
                                                                                                        }
                                ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <aside class="page-quiz__aside">
                        <div class="releated-posts releated-posts--quizzes">
                            <p class="sidebar-title"><?php echo __('Popular Quizzez'); ?></p>
                            <ul class="all-related-posts">
                                <?php
                                $args = array('post_type' => 'health-quiz', 'post_status' => 'publish', 'posts_per_page' => '3', 'paged' => 1);
                                $quiz_posts = new WP_Query($args);
                                if ($quiz_posts->have_posts()) :
                                    while ($quiz_posts->have_posts()) : $quiz_posts->the_post(); ?>
                                        <?php
                                        $post_id = get_the_ID();
                                        $big_post_img = get_the_post_thumbnail_url(null);
                                        $category = get_the_category();
                                        $firstCategory = $category[0]->cat_name;
                                        $post_set = get_field('quiz_metrics');
                                        ?>
                                        <li class="post-inner">
                                            <a href="<?php echo get_permalink($post_id); ?>">
                                                <div class="img-related-post">
                                                    <img src="<?php echo $big_post_img; ?>" alt="Healthypedia" title="Healthypedia">
                                                    <svg width="31" height="35">
                                                        <use xlink:href="<?php echo $path_to_plugin . '../'; ?>assets/img/icons/icons.svg#svg-q"></use>
                                                    </svg>
                                                </div>
                                                <div class="data-related-post">
                                                    <p class="title-related-post"><?php echo get_the_title(); ?></p>
                                                    <span class="read-related-post"><?php echo $post_set['likes']; ?></span>
                                                </div>
                                            </a>

                                        </li>
                                <?php endwhile;
                                endif;
                                wp_reset_postdata(); ?>
                            </ul>
                        </div>

                        <div class="releated-posts">
                            <p class="sidebar-title"><?php echo __('Related artilces'); ?></p>
                            <ul class="all-related-posts">
                                <?php
                                $args = array('post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => '3', 'paged' => 1, 'author' => $author_id);
                                $blog_posts = new WP_Query($args);
                                if ($blog_posts->have_posts()) :
                                    while ($blog_posts->have_posts()) : $blog_posts->the_post(); ?>
                                        <?php
                                        $post_id = get_the_ID();
                                        $big_post_img = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full', false)[0];
                                        $category = get_the_category();
                                        $firstCategory = $category[0]->cat_name;
                                        $post_set = get_field('post_setting');
                                        ?>
                                        <li class="post-inner">
                                            <a href="<?php echo get_permalink($post_id); ?>">
                                                <div class="img-related-post">
                                                    <img src="<?php echo $big_post_img; ?>" alt="Healthypedia" title="Healthypedia">
                                                </div>
                                                <div class="data-related-post">
                                                    <p class="title-related-post"><?php echo get_the_title(); ?></p>
                                                    <span class="read-related-post"><?php echo $post_set['reading_text'] . ' min'; ?></span>
                                                </div>
                                            </a>
                                        </li>
                                <?php endwhile;
                                endif;
                                wp_reset_postdata(); ?>
                            </ul>
                        </div>
                    </aside>
                </div>
            </div>
        </section>
        <!-- /.top-quiz -->
    </main>
</div>

<!-- END Quiz Content -->
<div class="share-popup">
    <div class="qunat-like">
        <?php echo '<div class="post-like-button"></div>'; ?>
        <span class="likes-val">
            <?php
            $post_likes = $post_settings['likes_show_real'] ? $post_settings['likes_real'] : $post_settings['likes'];
            if (isset($post_likes)) {
                echo $post_likes;
            };

            ?>
        </span>
    </div>
    <div class="share-block"></div>
</div>
<div class="drop-share">
    <div class="share-block-close">x</div>
    <ul>
        <li><a href="http://twitter.com/share?text=<?= the_title(); ?>&url=<?= the_permalink(); ?>">Twitter</a></li>
        <li><a href="#">Facebook</a></li>
        <li onclick="saveLink()">Copy link</li>
    </ul>
</div>
<script>
    jQuery(document).on('click', '.share-block', function() {
        let modal = jQuery('.drop-share');

        jQuery('.share-block').removeClass('active');
        jQuery(this).addClass('active');

        jQuery(this).parent().append(modal.fadeIn());


    });
    jQuery(document).on('click', '.share-block-close', function() {
        let lala = jQuery(this).parent().parent().find('.share-block');
        if (lala.hasClass('active')) {
            jQuery('.drop-share').fadeOut();
            lala.removeClass('active');
        }
    });

    jQuery(document).on('click', '.share-block ul li a', function() {

        jQuery(this).parent().parent().fadeOut();
    });


    function saveLink() {
        /* Get the text field */
        var copyText = window.location.href;

        /* Select the text field */

        /* Copy the text inside the text field */
        navigator.clipboard.writeText(copyText);
        jQuery('.copy-completed-modal').css('display', 'flex');
        jQuery('.drop-share').fadeOut();
        setTimeout(function() {
            jQuery('.copy-completed-modal').hide();
        }, 5000);
    }
    jQuery('.cross-window-copy').click(function() {
        jQuery('.copy-completed-modal').hide();
    });
</script>

<?php
get_footer();
?>