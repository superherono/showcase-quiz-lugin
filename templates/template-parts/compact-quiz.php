<?php

$quiz_answers = get_field('quiz_single', $current_compact_quiz_id);
$path_to_plugin = plugin_dir_url(__FILE__);

?>

<div class="compact-quiz">
    <div class="compact-quiz__slider">
        <div class="compact-quiz__top-line">
            <div class="compact-quiz__status">
                <span class="compact-quiz__current-slide"><i>1</i> question </span>
                <span class="compact-quiz__total-slides">of <i>6</i></span>
            </div>
            <div class="compact-quiz__navigation">
                <button type="button" class="compact-quiz__arr compact-quiz__arr--prev">
                    <svg width="20" height="20">
                        <use xlink:href="<?php echo $path_to_plugin . '../../'; ?>assets/img/icons/icons.svg#svg-arr-prev"></use>
                    </svg>
                </button>
                <button type="button" class="compact-quiz__arr compact-quiz__arr--next swiper-button-disabled">
                    <svg width="20" height="20">
                        <use xlink:href="<?php echo $path_to_plugin . '../../'; ?>assets/img/icons/icons.svg#svg-arr-next"></use>
                    </svg>
                </button>
            </div>
        </div>
        <div class="compact-quiz__proggres-bar"></div>
        <div class="compact-quiz__swiper">
            <?php $index = 1;
            foreach ($quiz_answers as $slide) : ?>
                <div class="compact-quiz__slide" data-question-id="<?php echo $index; ?>" data-quiz-color-sheme="<?php echo strtolower($slide['choosed_pallete']); ?>">
                    <div class="plugin-quiz-item__body">
                        <div class="plugin-quiz-item__top">
                            <h2 class="plugin-quiz-item__title quiz-plugin-label"><?php echo $slide['title']; ?></h2>
                        </div>
                        <?php if (!empty($slide['answers'])) : ?>
                            <div class="plugin-quiz-item__actions">
                                <?php foreach ($slide['answers'] as $button) :
                                    $answer_status = $button['is_correct'] ? '1' : '0';
                                    $answer_icon = $button['is_correct'] ? 'succes' : 'wrong';
                                ?>
                                    <button type="button" class="plugin-quiz-item__answer-button button-quiz-plugin" data-answer-status="<?php echo $answer_status; ?>">
                                        <span class="plugin-quiz-item__icon">
                                            <img src="<?php echo $path_to_plugin . '../../'; ?>assets/img/icons/<?php echo $answer_icon; ?>.svg" width="24" height="24" alt="Awesome image">
                                        </span>
                                        <span><?php echo $button['label'] ?></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="compact-quiz__slide" data-question-id="<?php echo $index; ?>">
                    <div class="plugin-quiz-item__add-info add-info-item-quiz">
                        <div class="add-info-item-quiz__wrapper add-info-item-quiz__wrapper--compact">
                            <div class="add-info-item-quiz__content">
                                <div class="add-info-item-quiz__top-row quiz-plugin-label">
                                    <span class="add-info-item-quiz__status">-</span>

                                    <?php if (!empty($slide['right_answer_full_version'])) : ?>
                                        <p class="add-info-item-quiz__label"><?php echo $slide['right_answer_full_version']; ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($slide['description_correct_answer'])) : ?>
                                    <div class="add-info-item-quiz__descr">
                                        <p><?php echo $slide['description_correct_answer']; ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($slide['compact_preview_image'])) : ?>
                                <div class="add-info-item-quiz__image">
                                    <img src="<?php echo $slide['compact_preview_image']; ?>" width="660" height="305" alt="Awesome image">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php ++$index;
            endforeach;
            unset($index); ?>

            <div class="compact-quiz__slide">
                <div class="compact-quiz__results results-plugin-quiz">
                    <div class="results-plugin-quiz__content">
                        <div class="results-plugin-quiz__top">
                            <div class="results-plugin-quiz__images plugin-quiz-results-top">
                                <div class="plugin-quiz-results-top__medal">
                                    <img class="plugin-quiz-results-top__img-medal" src="<?php echo $path_to_plugin . '../../'; ?>assets/img/gold.svg" width="191" height="181" alt="Awesome image">
                                    <p class="plugin-quiz-results-top__digits">
                                        <span class="plugin-quiz-results-top__correct-digits">-</span>
                                        <span class="plugin-quiz-results-top__total-digits">-</span>
                                    </p>
                                </div>
                            </div>
                            <svg class="plugin-quiz-results-top__results-bg" width="645" height="301">
                                <use xlink:href="<?php echo $path_to_plugin . '../../'; ?>assets/img/icons/icons.svg#svg-results-bg-compact"></use>
                            </svg>
                        </div>
                        <div class="results-plugin-quiz__descr">
                            <h2 class="results-plugin-quiz__title quiz-plugin-title">-</h2>
                            <div class="results-plugin-quiz__text">-</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>