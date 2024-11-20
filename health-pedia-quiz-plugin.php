<?php
/*
    Plugin name: HealthPedia Quiz Plugin
    Description: Interactive quizzes with customizable design and rewards.
    Version: 1.0
    Author: SilinceW
    Author URI: @SilinceW
*/

if (!defined('ABSPATH')) exit;

// Check if the ACF function 'acf_add_local_field_group' is available
if (function_exists('acf_add_local_field_group')) :

    // Connect file with ACF field definitions
    include plugin_dir_path(__FILE__) . 'fields/acf-fields.php';

endif;


class HealthPediaQuiz
{
    private $plugin_url;

    // Initialize variables and hooks
    function __construct()
    {
        $this->plugin_url = plugin_dir_url(__FILE__);
        $this->add_actions_and_filters();
    }

    // ---------------------------
    // Register WordPress Actions & Filters
    // ---------------------------
    private function add_actions_and_filters()
    {
        add_action('init', [$this, 'register_quiz_post_type']);
        add_action('acf/init', [$this, 'add_acf_options_sub_page']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_custom_styles_scripts'], 100);
        add_action('wp_ajax_update_hp_metrics_data', [$this, 'do_update_hp_metrics_data']);
        add_action('wp_ajax_nopriv_update_hp_metrics_data', [$this, 'do_update_hp_metrics_data']);

        add_filter('acf/load_field/name=choosed_pallete', [$this, 'load_color_pallets_values']);
        add_filter('single_template', [$this, 'hp_quiz_custom_single_template']);
        add_filter('acf/load_field', [$this, 'load_shortcode_into_acf_field']);
        add_filter('acf/prepare_field', [$this, 'hp_make_field_readonly']);

        add_shortcode('compact_quiz', [$this, 'compact_quiz_shortcode']);
    }

    // ---------------------------
    // Enqueue Methods
    // ---------------------------
    function enqueue_custom_styles_scripts()
    {
        global $post;

        if (isset($post->post_type) && ($post->post_type == 'health-quiz' || has_shortcode($post->post_content, 'compact_quiz'))) {
            wp_enqueue_style('health-quiz-style', $this->plugin_url . 'assets/css/style.min.css');
            wp_enqueue_script('health-quiz-script', $this->plugin_url . 'assets/js/app.min.js', [], '', true);

            // Получение данных
            $quiz_settings = get_field('level_settings');

            // Your inline styles
            $color_pallets = get_field('color_pallets', 'option');

            $custom_styles = '
            .page-quiz__content {
                position: relative;
            }';

            if (!empty($color_pallets) > 0) {
                $custom_styles .= ':root {';
                foreach ($color_pallets as $color_pallet) {
                    $custom_styles .= sprintf(
                        '[data-quiz-color-sheme="%s"] {
                        --color-bg-button: %s;
                        --color-bg-button-inactive: %s;
                    }',
                        strtolower($color_pallet['name']),
                        $color_pallet['colors']['active_color'],
                        $color_pallet['colors']['inactive_color']
                    );
                }
                $custom_styles .= '}';
            }

            wp_add_inline_style('health-quiz-style', $custom_styles);

            if ($post->post_type == 'health-quiz') {
                // Подготовка данных для передачи в JavaScript
                $localized_data = [
                    'ANSWER_STATUS' => [
                        'CORRECT' => 'correct',
                        'WRONG' => 'wrong'
                    ],
                    'QUIZ_ACHIEVEMENT_LEVELS' => [
                        'BRONZE' => [
                            'SRC' => $this->plugin_url . 'assets/img/bronze.svg',
                            'LEVEL' => $quiz_settings['bronze']['number'],
                            'TEXT' => $quiz_settings['bronze']['message']
                        ],
                        'SILVER' => [
                            'SRC' => $this->plugin_url . 'assets/img/silver.svg',
                            'LEVEL' => $quiz_settings['silver']['number'],
                            'TEXT' => $quiz_settings['silver']['message']
                        ],
                        'GOLD' => [
                            'SRC' => $this->plugin_url . 'assets/img/gold.svg',
                            'LEVEL' => $quiz_settings['gold']['number'],
                            'TEXT' => $quiz_settings['gold']['message']
                        ]
                    ],
                    'DATA' => [
                        'AJAX_URL' => admin_url("admin-ajax.php"),
                        'POST_ID' => get_the_ID(),
                    ]

                ];

                // Передача данных в JavaScript
                wp_localize_script('health-quiz-script', 'QuizData', $localized_data);
            }
        }
    }

