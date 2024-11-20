<article class="page-quiz__item plugin-quiz-item" data-question-id="<?php echo $index; ?>" data-quiz-color-sheme="<?php echo $color_scheme; ?>" data-show-answer="false">
    <img class="plugin-quiz-item__decor" src="<?php echo $path_to_plugin . '../'; ?>assets/img/quiz-card-bg-star.svg" width="32" height="32" alt="Awesome image">
    <div class="plugin-quiz-item__body">
        <div class="plugin-quiz-item__top">
            <?php if (!empty($article['title'])) : ?>
                <h2 class="plugin-quiz-item__title quiz-plugin-label"><?php echo $article['title']; ?></h2>
            <?php endif; ?>
        </div>

        <?php if (!empty($article['description'])) : ?>
            <p class="plugin-quiz-item__descr"><?php echo $article['description']; ?></p>
        <?php endif; ?>

        <?php if (!empty($article['preview_image'])) : ?>
            <div class="plugin-quiz-item__image">
                <picture>
                    <img src="<?php echo esc_url($article['preview_image']); ?>" width="652" height="250" alt="Awesome image">
                </picture>
            </div>
        <?php endif; ?>

        <?php if (!empty($article['answers']) > 0) : ?>
            <div class="plugin-quiz-item__actions">
                <?php foreach ($article['answers'] as $button) :
                    $answer_status = $button['is_correct'] ? '1' : '0';
                    $answer_icon = $button['is_correct'] ? 'succes' : 'wrong';
                ?>
                    <button type="button" class="plugin-quiz-item__answer-button button-quiz-plugin" data-answer-status="<?php echo $answer_status; ?>">
                        <span class="plugin-quiz-item__icon">
                            <img src="<?php echo $path_to_plugin . '../'; ?>assets/img/icons/<?php echo $answer_icon; ?>.svg" width="24" height="24" alt="Awesome image">
                        </span>
                        <span><?php echo $button['label'] ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="plugin-quiz-item__add-info add-info-item-quiz" hidden>
        <div class="add-info-item-quiz__wrapper">
            <div class="add-info-item-quiz__top-row quiz-plugin-label">
                <span class="add-info-item-quiz__status">-</span>

                <?php if (!empty($article['right_answer_full_version'])) : ?>
                    <p class="add-info-item-quiz__label"> <?php echo $article['right_answer_full_version']; ?></p>
                <?php endif; ?>

            </div>

            <?php if (!empty($article['description_correct_answer'])) : ?>
                <div class="add-info-item-quiz__descr">
                    <?php echo $article['description_correct_answer']; ?>
                </div>
            <?php endif; ?>

            <?php
            $describe_image = $article['describe_image'];
            $style = '';

            $image_height = !empty($describe_image['height']) && $describe_image['height'] != 'auto' ? 'height:' . $describe_image['height'] . 'px;' : '';
            $image_shadow = $describe_image['shadow'] == true ? 'filter: drop-shadow(0px 4px 14px rgba(117, 117, 117, 0.25));' : '';
            $image_position = !empty($describe_image['position']) ? 'object-position:' . $describe_image['position'] . ';' : '';

            $style = $image_height . $image_position . $image_shadow;
            ?>

            <?php if (!empty($describe_image['image'])) : ?>
                <div class="add-info-item-quiz__block-image">
                    <div class="add-info-item-quiz__image">
                        <picture>
                            <img src="<?php echo esc_url($describe_image['image']); ?>" width="660" height="305" style="<?php echo $style; ?>" alt="Awesome image">
                        </picture>
                    </div>
                    <?php if (!empty($describe_image['source'])) : ?>
                        <div class="add-info-item-quiz__source">
                            Source:
                            <a href="<?php echo $describe_image['source']['url']; ?>" rel="nofollow noopener noreferrer" target="_blank"><?php echo $describe_image['source']['title']; ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</article>