    // ---------------------------
    // ACF Add options sub-page
    // ---------------------------

    function add_acf_options_sub_page()
    {
        if (function_exists('acf_add_options_sub_page')) {
            acf_add_options_sub_page([
                'page_title'  => 'HP Quiz Settings',
                'menu_title'  => 'Settings',
                'parent_slug' => 'edit.php?post_type=health-quiz',
                'menu_slug'   => 'healthpedia_quiz_settings',
                'capability'  => 'edit_posts'
            ]);
        }
    }

    // ---------------------------
    // ACF Load color pallets values into field
    // ---------------------------
    function load_color_pallets_values($field)
    {
        $color_pallets = get_field('color_pallets', 'option');

        if ($color_pallets) {
            foreach ($color_pallets as $pallet) {
                $field['choices'][$pallet['name']] = $pallet['name'];
            }
        }

        return $field;
    }

    // ---------------------------
    // ACF Inject shortcode into field
    // ---------------------------
    function load_shortcode_into_acf_field($field)
    {
        global $post;

        // Проверяем, что это наш кастомный тип поста и поле ACF с ключом 'shortcode_display'
        if ($post && $post->post_type == 'health-quiz' && $field['name'] == 'hp_quiz_shortcode') {
            $field['default_value'] = '[compact_quiz id="' . $post->ID . '"]';
        }

        return $field;
    }

    function hp_make_field_readonly($field)
    {
        if ($field['key'] === 'field_64f0971858127' || $field['key'] === 'field_64f0974058128' || $field['key'] === 'field_64f0975f58129' || $field['key'] === 'field_64f0962c58122') {
            $field['readonly'] = true;
        }
        return $field;
    }


    // ---------------------------
    // Custom Post Types
    // ---------------------------
    function register_quiz_post_type()
    {
        if (!post_type_exists('health-quiz')) {
            register_post_type('health-quiz', [
                'supports'      => ['title', 'editor', 'excerpt', 'author', 'thumbnail', 'custom-fields'],
                'taxonomies'    => ['category', 'post_tag'],
                'rewrite'       => ['slug' => 'healthpedia-quiz'],
                'has_archive'   => true,
                'public'        => true,
                'show_in_rest'  => true,
                'labels'        => [
                    'name' => 'Quizes HP',
                    'add_new_item' => 'Add New Quiz',
                    'edit_item' => 'Edit Quiz',
                    'all_items' => 'All Quizes',
                    'singular_name' => 'Quiz'
                ],
                'menu_icon' => 'dashicons-awards'
            ]);
        }
    }

    // ---------------------------
    // Load custom single template
    // ---------------------------
    function hp_quiz_custom_single_template($single_template)
    {
        global $post;

        if ($post->post_type == 'health-quiz') {
            $template_path = plugin_dir_path(__FILE__) . 'templates/hp-quiz-single.php';
            if (file_exists($template_path)) {
                $single_template = $template_path;
            }
        }

        return $single_template;
    }

    // ---------------------------
    // Shortcodes rendering
    // ---------------------------
    function compact_quiz_shortcode($atts)
    {
        // Extract shortcode parameters
        $a = shortcode_atts([
            'id' => 0,  // by default ID = 0
        ], $atts);

        $post_id = $a['id'];

        // Check if the post exists
        if (!$post_id || get_post_type($post_id) != 'health-quiz') {
            return '';  // Return an empty string if the post is not found
        }

        // Set a global variable to be able to access the post ID inside the compact-quiz.php file
        global $current_compact_quiz_id;
        $current_compact_quiz_id = $post_id;

        // Get the quiz answers using ACF
        $quiz_answers = get_field('quiz_single', $current_compact_quiz_id);

        // Get the path to the plugin directory
        $path_to_plugin = plugin_dir_url(__FILE__);

        // Get the quiz settings using ACF
        $quiz_settings = get_field('level_settings', $current_compact_quiz_id);

        // Localize data for JavaScript
        $localized_data = [
            'ANSWER_STATUS' => [
                'CORRECT' => 'correct',
                'WRONG' => 'wrong'
            ],
            'QUIZ_ACHIEVEMENT_LEVELS' => [
                'BRONZE' => [
                    'SRC' => $this->plugin_url . 'assets/img/bronze.svg',
                    'LEVEL' => $quiz_settings['bronze']['number'],
                    'TEXT' => $quiz_settings['bronze']['message']
                ],
                'SILVER' => [
                    'SRC' => $this->plugin_url . 'assets/img/silver.svg',
                    'LEVEL' => $quiz_settings['silver']['number'],
                    'TEXT' => $quiz_settings['silver']['message']
                ],
                'GOLD' => [
                    'SRC' => $this->plugin_url . 'assets/img/gold.svg',
                    'LEVEL' => $quiz_settings['gold']['number'],
                    'TEXT' => $quiz_settings['gold']['message']
                ]
            ]
        ];

        // Localize data for JavaScript
        wp_localize_script('health-quiz-script', 'QuizData', $localized_data);


        $output = '<div class="compact-quiz">'
            . '<div class="compact-quiz__slider">'
            . '<div class="compact-quiz__top-line">'
            . '<div class="compact-quiz__status">'
            . '<span class="compact-quiz__current-slide"><i>1</i> question </span>'
            . '<span class="compact-quiz__total-slides">of <i>6</i></span>'
            . '</div>'
            . '<div class="compact-quiz__navigation">'
            . '<button type="button" class="compact-quiz__arr compact-quiz__arr--prev">'
            . '<svg width="20" height="20">'
            . '<use xlink:href="' . $path_to_plugin . 'assets/img/icons/icons.svg#svg-arr-prev"></use>'
            . '</svg>'
            . '</button>'
            . '<button type="button" class="compact-quiz__arr compact-quiz__arr--next swiper-button-disabled">'
            . '<svg width="20" height="20">'
            . '<use xlink:href="' . $path_to_plugin . 'assets/img/icons/icons.svg#svg-arr-next"></use>'
            . '</svg>'
            . '</button>'
            . '</div>'
            . '</div>'
            . '<div class="compact-quiz__proggres-bar"></div>'
            . '<div class="compact-quiz__swiper">';

        $index = 1;
        foreach ($quiz_answers as $slide) {
            $output .= '<div class="compact-quiz__slide" data-question-id="' . $index . '" data-quiz-color-sheme="' . strtolower($slide['choosed_pallete']) . '">'
                . '<div class="plugin-quiz-item__body">'
                . '<div class="plugin-quiz-item__top">'
                . '<h2 class="plugin-quiz-item__title quiz-plugin-label">' . $slide['title'] . '</h2>'
                . '</div>';

            if (!empty($slide['answers'])) {
                $output .= '<div class="plugin-quiz-item__actions">';
                foreach ($slide['answers'] as $button) {
                    $answer_status = $button['is_correct'] ? '1' : '0';
                    $answer_icon = $button['is_correct'] ? 'succes' : 'wrong';
                    $output .= '<button type="button" class="plugin-quiz-item__answer-button button-quiz-plugin" data-answer-status="' . $answer_status . '">'
                        . '<span class="plugin-quiz-item__icon">'
                        . '<img src="' . $path_to_plugin . 'assets/img/icons/' . $answer_icon . '.svg" width="24" height="24" alt="Awesome image">'
                        . '</span>'
                        . '<span>' . $button['label'] . '</span>'
                        . '</button>';
                }
                $output .= '</div>';
            }

            $output .= '</div>'
                . '</div>'
                . '<div class="compact-quiz__slide" data-question-id="' . $index . '">'
                . '<div class="plugin-quiz-item__add-info add-info-item-quiz">'
                . '<div class="add-info-item-quiz__top-row add-info-item-quiz__top-row--compact quiz-plugin-label">'
                . '<span class="add-info-item-quiz__status">-</span>';

            if (!empty($slide['right_answer_full_version'])) {
                $output .= '<div class="add-info-item-quiz__label">' . $slide['right_answer_full_version'] . '</div>';
            }

            $output .= '</div>'
                . '<div class="add-info-item-quiz__wrapper add-info-item-quiz__wrapper--compact">'
                . '<div class="add-info-item-quiz__content">';

            if (!empty($slide['description_correct_answer'])) {
                $output .= '<div class="add-info-item-quiz__descr">'
                    .  $slide['description_correct_answer']
                    . '</div>';
            }

            $output .= '</div>';

            if (!empty($slide['compact_preview_image'])) {
                $output .= '<div class="add-info-item-quiz__image">'
                    . '<img src="' . $slide['compact_preview_image'] . '" width="660" height="305" alt="Awesome image">'
                    . '</div>';
            }

            $output .= '</div>'
                . '</div>'
                . '</div>';
            ++$index;
        }

        unset($index);

        $output .= '<div class="compact-quiz__slide">'
            . '<div class="compact-quiz__results results-plugin-quiz">'
            . '<div class="results-plugin-quiz__content">'
            . '<div class="results-plugin-quiz__top">'
            . '<div class="results-plugin-quiz__images plugin-quiz-results-top">'
            . '<div class="plugin-quiz-results-top__medal">'
            . '<img class="plugin-quiz-results-top__img-medal" src="' . $path_to_plugin . 'assets/img/gold.svg" width="191" height="181" alt="Awesome image">'
            . '<div class="plugin-quiz-results-top__digits">'
            . '<span class="plugin-quiz-results-top__correct-digits">-</span>'
            . '<span class="plugin-quiz-results-top__total-digits">-</span>'
            . '</div>'
            . '</div>'
            . '</div>'
            . '<svg class="plugin-quiz-results-top__results-bg" width="531" height="247" viewBox="0 0 531 247" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity="0.06" d="M900.332 -233.602C876.458 -32.3425 688.968 188.987 273.785 -71.9743C-141.398 -332.936 -394.053 -796.592 116.874 -720.838" stroke="#7599F7" stroke-width="20" stroke-linecap="round"/><g opacity="0.2"><path d="M472.765 131.098C468.819 129.108 467.269 127.446 466.431 122.092C466.367 121.702 466.05 121.393 465.642 121.326C465.226 121.258 464.824 121.453 464.634 121.803C462.02 126.511 459.709 127.54 455.192 128.186C454.783 128.247 454.459 128.549 454.389 128.939C454.318 129.33 454.523 129.72 454.889 129.901C458.927 131.939 460.752 133.641 461.576 138.907C461.639 139.297 461.956 139.607 462.365 139.674C462.421 139.681 462.471 139.688 462.527 139.688C462.88 139.688 463.211 139.506 463.38 139.197C465.994 134.475 467.946 133.459 472.462 132.814C472.871 132.753 473.195 132.45 473.266 132.06C473.336 131.67 473.132 131.28 472.765 131.098Z" fill="var(--color-bg-button)"/></g><g opacity="0.2"><path d="M482.187 119.228C480.753 118.47 480.189 117.837 479.884 115.797C479.861 115.649 479.746 115.531 479.597 115.505C479.446 115.479 479.3 115.554 479.231 115.687C478.28 117.481 477.44 117.873 475.797 118.119C475.649 118.142 475.531 118.257 475.505 118.406C475.479 118.554 475.554 118.703 475.687 118.772C477.155 119.548 477.819 120.197 478.119 122.203C478.142 122.351 478.257 122.469 478.406 122.495C478.426 122.497 478.444 122.5 478.464 122.5C478.593 122.5 478.713 122.431 478.774 122.313C479.725 120.514 480.435 120.127 482.077 119.881C482.226 119.858 482.344 119.743 482.369 119.594C482.395 119.446 482.321 119.297 482.187 119.228Z" fill="var(--color-bg-button)"/></g><g opacity="0.2"><path d="M283.336 28.1555C287.461 25.975 289.082 24.1555 289.959 18.2917C290.025 17.8644 290.356 17.5256 290.784 17.4519C291.218 17.3783 291.638 17.5919 291.837 17.9749C294.57 23.1315 296.986 24.2586 301.708 24.9658C302.136 25.0321 302.474 25.3636 302.548 25.7908C302.622 26.2181 302.408 26.6453 302.025 26.8442C297.804 29.0763 295.896 30.94 295.034 36.708C294.968 37.1352 294.636 37.4741 294.209 37.5478C294.15 37.5551 294.099 37.5625 294.04 37.5625C293.671 37.5625 293.325 37.3636 293.148 37.0247C290.415 31.8535 288.375 30.7411 283.653 30.0339C283.226 29.9676 282.887 29.6361 282.813 29.2089C282.74 28.7816 282.953 28.3544 283.336 28.1555Z" fill="var(--color-bg-button)"/><path d="M272.914 15.7565C274.528 14.9033 275.163 14.1913 275.506 11.8968C275.532 11.7296 275.661 11.597 275.828 11.5681C275.998 11.5393 276.163 11.6229 276.241 11.7728C277.31 13.7906 278.256 14.2316 280.103 14.5083C280.27 14.5343 280.403 14.664 280.432 14.8312C280.461 14.9984 280.377 15.1656 280.227 15.2434C278.575 16.1168 277.829 16.8461 277.492 19.1031C277.466 19.2703 277.336 19.4029 277.169 19.4317C277.146 19.4346 277.126 19.4375 277.103 19.4375C276.958 19.4375 276.823 19.3597 276.754 19.2271C275.684 17.2035 274.886 16.7683 273.038 16.4915C272.871 16.4655 272.738 16.3359 272.709 16.1686C272.68 16.0014 272.765 15.8342 272.914 15.7565Z" fill="var(--color-bg-button)"/><path d="M261.494 5.62152C263.108 4.7683 263.743 4.05626 264.086 1.76182C264.112 1.5946 264.241 1.46204 264.408 1.43315C264.578 1.40427 264.743 1.48786 264.821 1.63772C265.89 3.6555 266.836 4.0965 268.683 4.37317C268.85 4.39917 268.983 4.52886 269.012 4.69606C269.041 4.86326 268.957 5.03044 268.807 5.10824C267.155 5.98166 266.409 6.71103 266.072 8.96804C266.046 9.13524 265.916 9.2678 265.749 9.29658C265.726 9.29947 265.706 9.3024 265.683 9.3024C265.538 9.3024 265.403 9.22458 265.334 9.09201C264.264 7.06839 263.466 6.63319 261.618 6.35642C261.451 6.33042 261.318 6.20074 261.289 6.03353C261.26 5.86633 261.345 5.69915 261.494 5.62152Z" fill="var(--color-bg-button)"/></g></svg>'
            . '</div>'
            . '<div class="results-plugin-quiz__descr">'
            . '<h2 class="results-plugin-quiz__title quiz-plugin-title">Your score</h2>'
            . '<div class="results-plugin-quiz__text">-</div>'
            . '</div>'
            . '</div>'
            . '</div>'
            . '</div>'
            . '</div>'
            . '</div>'
            . '</div>';

        return $output;
    }



    function do_update_hp_metrics_data()
    {
        $count = (int) get_field('quiz_metrics', $_POST['id'])[$_POST['field']];
        if (isset($count)) {
            ++$count;
            update_field('quiz_metrics', array($_POST['field'] => $count), $_POST['id']);
            if ($_POST['field'] == 'likes_real') {
                $custom_count = (int) get_field('quiz_metrics', $_POST['id'])['likes'];
                ++$custom_count;
                update_field('quiz_metrics', array('likes' => $custom_count), $_POST['id']);
            }
        }
        echo $count;

        wp_die(); // this is required to terminate immediately and return a proper response
    }
}

$healthPediaQuiz = new HealthPediaQuiz();
